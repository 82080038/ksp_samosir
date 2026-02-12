<?php

/**
 * Formatter & normalizer untuk rupiah, angka, tanggal, dan nomor telepon.
 */


function format_rupiah($value, int $fraction = 0): string
{
    $num = to_number_safe($value);
    if ($num === null) return '';
    return 'Rp' . number_format($num, $fraction, ',', '.');
}

function format_number($value, int $fraction = 2): string
{
    $num = to_number_safe($value);
    if ($num === null) return '';
    return number_format($num, $fraction, ',', '.');
}

function to_number_safe($value): ?float
{
    if ($value === null || $value === '') return null;
    if (is_numeric($value)) return (float)$value;
    // Bersihkan karakter selain digit, koma, titik, minus
    $str = preg_replace('/[^0-9,.-]/', '', (string)$value);
    $str = str_replace(',', '.', $str);
    if (!is_numeric($str)) return null;
    return (float)$str;
}

function format_date_id($value, bool $with_time = false): string
{
    try {
        $dt = new DateTime($value);
    } catch (Throwable $e) {
        return '';
    }
    return $dt->format($with_time ? 'd M Y H:i' : 'd M Y');
}

function normalize_phone($input): string
{
    if (!$input) return '';
    $raw = trim((string)$input);
    $cleaned = preg_replace('/(?!^\+)[^0-9]/', '', $raw);
    if (strpos($cleaned, '+62') === 0) return $cleaned;
    if (strpos($cleaned, '62') === 0) return '+' . $cleaned;
    if (strpos($cleaned, '0') === 0) return '+62' . substr($cleaned, 1);
    return strpos($cleaned, '+') === 0 ? $cleaned : '+' . $cleaned;
}

function is_valid_phone($input): bool
{
    $norm = normalize_phone($input);
    return (bool) preg_match('/^\+62\d{8,13}$/', $norm);
}

// ---------------- Email & URL ----------------
function normalize_email($input): string
{
    return strtolower(trim((string)$input));
}

function is_valid_email($input): bool
{
    return (bool) filter_var(normalize_email($input), FILTER_VALIDATE_EMAIL);
}

function sanitize_url($input): string
{
    $url = trim((string)$input);
    if (!$url) return '';
    $filtered = filter_var($url, FILTER_SANITIZE_URL);
    if (stripos($filtered, 'http') !== 0) {
        $filtered = 'https://' . $filtered;
    }
    return filter_var($filtered, FILTER_VALIDATE_URL) ? $filtered : '';
}

// ---------------- Identitas & nomor ----------------

function normalize_npwp($input): string
{
    return substr(preg_replace('/\D/', '', (string)$input), 0, 15);
}

function format_npwp($input): string
{
    $d = normalize_npwp($input);
    return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{1})(\d{3})(\d{3})/', '$1.$2.$3.$4-$5.$6', $d);
}

function is_valid_npwp($input): bool
{
    return strlen(normalize_npwp($input)) === 15;
}

function normalize_rekening($input): string
{
    return substr(preg_replace('/\D/', '', (string)$input), 0, 20);
}

function is_valid_rekening($input): bool
{
    $r = normalize_rekening($input);
    $len = strlen($r);
    return $len >= 8 && $len <= 20;
}

function normalize_kk($input): string
{
    return substr(preg_replace('/\D/', '', (string)$input), 0, 16);
}

function is_valid_kk($input): bool
{
    return strlen(normalize_kk($input)) === 16;
}

function normalize_sim($input): string
{
    return substr(preg_replace('/\D/', '', (string)$input), 0, 12);
}

function is_valid_sim($input): bool
{
    $sim = normalize_sim($input);
    $len = strlen($sim);
    return $len === 12;
}

function normalize_passport($input): string
{
    return substr(preg_replace('/[^A-Za-z0-9]/', '', strtoupper((string)$input)), 0, 9);
}

function is_valid_passport($input): bool
{
    $p = normalize_passport($input);
    $len = strlen($p);
    return $len >= 6 && $len <= 9;
}

function normalize_postal_code($input): string
{
    return substr(preg_replace('/\D/', '', (string)$input), 0, 5);
}

