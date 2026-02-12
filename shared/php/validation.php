<?php

/**
 * Validator & sanitizer ringan.
 */

function sanitize_string(?string $value): string
{
    return trim(filter_var($value ?? '', FILTER_SANITIZE_STRING));
}

function is_required($value): bool
{
    return !is_null($value) && trim((string)$value) !== '';
}

function is_email(?string $value): bool
{
    $value = trim((string)$value);
    return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
}

function is_numeric_string($value): bool
{
    return (bool) preg_match('/^-?\d+(\.\d+)?$/', (string)$value);
}

function validate_length($value, int $min = 0, ?int $max = null): bool
{
    $len = mb_strlen((string)$value);
    if ($len < $min) return false;
    if (!is_null($max) && $len > $max) return false;
    return true;
}

function collect_errors(array $rules): array
{
    // $rules: ['field' => ['label' => 'Nama', 'value' => $v, 'rules' => ['required', ['length', 3, 50]]]]
    $errors = [];
    foreach ($rules as $field => $config) {
        $label = $config['label'] ?? $field;
        $value = $config['value'] ?? null;
        foreach ($config['rules'] as $rule) {
            $ok = true;
            if ($rule === 'required') $ok = is_required($value);
            elseif ($rule === 'email') $ok = is_email($value);
            elseif ($rule === 'numeric') $ok = is_numeric_string($value);
            elseif (is_array($rule) && $rule[0] === 'length') {
                $ok = validate_length($value, $rule[1] ?? 0, $rule[2] ?? null);
            }
            if (!$ok) {
                $errors[$field] = "{$label} tidak valid";
                break;
            }
        }
    }
    return $errors;
}

// Validasi input
function is_valid_nik($nik) {
    return preg_match('/^[0-9]{16}$/', $nik);
}

function normalize_nik($nik) {
    return preg_replace('/[^0-9]/', '', $nik);
}




