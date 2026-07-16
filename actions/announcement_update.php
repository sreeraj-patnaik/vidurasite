<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_POST['id'] ?? 0);

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
UPDATE announcements
SET
title=?,
description=?,
expires_at=?
WHERE id=?
");

$stmt->execute([
    $title,
    $description,
    $expiresAt,
    $id
]);

$_SESSION['success'] = "Announcement updated successfully.";

header("Location: ../admin/announcements.php");
exit;
