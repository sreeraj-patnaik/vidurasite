<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {

    $stmt = $pdo->prepare("
        DELETE FROM users
        WHERE id = ?
        AND role != 'admin'
    ");

    $stmt->execute([$id]);
}

header("Location: ../admin/students.php");
exit;