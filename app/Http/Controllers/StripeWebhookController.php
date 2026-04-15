<?php

namespace App\Http\Controllers;

use App\Services\StripeBillingSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Subscription;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, StripeBillingSyncService $billing): Response
    {
        $secret = config('services.stripe.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            Log::error('Stripe webhook: STRIPE_WEBHOOK_SECRET em falta.');

            return response('', 503);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            Log::notice('Stripe webhook: assinatura ou payload inválidos.', ['message' => $e->getMessage()]);

            return response('', 400);
        }

        try {
            DB::transaction(function () use ($event, $billing): void {
                $inserted = DB::table('stripe_webhook_events')->insertOrIgnore([
                    'stripe_event_id' => $event->id,
                    'event_type' => $event->type,
                    'created_at' => now(),
                ]);

                if ($inserted === 0) {
                    return;
                }

                $this->dispatchEvent($event, $billing);
            });
        } catch (\Throwable $e) {
            Log::error('Stripe webhook: falha ao processar evento.', [
                'type' => $event->type,
                'id' => $event->id,
                'message' => $e->getMessage(),
            ]);

            return response('', 500);
        }

        return response('', 200);
    }

    private function dispatchEvent(Event $event, StripeBillingSyncService $billing): void
    {
        $object = $event->data->object;

        match ($event->type) {
            'checkout.session.completed' => $object instanceof Session
                ? $billing->syncFromCheckoutSession($object)
                : null,
            'customer.subscription.created', 'customer.subscription.updated' => $this->syncSubscriptionObject($object, $billing),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($object, $billing),
            'invoice.paid', 'invoice.payment_succeeded', 'invoice.payment_failed' => $object instanceof Invoice
                ? $billing->syncFromInvoice($object)
                : null,
            'payment_intent.succeeded', 'payment_intent.payment_failed' => $this->handlePaymentIntent($object, $billing),
            default => null,
        };
    }

    private function syncSubscriptionObject(mixed $object, StripeBillingSyncService $billing): void
    {
        $id = null;
        if ($object instanceof Subscription) {
            $id = $object->id;
        } elseif (is_object($object) && isset($object->id) && is_string($object->id)) {
            $id = $object->id;
        }
        if ($id) {
            $billing->syncFromSubscriptionId($id);
        }
    }

    private function handleSubscriptionDeleted(mixed $object, StripeBillingSyncService $billing): void
    {
        if (! $object instanceof Subscription) {
            return;
        }

        $customerId = is_string($object->customer) ? $object->customer : null;
        if ($customerId) {
            $billing->clearSubscriptionForCustomer($customerId, $object->id);
        }
    }

    private function handlePaymentIntent(mixed $object, StripeBillingSyncService $billing): void
    {
        if (! $object instanceof PaymentIntent) {
            return;
        }

        if (! is_string($object->invoice) || $object->invoice === '') {
            return;
        }

        $invoice = Invoice::retrieve($object->invoice);
        $billing->syncFromInvoice($invoice);
    }
}
