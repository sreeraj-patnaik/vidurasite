<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$expiresAtRaw = trim($_POST['expires_at'] ?? '');

if ($title === '' || $description === '') {
    $_SESSION['error'] = "Please fill all required fields.";
    header("Location: ../admin/announcements.php");
    exit;
}

$expiresAt = null;

if ($expiresAtRaw !== '') {
    $expiresAtDate = DateTime::createFromFormat('Y-m-d\TH:i', $expiresAtRaw);

    if ($expiresAtDate instanceof DateTime) {
        $expiresAt = $expiresAtDate->format('Y-m-d H:i:s');
    }
}

$stmt = $pdo->prepare("
INSERT INTO announcements
(title, description, expires_at)
VALUES (?, ?, ?)
");

$stmt->execute([
    $title,
    $description,
    $expiresAt
]);

$_SESSION['success'] = "Announcement published successfully.";

header("Location: ../admin/announcements.php");
exit;
