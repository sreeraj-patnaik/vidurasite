<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/settings_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/organizing_team.php");
    exit;
}

$settings = getSiteSettings($pdo);
$settingsId = (int) $settings['id'];

$config = defaultOrganizingTeamConfig();
$postedTeam = $_POST['organizing_team'] ?? [];

if (is_array($postedTeam)) {
    $config = array_replace_recursive($config, $postedTeam);
}

$uploadDir = '../uploads/settings/team/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploadPhoto = function (string $field, string $current = '') use ($uploadDir): string {
    if (
        !isset($_FILES['team_photos']['name'][$field]) ||
        ($_FILES['team_photos']['error'][$field] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    ) {
        return $current;
    }

    $extension = strtolower(pathinfo($_FILES['team_photos']['name'][$field], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowed, true)) {
        throw new RuntimeException("Invalid file type for {$field}.");
    }

    $newFile = $field . '_' . uniqid() . '.' . $extension;

    if (!move_uploaded_file($_FILES['team_photos']['tmp_name'][$field], $uploadDir . $newFile)) {
        throw new RuntimeException("Unable to upload {$field} image.");
    }

    if (!empty($current) && is_file($uploadDir . basename($current))) {
        unlink($uploadDir . basename($current));
    }

    return 'uploads/settings/team/' . $newFile;
};

$getNestedUpload = function (array $pathSegments): ?array {
    $fileBag = $_FILES['team_photos'] ?? null;

    if (!is_array($fileBag)) {
        return null;
    }

    foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $metaKey) {
        if (!isset($fileBag[$metaKey])) {
            return null;
        }
    }

    $name = $fileBag['name'];
    $tmpName = $fileBag['tmp_name'];
    $error = $fileBag['error'];

    foreach ($pathSegments as $segment) {
        if (!isset($name[$segment], $tmpName[$segment], $error[$segment])) {
            return null;
        }
        $name = $name[$segment];
        $tmpName = $tmpName[$segment];
        $error = $error[$segment];
    }

    if ($error !== UPLOAD_ERR_OK) {
        return null;
    }

    return [
        'name' => $name,
        'tmp_name' => $tmpName,
    ];
};

$uploadNestedPhoto = function (array $pathSegments, string $current = '') use ($uploadDir, $getNestedUpload): string {
    $uploaded = $getNestedUpload($pathSegments);

    if ($uploaded === null) {
        return $current;
    }

    $fieldName = end($pathSegments) ?: 'photo';
    $extension = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowed, true)) {
        throw new RuntimeException("Invalid file type for {$fieldName}.");
    }

    $newFile = $fieldName . '_' . uniqid() . '.' . $extension;

    if (!move_uploaded_file($uploaded['tmp_name'], $uploadDir . $newFile)) {
        throw new RuntimeException("Unable to upload {$fieldName} image.");
    }

    if (!empty($current) && is_file($uploadDir . basename($current))) {
        unlink($uploadDir . basename($current));
    }

    return 'uploads/settings/team/' . $newFile;
};

$resolveMemberId = function ($value) use ($pdo): ?int {
    $studentId = (int) $value;

    if ($studentId <= 0) {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM users
        WHERE id = ?
          AND role = 'member'
        LIMIT 1
    ");
    $stmt->execute([$studentId]);

    return $stmt->fetchColumn() ? $studentId : null;
};

$config['top']['patron']['label'] = trim($config['top']['patron']['label'] ?? 'Patron (HoD)');
$config['top']['patron']['name'] = trim($config['top']['patron']['name'] ?? '');
$config['top']['faculty_coordinator']['label'] = trim($config['top']['faculty_coordinator']['label'] ?? 'Faculty Coordinator');
$config['top']['faculty_coordinator']['name'] = trim($config['top']['faculty_coordinator']['name'] ?? '');

$config['top']['student_president']['label'] = trim($config['top']['student_president']['label'] ?? 'Student President');
$config['top']['student_president']['user_id'] = $resolveMemberId($config['top']['student_president']['user_id'] ?? 0);
$config['top']['finance_secretary']['label'] = trim($config['top']['finance_secretary']['label'] ?? 'Finance Secretary');
$config['top']['finance_secretary']['user_id'] = $resolveMemberId($config['top']['finance_secretary']['user_id'] ?? 0);

foreach (($config['clubs'] ?? []) as $clubKey => &$club) {
    $club['title'] = trim($club['title'] ?? strtoupper($clubKey));
    $club['accent'] = trim($club['accent'] ?? '#1d4ed8');
    $club['coordinator']['label'] = trim($club['coordinator']['label'] ?? 'Coordinator');
    $club['coordinator']['photo'] = trim($club['coordinator']['photo'] ?? '');
    $club['coordinator']['user_id'] = $resolveMemberId($club['coordinator']['user_id'] ?? 0);

    foreach (($club['roles'] ?? []) as $index => &$role) {
        $role['label'] = trim($role['label'] ?? ('Role ' . ($index + 1)));
        $role['user_id'] = $resolveMemberId($role['user_id'] ?? 0);
    }
    unset($role);
}
unset($club);

foreach (($config['year_coordinators'] ?? []) as $index => &$role) {
    $role['label'] = trim($role['label'] ?? (($index + 1) . ' Year Coordinator'));
    $role['user_id'] = $resolveMemberId($role['user_id'] ?? 0);
}
unset($role);

$config['members_label'] = trim($config['members_label'] ?? 'STUDENT MEMBERS');

try {
    $config['top']['patron']['photo'] = $uploadPhoto('patron', $settings['patron_photo'] ?? ($config['top']['patron']['photo'] ?? ''));
    $config['top']['faculty_coordinator']['photo'] = $uploadPhoto('faculty_coordinator', $config['top']['faculty_coordinator']['photo'] ?? '');

    foreach (array_keys($config['clubs'] ?? []) as $clubKey) {
        $current = $config['clubs'][$clubKey]['coordinator']['photo'] ?? '';
        $config['clubs'][$clubKey]['coordinator']['photo'] = $uploadNestedPhoto(['clubs', $clubKey, 'coordinator'], $current);
    }

    $encoded = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt = $pdo->prepare("
        UPDATE settings
        SET
            patron_name = ?,
            patron_photo = ?,
            faculty_coordinator_name = ?,
            student_president_user_id = ?,
            finance_secretary_user_id = ?,
            organizing_team_json = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $config['top']['patron']['name'] ?: null,
        $config['top']['patron']['photo'] ?: null,
        $config['top']['faculty_coordinator']['name'] ?: null,
        $config['top']['student_president']['user_id'],
        $config['top']['finance_secretary']['user_id'],
        $encoded,
        $settingsId,
    ]);

    $_SESSION['success'] = 'Organizing team updated successfully.';
} catch (Throwable $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../admin/organizing_team.php");
exit;
