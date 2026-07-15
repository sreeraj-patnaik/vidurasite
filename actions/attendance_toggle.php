<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("
SELECT attended
FROM registrations
WHERE id=?
");

$stmt->execute([$id]);

$current = $stmt->fetchColumn();

$new = $current ? false : true;

$stmt = $pdo->prepare("
UPDATE registrations
SET attended=?
WHERE id=?
");

$stmt->execute([$new, $id]);

header("Location: ../admin/registrations.php");
exit;
