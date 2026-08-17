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
        'patron_name' => 'TEXT',
        'patron_photo' => 'TEXT',
        'faculty_coordinator_name' => 'TEXT',
        'student_president_user_id' => 'INTEGER',
        'finance_secretary_user_id' => 'INTEGER',
        'organizing_team_json' => 'TEXT',
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

function defaultOrganizingTeamConfig(): array
{
    return [
        'top' => [
            'patron' => [
                'label' => 'Patron (HoD)',
                'name' => '',
                'photo' => '',
            ],
            'faculty_coordinator' => [
                'label' => 'Faculty Coordinator',
                'name' => '',
                'photo' => '',
            ],
            'student_president' => [
                'label' => 'Student President',
                'user_id' => null,
            ],
            'finance_secretary' => [
                'label' => 'Finance Secretary',
                'user_id' => null,
            ],
        ],
        'clubs' => [
            'techkruti' => [
                'title' => 'TECHKRUTI',
                'accent' => '#ea580c',
                'coordinator' => [
                    'label' => 'Coordinator',
                    'photo' => '',
                    'user_id' => null,
                ],
                'roles' => [
                    ['label' => 'Events Head', 'user_id' => null],
                    ['label' => 'Treasurer', 'user_id' => null],
                    ['label' => 'Tech Lead', 'user_id' => null],
                ],
            ],
            'khelkruti' => [
                'title' => 'KHELKRUTI',
                'accent' => '#15803d',
                'coordinator' => [
                    'label' => 'Coordinator',
                    'photo' => '',
                    'user_id' => null,
                ],
                'roles' => [
                    ['label' => 'Role 1', 'user_id' => null],
                    ['label' => 'Role 2', 'user_id' => null],
                    ['label' => 'Role 3', 'user_id' => null],
                ],
            ],
            'samskruti' => [
                'title' => 'SAMSKRUTI',
                'accent' => '#7e22ce',
                'coordinator' => [
                    'label' => 'Coordinator',
                    'photo' => '',
                    'user_id' => null,
                ],
                'roles' => [
                    ['label' => 'Role 1', 'user_id' => null],
                    ['label' => 'Role 2', 'user_id' => null],
                    ['label' => 'Role 3', 'user_id' => null],
                ],
            ],
        ],
        'year_coordinators' => [
            ['label' => 'I Year Coordinator', 'user_id' => null],
            ['label' => 'II Year Coordinator', 'user_id' => null],
            ['label' => 'III Year Coordinator', 'user_id' => null],
            ['label' => 'IV Year Coordinator', 'user_id' => null],
        ],
        'members_label' => 'STUDENT MEMBERS',
    ];
}

function getOrganizingTeamConfig(PDO $pdo): array
{
    $settings = getSiteSettings($pdo);
    $config = defaultOrganizingTeamConfig();

    if (!empty($settings['organizing_team_json'])) {
        $decoded = json_decode($settings['organizing_team_json'], true);
        if (is_array($decoded)) {
            $config = array_replace_recursive($config, $decoded);
        }
    } else {
        $config['top']['patron']['name'] = $settings['patron_name'] ?? '';
        $config['top']['patron']['photo'] = $settings['patron_photo'] ?? '';
        $config['top']['faculty_coordinator']['name'] = $settings['faculty_coordinator_name'] ?? '';
        $config['top']['student_president']['user_id'] = (int) ($settings['student_president_user_id'] ?? 0) ?: null;
        $config['top']['finance_secretary']['user_id'] = (int) ($settings['finance_secretary_user_id'] ?? 0) ?: null;
    }

    return $config;
}

function getOrganizingTeamRoleLabels(PDO $pdo, int $userId): array
{
    $config = getOrganizingTeamConfig($pdo);
    $roles = [];

    $topRoles = [
        $config['top']['student_president'] ?? null,
        $config['top']['finance_secretary'] ?? null,
    ];

    foreach ($topRoles as $role) {
        if (!empty($role['user_id']) && (int) $role['user_id'] === $userId && !empty($role['label'])) {
            $roles[] = $role['label'];
        }
    }

    foreach (($config['clubs'] ?? []) as $club) {
        if (!empty($club['coordinator']['user_id']) && (int) $club['coordinator']['user_id'] === $userId && !empty($club['coordinator']['label'])) {
            $roles[] = $club['title'] . ' ' . $club['coordinator']['label'];
        }

        foreach (($club['roles'] ?? []) as $role) {
            if (!empty($role['user_id']) && (int) $role['user_id'] === $userId && !empty($role['label'])) {
                $roles[] = $club['title'] . ' ' . $role['label'];
            }
        }
    }

    foreach (($config['year_coordinators'] ?? []) as $role) {
        if (!empty($role['user_id']) && (int) $role['user_id'] === $userId && !empty($role['label'])) {
            $roles[] = $role['label'];
        }
    }

    return array_values(array_unique($roles));
}

function getOrganizingTeamUserMap(PDO $pdo, array $config): array
{
    $ids = [];

    $addId = static function ($value) use (&$ids): void {
        $id = (int) $value;
        if ($id > 0) {
            $ids[$id] = $id;
        }
    };

    $addId($config['top']['student_president']['user_id'] ?? 0);
    $addId($config['top']['finance_secretary']['user_id'] ?? 0);

    foreach (($config['clubs'] ?? []) as $club) {
        $addId($club['coordinator']['user_id'] ?? 0);

        foreach (($club['roles'] ?? []) as $role) {
            $addId($role['user_id'] ?? 0);
        }
    }

    foreach (($config['year_coordinators'] ?? []) as $role) {
        $addId($role['user_id'] ?? 0);
    }

    if (empty($ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        SELECT id, name, roll_number, profile_photo
        FROM users
        WHERE id IN ($placeholders)
    ");
    $stmt->execute(array_values($ids));

    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        $map[(int) $row['id']] = $row;
    }

    return $map;
}
