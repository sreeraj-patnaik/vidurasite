<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_POST['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT *
FROM badges
WHERE id=?
");

$stmt->execute([$id]);

$badge = $stmt->fetch();

if (!$badge) {
    die("Badge not found.");
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$color = trim($_POST['color'] ?? '');

$icon = $badge['icon'];
$newIcon = $icon;
$newIconUploaded = false;

if (
    isset($_FILES['icon']) &&
    $_FILES['icon']['error'] == 0
) {

    $uploadDir = "../uploads/badges/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

    if (in_array($ext, $allowed, true)) {
        $newIcon = uniqid("badge_") . "." . $ext;

        if (move_uploaded_file(
            $_FILES['icon']['tmp_name'],
            $uploadDir . $newIcon
        )) {
            $newIconUploaded = true;
        } else {
            $newIcon = $icon;
        }
    }
}

$stmt = $pdo->prepare("
UPDATE badges
SET
title=?,
description=?,
icon=?,
color=?
WHERE id=?
");

$stmt->execute([
    $title,
    $description,
    $newIcon,
    $color,
    $id
]);

if (
    $newIconUploaded &&
    !empty($icon) &&
    $newIcon !== $icon &&
    file_exists($uploadDir . $icon)
) {
    unlink($uploadDir . $icon);
}

$_SESSION['success'] = "Badge updated successfully.";

header("Location: ../admin/badges.php");
exit;
