<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../admin/gallery.php");
    exit;
}

$stmt = $pdo->prepare("
SELECT image
FROM gallery
WHERE id=?
");
$stmt->execute([$id]);
$image = $stmt->fetchColumn();

if ($image) {
    $path = "../uploads/gallery/" . $image;
    if (is_file($path)) {
        unlink($path);
    }
}

$stmt = $pdo->prepare("
DELETE FROM gallery
WHERE id=?
");
$stmt->execute([$id]);

$_SESSION['success'] = "Gallery image deleted successfully.";
header("Location: ../admin/gallery.php");
exit;
