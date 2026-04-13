<?php

namespace App\Services\Cnh;

use App\Support\DocumentoBrasil;
use Carbon\Carbon;
use Psr\Log\LoggerInterface;

/**
 * Converte payload bruto (JSON do QR ou texto OCR) em chaves do formulário de cliente.
 *
 * OCR: pipeline tolerante (pré-processamento, heurísticas, score de confiança).
 *
 * @phpstan-type Normalized array<string, string|int|null>
 */
final class CnhPayloadNormalizer
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @return Normalized
     */
    public function normalizeFromQrPayload(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return $this->applyValidadeDisplayAlias($this->emptyNormalized());
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            $data = $this->tryParseLooseJson($raw);
        }
        if (! is_array($data)) {
            return $this->applyValidadeDisplayAlias($this->emptyNormalized());
        }

        $qrMeta = ['nascimento_repaired' => false];
        $out = $this->mapAssociative($data, $qrMeta);
        $this->validateConsistency($out, false);
        $this->applyFieldScores($out, [
            'nome_source' => 'qr',
            'nascimento_repaired' => $qrMeta['nascimento_repaired'],
        ]);
        $out['confidence_score'] = $this->computeAdvancedScore($out);
        $this->logParserAdvanced($out);

        return $this->applyValidadeDisplayAlias($out);
    }

    /**
     * @return Normalized
     */
    public function normalizeFromOcrText(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            $empty = $this->emptyNormalized();
            $empty['confidence_score'] = 0;

            return $this->applyValidadeDisplayAlias($empty);
        }

        $prepared = $this->preprocessOcrText($text);

        $out = $this->emptyNormalized();

        $ocrMeta = [
            'nome_source' => null,
            'nascimento_repaired' => false,
        ];

        $this->mergeLineBasedOcr($text, $out);
        if ($out['nome'] !== null && is_string($out['nome']) && trim($out['nome']) !== '') {
            $ocrMeta['nome_source'] = 'line';
        }

        if ($out['nome'] === null || ! is_string($out['nome']) || trim($out['nome']) === '') {
            $nomeMeta = $this->extractNomeWithMeta($text, $prepared);
            $out['nome'] = $nomeMeta['nome'];
            $ocrMeta['nome_source'] = $nomeMeta['source'];
        }

        $out['cpf'] ??= $this->extractCpf($text);
        $out['validade_cnh'] ??= $this->extractValidade($text, $prepared);
        $out['categoria_cnh'] ??= $this->extractCategoria($text, $prepared);

        $this->mergeStructuralCnhFields($text, $prepared, $out);

        if ($out['data_nascimento'] === null) {
            $birthRepaired = false;
            $out['data_nascimento'] = $this->extractDataNascimento($text, $prepared, $out['primeira_habilitacao'], $birthRepaired);
            if ($birthRepaired) {
                $ocrMeta['nascimento_repaired'] = true;
            }
        }

        $this->validateConsistency($out, true);
        $this->applyFieldScores($out, $ocrMeta);
        $out['confidence_score'] = $this->computeAdvancedScore($out);
        $out = $this->applyValidadeDisplayAlias($out);

        $this->logParserAdvanced($out);

        return $out;
    }

    /**
     * Campo derivado para o front: validade da CNH; se o OCR não achou, usa 1.ª habilitação (mesmo ISO).
     *
     * @param  Normalized  $data
     * @return Normalized
     */
    private function applyValidadeDisplayAlias(array $data): array
    {
        $vc = $data['validade_cnh'] ?? null;
        $ph = $data['primeira_habilitacao'] ?? null;
        $alias = null;
        if (is_string($vc) && trim($vc) !== '') {
            $alias = trim($vc);
        } elseif (is_string($ph) && trim($ph) !== '') {
            $alias = trim($ph);
        }
        $data['validade'] = $alias;

        return $data;
    }

    /**
     * Texto normalizado para heurísticas: maiúsculas, ruído reduzido, correções OCR comuns em datas/números.
     */
    public function preprocessOcrText(string $text): string
    {
        $t = mb_strtoupper(trim($text), 'UTF-8');
        $t = preg_replace('/[^\p{L}\p{N}\s\/().\[\]:\-]/u', ' ', $t) ?? $t;
        $t = preg_replace('/\s+/u', ' ', $t) ?? $t;
        $t = trim($t);

        $t = $this->autocorrectOcr($t);
        $t = $this->applyOcrDigitCorrections($t);

        return trim($t);
    }

    public function extractNome(string $rawText, string $preparedText): ?string
    {
        return $this->extractNomeWithMeta($rawText, $preparedText)['nome'];
    }

    /**
     * @return array{nome: ?string, source: ?string}
     */
    private function extractNomeWithMeta(string $rawText, string $preparedText): array
    {
        $rawText = trim($rawText);
        if ($rawText === '') {
            return ['nome' => null, 'source' => null];
        }

        if (preg_match('/NOME\s+E\s+SOBRENOME[^\(]*\(([^\)]+)\)/iu', $rawText, $m)) {
            $n = $this->cleanNome($m[1]);
            if (mb_strlen($n) >= 3 && preg_match('/\p{L}/u', $n)) {
                return ['nome' => $n, 'source' => 'official'];
            }
        }

        if (preg_match('/HABILITA(?:ÇÃO|CAO|CÃO)[^\(]*\(([^\)]+)\)/iu', $rawText, $m)) {
            $n = $this->cleanNome($m[1]);
            if (mb_strlen($n) >= 3 && preg_match('/\p{L}/u', $n)) {
                return ['nome' => $n, 'source' => 'official'];
            }
        }

        $up = mb_strtoupper($preparedText, 'UTF-8');

        if (preg_match('/NOME\s+E\s+SOBRENOME[^\(]*\(([^\)]+)\)/u', $up, $m)) {
            $n = $this->cleanNome($m[1]);
            if (mb_strlen($n) >= 3 && preg_match('/\p{L}/u', $n)) {
                return ['nome' => $n, 'source' => 'official'];
            }
        }

        if (preg_match('/HABILITA(?:ÇÃO|CAO|CÃO)[^\(]*\(([^\)]+)\)/u', $up, $m)) {
            $n = $this->cleanNome($m[1]);
            if (mb_strlen($n) >= 3 && preg_match('/\p{L}/u', $n)) {
                return ['nome' => $n, 'source' => 'official'];
            }
        }

        if (preg_match_all('/\b([A-ZÀ-Ú]{2,}(?:\s+[A-ZÀ-Ú]{2,}){2,})\b/u', $up, $matches)) {
            foreach ($matches[1] as $cand) {
                if (preg_match('/\b(BRASIL|REPUBLICA|REPÚBLICA|MINISTERIO|MINISTÉRIO|INFRAESTRUTURA|SENATRAN|DEPARTAMENTO|NACIONAL|CARTEIRA|HABILITACAO|HABILITAÇÃO|DRIVER|LICENSE)\b/iu', $cand)) {
                    continue;
                }

                if (mb_strlen($cand) > 60) {
                    continue;
                }
                if (substr_count($cand, ' ') < 2) {
                    continue;
                }

                return ['nome' => $this->titleCaseName($cand), 'source' => 'fallback'];
            }
        }

        return ['nome' => null, 'source' => null];
    }

    private function cleanNome(string $nome): string
    {
        $nome = preg_replace('/[^A-ZÀ-Ú\s]/iu', ' ', $nome) ?? $nome;
        $nome = preg_replace('/\s+/', ' ', $nome) ?? $nome;

        return $this->titleCaseName(trim($nome));
    }

    public function extractDataNascimento(string $rawText, string $preparedText, ?string $excludeIso = null, ?bool &$birthUsedOcrRepair = null): ?string
    {
        if (preg_match('/NASCIMENTO[^\[]*\[([^\]\n]+)/iu', $rawText, $m)) {
            $chunk = trim(explode(',', $m[1], 2)[0]);
            $fragRepaired = false;
            $iso = $this->parseFlexibleBrDate($this->applyOcrDigitCorrections(mb_strtoupper($chunk, 'UTF-8')), $fragRepaired);
            if ($iso !== null && $iso !== $excludeIso) {
                if ($birthUsedOcrRepair !== null) {
                    $birthUsedOcrRepair = $fragRepaired;
                }

                return $iso;
            }
        }

        if (preg_match('/NASCIMENTO.{0,80}?(\d{2}[\/\s.\-]*\d{2}[\/\sNR0O.\-]+\d{3,4})/iu', $preparedText, $m)) {
            $fragRepaired = false;
            $iso = $this->parseFlexibleBrDate($m[1], $fragRepaired);
            if ($iso !== null && $iso !== $excludeIso && $this->isPlausibleBirthYear((int) substr($iso, 0, 4))) {
                if ($birthUsedOcrRepair !== null) {
                    $birthUsedOcrRepair = $fragRepaired;
                }

                return $iso;
            }
        }

        $nascPos = mb_stripos($preparedText, 'NASCIMENTO', 0, 'UTF-8');

        $bestIso = null;
        /** @var array{0: int, 1: int}|null $bestKey */
        $bestKey = null;
        $bestRepaired = false;
        if (preg_match_all('/\b(\d{2}[\/]?\d{2}[\/NRnr0O.\-]+\d{3,4})\b/u', $preparedText, $all, PREG_OFFSET_CAPTURE)) {
            foreach ($all[1] as $pair) {
                [$frag, $offset] = $pair;
                if (! is_int($offset)) {
                    continue;
                }
                $fragRepaired = false;
                $iso = $this->parseFlexibleBrDate($frag, $fragRepaired);
                if ($iso === null || $iso === $excludeIso) {
                    continue;
                }
                $y = (int) substr($iso, 0, 4);
                if (! $this->isPlausibleBirthYear($y)) {
                    continue;
                }
                $dist = $nascPos === false ? PHP_INT_MAX : abs($offset - $nascPos);
                $key = [$dist, $y];
                if ($bestKey === null || $key < $bestKey) {
                    $bestKey = $key;
                    $bestIso = $iso;
                    $bestRepaired = $fragRepaired;
                }
            }
        }

        if ($bestIso !== null && $birthUsedOcrRepair !== null) {
            $birthUsedOcrRepair = $bestRepaired;
        }

        return $bestIso;
    }

    public function extractCpf(string $text): ?string
    {
        $digits = DocumentoBrasil::apenasDigitos($text);
        $len = strlen($digits);
        for ($i = 0; $i <= $len - 11; $i++) {
            $slice = substr($digits, $i, 11);
            if (DocumentoBrasil::cpfValido($slice)) {
                return DocumentoBrasil::formatarCpf($slice);
            }
        }

        return null;
    }

    public function extractValidade(string $rawText, string $preparedText): ?string
    {
        foreach (['VALIDADE', 'VENCIMENTO'] as $kw) {
            if (preg_match(
                '/\b'.$kw.'\b[^\d]{0,40}(\d{1,2}\s*[\/.\-]\s*\d{1,2}\s*[\/.\-]\s*\d{4}|\d{2}[\/NRnr0O.\-]+\d{2}[\/NRnr0O.\-]+\d{3,4})/iu',
                $preparedText,
                $m
            )) {
                $iso = $this->parseFlexibleBrDate(preg_replace('/\s+/', '', $m[1]) ?? $m[1]);
                if ($iso !== null) {
                    return $iso;
                }
            }
        }

        if (preg_match('/VALIDADE[^\[]*\[([^\]\n]+)/iu', $rawText, $m)) {
            $iso = $this->parseFlexibleBrDate($this->applyOcrDigitCorrections(mb_strtoupper(trim($m[1]), 'UTF-8')));
            if ($iso !== null) {
                return $iso;
            }
        }

        return null;
    }

    public function extractCategoria(string $rawText, string $preparedText): ?string
    {
        if (preg_match('/CATEGORIA[^\n]{0,55}?([ABCDE]{1,4})\b/iu', $preparedText, $m)) {
            return strtoupper(preg_replace('/\s+/', '', $m[1]) ?? $m[1]);
        }

        if (preg_match('/\b(?:CAT|CATEG)\b[^\n]{0,20}?\b([ABCDE]{1,4})\b/u', $preparedText, $m)) {
            return strtoupper($m[1]);
        }

        return null;
    }

    /**
     * @param  Normalized  $data
     */
    public function hasMinimumData(array $data): bool
    {
        foreach (['nome', 'cpf', 'data_nascimento'] as $k) {
            if (! empty($data[$k]) && is_string($data[$k]) && trim($data[$k]) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Normalized  $data
     */
    public function hasAnyMeaningfulField(array $data): bool
    {
        $skip = [
            'confidence_score' => true,
            'validade' => true,
            'nome_score' => true,
            'cpf_score' => true,
            'nascimento_score' => true,
        ];
        foreach ($data as $key => $v) {
            if (isset($skip[$key])) {
                continue;
            }
            if (is_string($v) && trim($v) !== '') {
                return true;
            }
        }

        return false;
    }

    private function isValidNome(string $nome): bool
    {
        if (mb_strlen($nome) < 10) {
            return false;
        }

        if (preg_match('/BRASIL|REPUBLICA|MINISTERIO|SENATRAN/i', $nome)) {
            return false;
        }

        if (substr_count($nome, ' ') < 2) {
            return false;
        }

        return true;
    }

    /**
     * Correção OCR: não substitui O/I em todo o texto (destruiria "NOME", "OLIVEIRA"); só entre dígitos + padrão de data quebrada.
     */
    private function autocorrectOcr(string $text): string
    {
        $text = preg_replace('/(?<=\d)O(?=\d)/', '0', $text) ?? $text;
        $text = preg_replace('/(?<=\d)I(?=\d)/', '1', $text) ?? $text;
        $text = preg_replace('/(\d{2})(\d{2})[nNrR](\d{3,4})/', '$1/$2/$3', $text) ?? $text;

        return $text;
    }

    private function validateConsistency(array &$data, bool $strictNomeHeuristics = true): void
    {
        if ($strictNomeHeuristics && ! empty($data['nome']) && is_string($data['nome']) && ! $this->isValidNome($data['nome'])) {
            $data['nome'] = null;
        }

        if (! empty($data['cpf']) && is_string($data['cpf'])) {
            $d = preg_replace('/\D/', '', $data['cpf']) ?? '';
            if (strlen($d) !== 11 || ! DocumentoBrasil::cpfValido($d)) {
                $data['cpf'] = null;
            }
        }

        if (! empty($data['data_nascimento']) && is_string($data['data_nascimento'])) {
            $iso = $data['data_nascimento'];
            $year = (int) substr($iso, 0, 4);
            $yNow = (int) date('Y');
            if ($year < 1920 || $year > $yNow) {
                $data['data_nascimento'] = null;
            } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $iso, $m)
                && ! checkdate((int) $m[2], (int) $m[3], $year)) {
                $data['data_nascimento'] = null;
            }
        }
    }

    private function computeAdvancedScore(array $data): int
    {
        $score = 0;

        if (! empty($data['nome'])) {
            $score += 30;
        }
        if (! empty($data['cpf'])) {
            $score += 40;
        }
        if (! empty($data['data_nascimento'])) {
            $score += 30;
        }

        return $score;
    }

    /**
     * @param  Normalized  $out
     * @param  array{nome_source: ?string, nascimento_repaired?: bool}  $meta
     */
    private function applyFieldScores(array &$out, array $meta): void
    {
        $nome = isset($out['nome']) && is_string($out['nome']) ? $out['nome'] : null;
        $out['nome_score'] = $this->resolveNomeScore($nome, $meta['nome_source'] ?? null);
        $out['cpf_score'] = $this->resolveCpfScore($out['cpf'] ?? null);
        $out['nascimento_score'] = $this->resolveNascimentoScore(
            isset($out['data_nascimento']) && is_string($out['data_nascimento']) ? $out['data_nascimento'] : null,
            (bool) ($meta['nascimento_repaired'] ?? false)
        );
    }

    private function resolveNomeScore(?string $nome, ?string $source): int
    {
        if ($nome === null || trim($nome) === '') {
            return 0;
        }
        if ($source === 'official' || $source === 'qr') {
            return 100;
        }
        if ($source === 'line' || $source === 'fallback') {
            return 70;
        }

        return 0;
    }

    private function resolveCpfScore(mixed $cpf): int
    {
        if (! is_string($cpf) || trim($cpf) === '') {
            return 0;
        }
        $d = preg_replace('/\D/', '', $cpf) ?? '';
        if (strlen($d) !== 11 || ! DocumentoBrasil::cpfValido($d)) {
            return 0;
        }

        return 100;
    }

    private function resolveNascimentoScore(?string $iso, bool $repaired): int
    {
        if ($iso === null || trim($iso) === '') {
            return 0;
        }
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $iso, $m)) {
            return 0;
        }
        $y = (int) $m[1];
        $mo = (int) $m[2];
        $d = (int) $m[3];
        $yNow = (int) date('Y');
        if ($y < 1920 || $y > $yNow) {
            return 0;
        }
        if (! checkdate($mo, $d, $y)) {
            return 0;
        }

        return $repaired ? 50 : 100;
    }

    /**
     * @param  Normalized  $out
     */
    private function logParserAdvanced(array $out): void
    {
        $this->logger->info('cnh.parser.fields_detected', [
            'nome' => ! empty($out['nome']),
            'cpf' => ! empty($out['cpf']),
            'nascimento' => ! empty($out['data_nascimento']),
            'validade_cnh' => ! empty($out['validade_cnh']),
            'categoria_cnh' => ! empty($out['categoria_cnh']),
            'primeira_habilitacao' => ! empty($out['primeira_habilitacao']),
            'validade_alias' => ! empty($out['validade'] ?? null),
        ]);
        $this->logger->info('cnh.parser.validation', [
            'nome_ok' => ! empty($out['nome']),
            'cpf_ok' => ! empty($out['cpf']),
            'nascimento_ok' => ! empty($out['data_nascimento']),
        ]);
        $this->logger->info('cnh.parser.field_scores', [
            'nome_score' => $out['nome_score'] ?? 0,
            'cpf_score' => $out['cpf_score'] ?? 0,
            'nascimento_score' => $out['nascimento_score'] ?? 0,
        ]);
        $this->logger->info('cnh.parser.confidence_score', ['score' => $out['confidence_score'] ?? 0]);
    }

    private function applyOcrDigitCorrections(string $t): string
    {
        $t = preg_replace('/(\d)O\//u', '${1}0/', $t) ?? $t;
        $t = preg_replace('/(?<=\d)[nrNR](?=\d)/', '/', $t) ?? $t;
        $t = preg_replace('/(?<=\d)O(?=\d)/', '0', $t) ?? $t;
        $t = preg_replace('/(?<=\d)I(?=\d)/', '1', $t) ?? $t;

        return trim($t);
    }

    /**
     * Corrige datas OCR (dd/mm/aaaa ou ISO) com mês/dia trocados ou dígitos invertidos no mês (ex.: 21 → 12).
     */
    private function fixBrokenDate(string $date): ?string
    {
        $date = trim(preg_replace('/\s+/', '', $date) ?? $date);
        if ($date === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $mm)) {
            $y = (int) $mm[1];
            $mth = (int) $mm[2];
            $d = (int) $mm[3];
            if (checkdate($mth, $d, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $mth, $d);
            }
            if ($mth > 12 && $d <= 12 && checkdate($d, $mth, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $d, $mth);
            }

            return null;
        }

        if (preg_match('/(\d{2})[^\d]?(\d{2})[^\d]?(\d{4})/', $date, $mm)) {
            $d = (int) $mm[1];
            $mth = (int) $mm[2];
            $y = (int) $mm[3];

            if ($mth >= 10 && $mth <= 99) {
                $mPad = str_pad((string) $mth, 2, '0', STR_PAD_LEFT);
                if (strlen($mPad) === 2) {
                    $revM = (int) strrev($mPad);
                    if ($revM >= 1 && $revM <= 12 && checkdate($revM, $d, $y)) {
                        $mth = $revM;
                    }
                }
            }

            if ($mth > 12) {
                if ($d <= 12) {
                    [$d, $mth] = [$mth, $d];
                } else {
                    $mth = max(1, min(12, $mth % 12));
                }
            }

            if (checkdate($mth, $d, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $mth, $d);
            }
        }

        return null;
    }

    private function finalizeIsoTracked(string $naiveIso, ?bool &$ocrRepaired = null): string
    {
        $fixed = $this->fixBrokenDate($naiveIso);
        $out = $fixed ?? $naiveIso;
        if ($ocrRepaired !== null && $fixed !== null && $fixed !== $naiveIso) {
            $ocrRepaired = true;
        }

        return $out;
    }

    private function parseFlexibleBrDate(string $s, ?bool &$ocrRepaired = null): ?string
    {
        $s = trim($s);
        if ($s === '') {
            return null;
        }

        $s = $this->applyOcrDigitCorrections(mb_strtoupper($s, 'UTF-8'));
        $s = preg_replace('/\s+/', '', $s) ?? $s;

        $digitsFlat = preg_replace('/\D/', '', $s) ?? '';
        if (preg_match('/^(\d{2})(\d{2})(\d{3,4})$/', $digitsFlat, $m)) {
            $y = $this->expandOcrYear($m[3]);
            if ($y !== null && $this->isValidCalendarDate((int) $y, (int) $m[2], (int) $m[1])) {
                return $this->finalizeIsoTracked(sprintf('%s-%s-%s', $y, $m[2], $m[1]), $ocrRepaired);
            }
        }

        if (preg_match('/^(\d{2})(\d{2})(\d{4})$/', $s, $m) && $this->isValidCalendarDate((int) $m[3], (int) $m[2], (int) $m[1])) {
            return $this->finalizeIsoTracked(sprintf('%s-%s-%s', $m[3], $m[2], $m[1]), $ocrRepaired);
        }

        if (preg_match('/^(\d{2})(\d{2})[A-Z](\d{3,4})$/', $s, $m)) {
            $y = $this->expandOcrYear($m[3]);
            if ($y !== null && $this->isValidCalendarDate((int) $y, (int) $m[2], (int) $m[1])) {
                return $this->finalizeIsoTracked(sprintf('%s-%s-%s', $y, $m[2], $m[1]), $ocrRepaired);
            }
        }

        if (preg_match('/^(\d{2})[\/.\-](\d{2})[\/.\-](\d{4})$/', $s, $m)
            && $this->isValidCalendarDate((int) $m[3], (int) $m[2], (int) $m[1])) {
            return $this->finalizeIsoTracked(sprintf('%s-%s-%s', $m[3], $m[2], $m[1]), $ocrRepaired);
        }

        if (preg_match('/(\d{2})[\/.\-](\d{2})[\/.\-](\d{4})/', $s, $m)
            && $this->isValidCalendarDate((int) $m[3], (int) $m[2], (int) $m[1])) {
            return $this->finalizeIsoTracked(sprintf('%s-%s-%s', $m[3], $m[2], $m[1]), $ocrRepaired);
        }

        if (preg_match('/(\d{2})[\/.\-](\d{2})\D+(\d{3,4})/', $s, $m)) {
            $y = $this->expandOcrYear($m[3]);
            if ($y !== null && $this->isValidCalendarDate((int) $y, (int) $m[2], (int) $m[1])) {
                return $this->finalizeIsoTracked(sprintf('%s-%s-%s', $y, $m[2], $m[1]), $ocrRepaired);
            }
        }

        if (preg_match('/(\d{2})\D+(\d{2})\D+(\d{3,4})/', $s, $m)) {
            $y = $this->expandOcrYear($m[3]);
            if ($y !== null && $this->isValidCalendarDate((int) $y, (int) $m[2], (int) $m[1])) {
                return $this->finalizeIsoTracked(sprintf('%s-%s-%s', $y, $m[2], $m[1]), $ocrRepaired);
            }
        }

        $iso = $this->fixBrokenDate($s);
        if ($iso !== null && $ocrRepaired !== null) {
            $ocrRepaired = true;
        }

        return $iso;
    }

    private function isPlausibleBirthYear(int $year): bool
    {
        $y = (int) date('Y');

        return $year >= 1920 && $year <= $y;
    }

    private function isValidCalendarDate(int $year, int $month, int $day): bool
    {
        if ($year < 1900 || $year > 2100) {
            return false;
        }

        return checkdate($month, $day, $year);
    }

    private function expandOcrYear(string $yRaw): ?string
    {
        if (preg_match('/^\d{4}$/', $yRaw)) {
            return $yRaw;
        }
        if (preg_match('/^(\d{3})$/', $yRaw, $m)) {
            $y = $m[1];

            return ($y[0] === '9') ? '1'.$y : '2'.$y;
        }

        return null;
    }

    /**
     * @param  Normalized  $out
     */
    private function mergeLineBasedOcr(string $text, array &$out): void
    {
        $lines = preg_split('/\R+/', $text) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if ($out['nome'] === null && preg_match('/^nome\s*[:\-]\s*(.+)$/iu', $line, $m)) {
                $nomeLinha = trim(preg_split('/\b(cpf|categoria|validade|cat|doc|rg)\b/iu', $m[1], 2)[0] ?? $m[1]);
                $out['nome'] = $this->titleCaseName($nomeLinha);

                continue;
            }
            if ($out['cpf'] === null && preg_match('/^(?:cpf|doc)\s*[:\-]\s*([\d.\-]+)$/iu', $line, $m)) {
                $d = DocumentoBrasil::apenasDigitos($m[1]);
                if (strlen($d) === 11 && DocumentoBrasil::cpfValido($d)) {
                    $out['cpf'] = DocumentoBrasil::formatarCpf($d);
                }

                continue;
            }
            if ($out['categoria_cnh'] === null && preg_match('/^(?:categoria|cat)\s*[:\-]\s*([A-Z0-9]+)/iu', $line, $m)) {
                $out['categoria_cnh'] = strtoupper($m[1]);

                continue;
            }
            if ($out['validade_cnh'] === null && preg_match('/^(?:validade|val)\s*[:\-]\s*([\d\/.\-]+)/iu', $line, $m)) {
                $out['validade_cnh'] = $this->parseDateBrToIso($m[1]);

                continue;
            }
            if ($out['primeira_habilitacao'] === null && preg_match('/^(?:1ª?\s*hab|primeira\s*hab|1a\s*hab)\s*[:\-]\s*([\d\/.\-]+)/iu', $line, $m)) {
                $out['primeira_habilitacao'] = $this->parseDateBrToIso($m[1]);

                continue;
            }
            if ($out['numero_cnh'] === null && preg_match('/^(?:registro|renach|cnh)\s*[:\-]\s*([A-Z0-9]+)/iu', $line, $m)) {
                $out['numero_cnh'] = strtoupper($m[1]);
                $out['documento_identidade_numero'] ??= strtoupper($m[1]);

                continue;
            }
        }
    }

    /**
     * @param  Normalized  $out
     */
    private function mergeStructuralCnhFields(string $rawText, string $preparedText, array &$out): void
    {
        if ($out['primeira_habilitacao'] === null && preg_match(
            '/(?:1[ªaA]?\s*HABIL|PRIMEIRA\s+HABIL)[^\[]*\[([^\]\n]+)/iu',
            $rawText,
            $m
        )) {
            $out['primeira_habilitacao'] = $this->parseFlexibleBrDate(trim($m[1]));
        }

        if ($out['primeira_habilitacao'] === null && preg_match(
            '/HABILITA(?:ÇÃO|CAO|CÃO)\s*\([^\)]+\)\s*\[([^\]\n]+)/iu',
            $rawText,
            $m
        )) {
            $out['primeira_habilitacao'] = $this->parseFlexibleBrDate(trim($m[1]));
        }

        if ($out['primeira_habilitacao'] === null && preg_match(
            '/\)\s*\[([0-9OONRnrIilL\/.\-\s]{5,})\]/u',
            $preparedText,
            $m
        )) {
            $out['primeira_habilitacao'] = $this->parseFlexibleBrDate(trim($m[1]));
        }

        if ($out['numero_cnh'] === null && preg_match(
            '/(?:REGISTRO|RENACH|N[º°\.]?\s*REG)[^\d]{0,18}(\d{9,11})\b/iu',
            $preparedText,
            $m
        )) {
            $out['numero_cnh'] = $m[1];
            $out['documento_identidade_numero'] ??= $m[1];
        }

        if ($out['naturalidade'] === null && preg_match(
            '/NASCIMENTO[^\[]*\[[^\],]+,\s*([A-ZÀ-Ú\s]{3,40})/iu',
            $rawText,
            $m
        )) {
            $out['naturalidade'] = trim($m[1]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{nascimento_repaired?: bool}|null  $qrMeta
     * @return Normalized
     */
    private function mapAssociative(array $data, ?array &$qrMeta = null): array
    {
        $out = $this->emptyNormalized();

        $nome = $this->firstString($data, ['nome', 'name', 'NOME', 'nome_completo', 'nomeCompleto']);
        if ($nome !== null) {
            $out['nome'] = $this->titleCaseName($nome);
        }

        $cpfRaw = $this->firstString($data, ['cpf', 'CPF', 'documento', 'doc']);
        if ($cpfRaw !== null) {
            $d = DocumentoBrasil::apenasDigitos($cpfRaw);
            if (strlen($d) === 11 && DocumentoBrasil::cpfValido($d)) {
                $out['cpf'] = DocumentoBrasil::formatarCpf($d);
            }
        }

        $nasc = $this->firstString($data, ['data_nascimento', 'dataNascimento', 'nascimento', 'dt_nascimento']);
        if ($nasc !== null) {
            $nascRepaired = false;
            $out['data_nascimento'] = $this->parseAnyDateToIso($nasc, $nascRepaired);
            if ($qrMeta !== null) {
                $qrMeta['nascimento_repaired'] = $nascRepaired;
            }
        }

        $rg = $this->firstString($data, ['rg', 'RG', 'documento_rg']);
        if ($rg !== null) {
            $out['documento_identidade_numero'] = trim($rg);
        }

        $reg = $this->firstString($data, ['registro_cnh', 'registro', 'numero_cnh', 'numeroCnh', 'renach']);
        if ($reg !== null) {
            $out['numero_cnh'] = strtoupper(preg_replace('/\s+/', '', $reg) ?? $reg);
            $out['documento_identidade_numero'] ??= $out['numero_cnh'];
        }

        $org = $this->firstString($data, ['orgao_emissor', 'orgaoEmissor', 'orgao', 'detran']);
        if ($org !== null) {
            $out['orgao_emissor'] = $this->normalizeOrgaoEmissor($org);
        }

        $cat = $this->firstString($data, ['categoria', 'categoria_cnh', 'cat']);
        if ($cat !== null) {
            $out['categoria_cnh'] = strtoupper(preg_replace('/\s+/', '', $cat) ?? $cat);
        }

        $val = $this->firstString($data, ['validade', 'validade_cnh', 'data_validade_cnh']);
        if ($val !== null) {
            $out['validade_cnh'] = $this->parseAnyDateToIso($val);
        }

        $ph = $this->firstString($data, ['primeira_habilitacao', 'primeiraHabilitacao', '1_habilitacao', 'data_primeira_habilitacao']);
        if ($ph !== null) {
            $out['primeira_habilitacao'] = $this->parseAnyDateToIso($ph);
        }

        $nat = $this->firstString($data, ['naturalidade', 'local_nascimento', 'municipio_nascimento']);
        if ($nat !== null) {
            $out['naturalidade'] = trim($nat);
        }

        $filiacao = $data['filiacao'] ?? $data['filiacao_nome'] ?? null;
        if (is_array($filiacao)) {
            $pai = $this->firstString($filiacao, ['pai', 'nome_pai', 'nomePai', 'father']);
            $mae = $this->firstString($filiacao, ['mae', 'nome_mae', 'nomeMae', 'mother']);
            if ($pai !== null) {
                $out['nome_pai'] = $this->titleCaseName($pai);
            }
            if ($mae !== null) {
                $out['nome_mae'] = $this->titleCaseName($mae);
            }
        }

        $pai = $this->firstString($data, ['nome_pai', 'nomePai', 'filiacao_pai']);
        if ($pai !== null) {
            $out['nome_pai'] = $this->titleCaseName($pai);
        }
        $mae = $this->firstString($data, ['nome_mae', 'nomeMae', 'filiacao_mae']);
        if ($mae !== null) {
            $out['nome_mae'] = $this->titleCaseName($mae);
        }

        return $out;
    }

    /**
     * @return Normalized
     */
    private function emptyNormalized(): array
    {
        return [
            'nome' => null,
            'cpf' => null,
            'data_nascimento' => null,
            'documento_identidade_numero' => null,
            'orgao_emissor' => null,
            'numero_cnh' => null,
            'categoria_cnh' => null,
            'validade_cnh' => null,
            'primeira_habilitacao' => null,
            'naturalidade' => null,
            'nome_pai' => null,
            'nome_mae' => null,
            'nome_score' => 0,
            'cpf_score' => 0,
            'nascimento_score' => 0,
            'confidence_score' => 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $keys
     */
    private function firstString(array $data, array $keys): ?string
    {
        foreach ($keys as $k) {
            if (! array_key_exists($k, $data)) {
                continue;
            }
            $v = $data[$k];
            if (is_string($v) && trim($v) !== '') {
                return trim($v);
            }
        }

        return null;
    }

    private function tryParseLooseJson(string $raw): ?array
    {
        if (preg_match('/\{[\s\S]*\}/', $raw, $m)) {
            $j = json_decode($m[0], true);
            if (is_array($j)) {
                return $j;
            }
        }

        return null;
    }

    private function titleCaseName(string $s): string
    {
        $s = preg_replace('/\s+/', ' ', trim($s)) ?? $s;

        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }

    private function parseDateBrToIso(string $s): ?string
    {
        $s = trim($s);
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $s, $m)) {
            $iso = sprintf('%s-%s-%s', $m[3], $m[2], $m[1]);

            return $this->fixBrokenDate($iso) ?? $iso;
        }

        $any = $this->parseAnyDateToIso($s);
        if ($any === null) {
            return null;
        }

        return $this->fixBrokenDate($any) ?? $any;
    }

    private function parseAnyDateToIso(string $s, ?bool &$repaired = null): ?string
    {
        $s = trim($s);
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
                $fixed = $this->fixBrokenDate($s);
                $out = $fixed ?? $s;
                if ($repaired !== null && $fixed !== null && $fixed !== $s) {
                    $repaired = true;
                }

                return $out;
            }
            $c = Carbon::parse($s);
            $iso = $c->format('Y-m-d');
            $fixed = $this->fixBrokenDate($iso);
            $out = $fixed ?? $iso;
            if ($repaired !== null && $fixed !== null && $fixed !== $iso) {
                $repaired = true;
            }

            return $out;
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeOrgaoEmissor(string $org): string
    {
        $org = strtoupper(trim($org));
        $org = preg_replace('/\s+/', '', $org) ?? $org;
        if (preg_match('/DETRAN\/?([A-Z]{2})/', $org, $m)) {
            return 'DETRAN/'.$m[1];
        }

        return trim($org);
    }
}
