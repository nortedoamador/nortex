<?php

namespace App\Http\Requests\Platform;

use App\Support\BrazilStates;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformSubscriptionEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_platform_admin === true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug') && trim((string) $this->input('slug')) === '') {
            $this->merge(['slug' => null]);
        }
        if ($this->has('acesso_plataforma_ate') && trim((string) $this->input('acesso_plataforma_ate')) === '') {
            $this->merge(['acesso_plataforma_ate' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('empresas', 'slug')],
            'email_contato' => ['required', 'string', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:40'],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'uf' => ['nullable', 'string', Rule::in(BrazilStates::codes())],
            'ativo' => ['nullable', 'boolean'],
            'acesso_plataforma_ate' => ['nullable', 'date'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'enviar_convite' => ['nullable', 'boolean'],
            'stripe_customer_id' => ['nullable', 'string', 'max:255'],
            'stripe_subscription_id' => ['nullable', 'string', 'max:255'],
            'stripe_subscription_status' => ['nullable', 'string', 'max:32'],
            'stripe_current_price_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $c = $this->input('stripe_customer_id');
            if (is_string($c) && trim($c) !== '' && ! str_starts_with(trim($c), 'cus_')) {
                $validator->errors()->add('stripe_customer_id', __('Deve começar por cus_ (ID de cliente Stripe) ou ficar vazio.'));
            }
            $s = $this->input('stripe_subscription_id');
            if (is_string($s) && trim($s) !== '' && ! str_starts_with(trim($s), 'sub_')) {
                $validator->errors()->add('stripe_subscription_id', __('Deve começar por sub_ (ID de subscrição Stripe) ou ficar vazio.'));
            }
            $p = $this->input('stripe_current_price_id');
            if (is_string($p) && trim($p) !== '' && ! str_starts_with(trim($p), 'price_')) {
                $validator->errors()->add('stripe_current_price_id', __('Deve começar por price_ (ID de preço Stripe) ou ficar vazio.'));
            }
            $st = $this->input('stripe_subscription_status');
            if (is_string($st) && trim($st) !== '') {
                $allowed = [
                    'active', 'trialing', 'past_due', 'canceled', 'unpaid',
                    'incomplete', 'incomplete_expired', 'paused',
                ];
                if (! in_array(trim($st), $allowed, true)) {
                    $validator->errors()->add('stripe_subscription_status', __('Estado inválido para subscrição Stripe.'));
                }
            }
        });
    }
}
