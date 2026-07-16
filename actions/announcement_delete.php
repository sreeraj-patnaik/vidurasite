<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
DELETE FROM announcements
WHERE id=?
");

$stmt->execute([$id]);

$_SESSION['success'] = "Announcement deleted successfully.";

header("Location: ../admin/announcements.php");
exit;