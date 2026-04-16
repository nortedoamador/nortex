<?php

namespace App\Models;

use Database\Factories\EmpresaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Empresa extends Model
{
    /** @use HasFactory<EmpresaFactory> */
    use HasFactory;

    protected $fillable = [
        'nome',
        'nome_fantasia',
        'slug',
        'cnpj',
        'uf',
        'cidade',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'ativo',
        'acesso_plataforma_ate',
        'pagamento_inicial_pendente',
        'email_contato',
        'telefone',
        'logo_path',
        'texto_procuracao_procuradores',
        'plan_id',
        'plan_overrides',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_subscription_status',
        'stripe_current_price_id',
        'stripe_subscription_cancel_at_period_end',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'acesso_plataforma_ate' => 'date',
            'pagamento_inicial_pendente' => 'boolean',
            'plan_overrides' => 'array',
            'documento_modelos_lab_slugs_ocultos' => 'array',
            'stripe_subscription_cancel_at_period_end' => 'boolean',
        ];
    }

    public function documentoModeloLabSlugEstaOculto(string $slug): bool
    {
        $slug = Str::lower(trim($slug));
        if ($slug === '') {
            return false;
        }

        return in_array($slug, $this->documento_modelos_lab_slugs_ocultos ?? [], true);
    }

    public function addDocumentoModeloLabSlugOculto(string $slug): void
    {
        $slug = Str::lower(trim($slug));
        if ($slug === '') {
            return;
        }

        $list = $this->documento_modelos_lab_slugs_ocultos ?? [];
        if (in_array($slug, $list, true)) {
            return;
        }

        $list[] = $slug;
        $this->documento_modelos_lab_slugs_ocultos = array_values($list);
        $this->save();
    }

    public function removeDocumentoModeloLabSlugOculto(string $slug): void
    {
        $slug = Str::lower(trim($slug));
        if ($slug === '') {
            return;
        }

        $filtered = array_values(array_filter(
            $this->documento_modelos_lab_slugs_ocultos ?? [],
            static fn (mixed $s): bool => is_string($s) && $s !== $slug,
        ));

        $this->documento_modelos_lab_slugs_ocultos = $filtered;
        $this->save();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class);
    }

    public function compromissosAgenda(): HasMany
    {
        return $this->hasMany(EmpresaCompromisso::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Subscrição Stripe válida para usar os módulos do tenant (preços configurados em STRIPE_PRICE_FULL e/ou STRIPE_PRICE_BASIC).
     */
    public function assinaturaPlataformaAtiva(): bool
    {
        if ($this->pagamento_inicial_pendente) {
            return false;
        }

        $configuredPriceIds = self::normalizedStripePriceIdsForTenantSubscription();
        $anyPriceConfigured = $configuredPriceIds !== [];
        $enforce = (bool) config('services.stripe.enforce_subscription', false);

        $noStripe = $this->stripe_customer_id === null && $this->stripe_subscription_id === null;

        // Sem nenhum preço no .env: mantém legado (dev / instalações antigas sem billing).
        if (! $anyPriceConfigured && ! $enforce && $noStripe) {
            return true;
        }

        // Com pelo menos um preço configurado: é obrigatório ter subscrição Stripe válida para um desses preços.
        if ($anyPriceConfigured && $noStripe) {
            return false;
        }

        if (! in_array($this->stripe_subscription_status, ['active', 'trialing'], true)) {
            return false;
        }

        if (! $anyPriceConfigured) {
            return true;
        }

        $current = $this->stripe_current_price_id;

        return is_string($current) && in_array($current, $configuredPriceIds, true);
    }

    /**
     * @return list<string>
     */
    private static function normalizedStripePriceIdsForTenantSubscription(): array
    {
        $out = [];
        foreach (['price_full', 'price_basic'] as $key) {
            $raw = config('services.stripe.'.$key);
            if (is_string($raw)) {
                $t = trim($raw);
                if ($t !== '') {
                    $out[] = $t;
                }
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Indica se a empresa ainda pode usar rotas tenant (até ao fim do dia indicado em acesso_plataforma_ate, inclusive).
     */
    public function acessoPlataformaVigente(): bool
    {
        if ($this->acesso_plataforma_ate === null) {
            return true;
        }

        $limite = Carbon::parse($this->acesso_plataforma_ate)->startOfDay();

        return ! Carbon::today()->greaterThan($limite);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Módulo financeiro (permissões financeiro.*) só é permitido pelo billing quando
     * a subscrição Stripe ativa corresponde ao Price "completo" (STRIPE_PRICE_FULL).
     * Sem integração Stripe na empresa, mantém-se o comportamento legado.
     */
    public function billingIncludesFinanceiro(): bool
    {
        if ($this->stripe_customer_id === null && $this->stripe_subscription_id === null) {
            return true;
        }

        if (! in_array($this->stripe_subscription_status, ['active', 'trialing'], true)) {
            return false;
        }

        $fullPrice = config('services.stripe.price_full');
        if (! is_string($fullPrice) || $fullPrice === '') {
            return true;
        }

        return $this->stripe_current_price_id === $fullPrice;
    }

    /**
     * Rótulo do plano de assinatura (Stripe) para exibição administrativa.
     */
    public function stripePlanLabel(): ?string
    {
        $pid = $this->stripe_current_price_id;
        if (! is_string($pid) || $pid === '') {
            return null;
        }

        $full = config('services.stripe.price_full');
        $basic = config('services.stripe.price_basic');
        if (is_string($full) && $full !== '' && $pid === $full) {
            return 'Completo';
        }
        if (is_string($basic) && $basic !== '' && $pid === $basic) {
            return 'Essencial';
        }

        return $pid;
    }

    public function stripeDashboardBaseUrl(): string
    {
        $key = (string) config('services.stripe.key');

        return str_starts_with($key, 'pk_live_')
            ? 'https://dashboard.stripe.com'
            : 'https://dashboard.stripe.com/test';
    }
}
