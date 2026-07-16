<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$color = trim($_POST['color'] ?? '');

$icon = null;

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

        $icon = uniqid("badge_") . "." . $ext;

        if (!move_uploaded_file(
            $_FILES['icon']['tmp_name'],
            $uploadDir . $icon
        )) {
            $icon = null;
        }
    }
}

$stmt = $pdo->prepare("
INSERT INTO badges
(title, description, icon, color)
VALUES (?, ?, ?, ?)
");

$stmt->execute([
    $title,
    $description,
    $icon,
    $color
]);

$_SESSION['success'] = "Badge created successfully.";

header("Location: ../admin/badges.php");
exit;
