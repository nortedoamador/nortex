<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        /** Price ID mensal "completo" (ex.: 497) — desbloqueia financeiro quando a subscrição está ativa. */
        'price_full' => env('STRIPE_PRICE_FULL'),
        /** Price ID do plano essencial/básico (ex.: 297) — módulos tenant sem financeiro. */
        'price_basic' => env('STRIPE_PRICE_BASIC'),
        /** Referência ao produto Stripe (ex. prod_xxx); o Checkout usa os Price IDs acima. */
        'product_full' => env('STRIPE_PRODUCT_FULL'),
        /** Valor em reais só para texto na página Planos (opcional). */
        'plan_basica_display_brl' => (int) env('STRIPE_PLAN_BASICA_BRL', 297),
        'plan_completa_display_brl' => (int) env('STRIPE_PLAN_COMPLETA_BRL', 497),
        /**
         * Se true, após o registo redirecciona para o Checkout quando o plano completo está configurado.
         * Com STRIPE_PRICE_FULL e/ou STRIPE_PRICE_BASIC definidos, o acesso aos módulos exige subscrição Stripe activa para um desses preços.
         * Sem preços configurados e com false: empresas sem Stripe mantêm acesso legado (dev / instalações antigas).
         */
        'enforce_subscription' => env('STRIPE_ENFORCE_SUBSCRIPTION', false),
    ],

];