function is_valid_postal_code($input): bool
{
    return strlen(normalize_postal_code($input)) === 5;
}

// ---------------- Persentase ----------------
function parse_percent($input): ?float
{
    if ($input === null || $input === '') return null;
    $str = str_replace('%', '', (string)$input);
    $num = to_number_safe($str);
    if ($num === null) return null;
    return $num >= 1 ? $num / 100 : $num;
}

function format_percent($value, int $decimals = 2): string
{
    $num = is_numeric($value) ? (float)$value : parse_percent($value);
    if ($num === null) return '';
    $out = round($num * 100, $decimals);
    return rtrim(rtrim(number_format($out, $decimals, ',', '.'), '0'), ',') . '%';
}

// Angka lokal toleran (thousand separator campur)
function parse_localized_number($input): ?float
{
    if ($input === null || $input === '') return null;
    $str = preg_replace('/\s+/', '', (string)$input);
    $hasComma = strpos($str, ',') !== false;
    $hasDot = strpos($str, '.') !== false;
    if ($hasComma && $hasDot) {
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);
    } elseif ($hasComma && !$hasDot) {
        $str = str_replace(',', '.', $str);
    }
    $num = filter_var($str, FILTER_VALIDATE_FLOAT);
    return $num === false ? null : (float)$num;
}

// ---------------- RT/RW ----------------
function normalize_rt_rw($input, int $length = 2): string
{
    $digits = preg_replace('/\D/', '', (string)$input);
    return $digits ? str_pad(substr($digits, -$length), $length, '0', STR_PAD_LEFT) : '';
}

// ---------------- Code generator ----------------
function generate_code(string $prefix = 'KOP', int $randLength = 4): string
{
    $now = new DateTime();
    $rand = strtoupper(substr(bin2hex(random_bytes(8)), 0, $randLength));
    return sprintf('%s-%s%s%s-%s',
        $prefix,
        $now->format('Y'),
        $now->format('m'),
        $now->format('d'),
        $rand
    );
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function short_id(int $length = 6): string
{
    return strtoupper(substr(bin2hex(random_bytes(8)), 0, $length));
}

// ---------------- Nama & alamat ----------------
function title_case_name(string $text): string
{
    if (!$text) return '';
    $parts = preg_split('/\s+/', strtolower($text));
    $keep = ['bin', 'binti', 'al', 'de', 'van'];
    $parts = array_map(function ($w) use ($keep) {
        return in_array($w, $keep) ? $w : ucfirst($w);
    }, $parts);
    return implode(' ', $parts);
}

function title_case_address(string $text): string
{
    if (!$text) return '';
    $lowerWords = ['dan', 'di', 'ke', 'dari', 'rt', 'rw'];
    $parts = preg_split('/\s+/', strtolower($text));
    $parts = array_map(function ($w) use ($lowerWords) {
        return in_array($w, $lowerWords) ? $w : ucfirst($w);
    }, $parts);
    return implode(' ', $parts);
}

// ---------------- Virtual Account ----------------
function normalize_va($input): string
{
    return substr(preg_replace('/\D/', '', (string)$input), 0, 25);
}

function is_valid_va($input): bool
{
    $v = normalize_va($input);
    $len = strlen($v);
    return $len >= 8 && $len <= 25;
}

// ---------------- File upload pre-check (meta only) ----------------
function validate_file_meta(array $file, array $options = []): array
{
    $maxSizeMB = $options['maxSizeMB'] ?? 5;
    $allowedTypes = $options['allowedTypes'] ?? ['application/pdf', 'image/jpeg', 'image/png'];
    if (!isset($file['tmp_name'])) return ['ok' => false, 'error' => 'File tidak ditemukan'];
    $sizeOk = ($file['size'] ?? 0) <= $maxSizeMB * 1024 * 1024;
    $type = mime_content_type($file['tmp_name']);
    $typeOk = in_array($type, $allowedTypes);
    $errors = [];
    if (!$sizeOk) $errors[] = "Ukuran maksimal {$maxSizeMB}MB";
    if (!$typeOk) $errors[] = 'Tipe harus ' . implode(', ', $allowedTypes);
    return ['ok' => $sizeOk && $typeOk, 'error' => implode('; ', $errors), 'type' => $type];
}
