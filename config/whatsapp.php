<?php

function normalize_whatsapp_number(string $value): ?string
{
    $value = trim($value);
    if ($value === '' || preg_match('/[^0-9\s()+-]/', $value)) {
        return null;
    }

    $number = (string) preg_replace('/\D+/', '', $value);
    if (!preg_match('/^[1-9][0-9]{7,14}$/', $number)) {
        return null;
    }

    return $number;
}

function whatsapp_business_number(PDO $pdo): string
{
    $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = :setting_key LIMIT 1');
    $stmt->execute(['setting_key' => 'whatsapp_business_number']);
    $value = $stmt->fetchColumn();
    $number = is_string($value) ? normalize_whatsapp_number($value) : null;

    return $number ?? '';
}
