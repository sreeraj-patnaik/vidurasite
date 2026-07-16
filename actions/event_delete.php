<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../admin/events.php");
    exit;
}

$stmt = $pdo->prepare("SELECT banner FROM events WHERE id=?");
$stmt->execute([$id]);
$banner = $stmt->fetchColumn();

if (!empty($banner)) {
    $path = "../uploads/events/" . $banner;
    if (is_file($path)) {
        unlink($path);
    }
}

$stmt = $pdo->prepare("DELETE FROM events WHERE id=?");
$stmt->execute([$id]);

$_SESSION['success'] = "Event deleted successfully.";
header("Location: ../admin/events.php");
exit;
