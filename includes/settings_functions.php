<?php

function ensureSettingsSchema(PDO $pdo): void
{
    $columns = [
        'homepage_banner' => 'TEXT',
        'techkruti_image' => 'TEXT',
        'khelkruti_image' => 'TEXT',
        'samskruti_image' => 'TEXT',
        'liet_logo' => 'TEXT',
        'vidura_logo' => 'TEXT',
    ];

    foreach ($columns as $column => $type) {
        $stmt = $pdo->prepare("
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = current_schema()
              AND table_name = 'settings'
              AND column_name = ?
            LIMIT 1
        ");
        $stmt->execute([$column]);

        if (!$stmt->fetchColumn()) {
            $pdo->exec("ALTER TABLE settings ADD COLUMN {$column} {$type}");
        }
    }
}

function getSiteSettings(PDO $pdo): array
{
    ensureSettingsSchema($pdo);

    $settings = $pdo->query("
        SELECT *
        FROM settings
        ORDER BY id ASC
        LIMIT 1
    ")->fetch();

    if (!$settings) {
        $pdo->exec("INSERT INTO settings DEFAULT VALUES");

        $settings = $pdo->query("
            SELECT *
            FROM settings
            ORDER BY id ASC
            LIMIT 1
        ")->fetch();
    }

    return $settings ?: [];
}

function settingsImageUrl(array $settings, string $field, string $fallback): string
{
    if (!empty($settings[$field])) {
        return BASE_URL . '/uploads/settings/' . rawurlencode(basename($settings[$field]));
    }

    return BASE_URL . '/' . ltrim($fallback, '/');
}
