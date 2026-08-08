<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/settings_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/settings.php");
    exit;
}

$settings = getSiteSettings($pdo);
$settingsId = (int) $settings['id'];

$data = [
    'website_title' => trim($_POST['website_title'] ?? ''),
    'membership_fee' => !empty($_POST['membership_fee']) ? (int) $_POST['membership_fee'] : null,
    'semester' => trim($_POST['semester'] ?? ''),
    'contact_email' => trim($_POST['contact_email'] ?? ''),
];

$imageFields = [
    'homepage_banner' => 'assets/images/hero.png',
    'techkruti_image' => 'assets/images/techkruti.png',
    'khelkruti_image' => 'assets/images/khelkruti.png',
    'samskruti_image' => 'assets/images/samskruti.png',
    'liet_logo' => 'assets/images/liet_logo.png',
    'vidura_logo' => 'assets/images/vidura_logo.png',
];

$uploadDir = '../uploads/settings/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

foreach ($imageFields as $field => $fallback) {
    $currentFile = $settings[$field] ?? null;

    if (
        isset($_FILES[$field]) &&
        $_FILES[$field]['error'] === UPLOAD_ERR_OK
    ) {
        $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $allowed, true)) {
            $_SESSION['error'] = "Invalid file type for {$field}.";
            header("Location: ../admin/settings.php");
            exit;
        }

        $newFile = $field . '_' . uniqid() . '.' . $extension;

        if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $newFile)) {
            if (!empty($currentFile) && is_file($uploadDir . $currentFile)) {
                unlink($uploadDir . $currentFile);
            }
            $currentFile = $newFile;
        }
    }

    $data[$field] = $currentFile;
}

$stmt = $pdo->prepare("
    UPDATE settings
    SET
        website_title = ?,
        membership_fee = ?,
        semester = ?,
        contact_email = ?,
        homepage_banner = ?,
        techkruti_image = ?,
        khelkruti_image = ?,
        samskruti_image = ?,
        liet_logo = ?,
        vidura_logo = ?
    WHERE id = ?
");

$stmt->execute([
    $data['website_title'],
    $data['membership_fee'],
    $data['semester'],
    $data['contact_email'],
    $data['homepage_banner'],
    $data['techkruti_image'],
    $data['khelkruti_image'],
    $data['samskruti_image'],
    $data['liet_logo'],
    $data['vidura_logo'],
    $settingsId,
]);

$_SESSION['success'] = 'Settings updated successfully.';
header("Location: ../admin/settings.php");
exit;
