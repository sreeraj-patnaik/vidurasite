<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("
SELECT icon
FROM badges
WHERE id=?
");

$stmt->execute([$id]);

$badge = $stmt->fetch();

if ($badge && !empty($badge['icon'])) {

    $path = "../uploads/badges/" . $badge['icon'];

    if (file_exists($path)) {
        unlink($path);
    }
}

$stmt = $pdo->prepare("
DELETE FROM badges
WHERE id=?
");

$stmt->execute([$id]);

$_SESSION['success'] = "Badge deleted successfully.";

header("Location: ../admin/badges.php");
exit;