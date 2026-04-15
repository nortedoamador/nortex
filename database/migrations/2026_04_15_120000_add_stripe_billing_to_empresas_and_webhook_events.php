<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('plan_overrides');
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('stripe_subscription_status', 32)->nullable();
            $table->string('stripe_current_price_id')->nullable();
        });

        Schema::create('stripe_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_event_id')->unique();
            $table->string('event_type', 128);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_events');

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_customer_id',
                'stripe_subscription_id',
                'stripe_subscription_status',
                'stripe_current_price_id',
            ]);
        });
    }
};
