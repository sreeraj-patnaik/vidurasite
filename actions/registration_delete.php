<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("
DELETE FROM registrations
WHERE id=?
");

$stmt->execute([$id]);

header("Location: ../admin/registrations.php");
exit;