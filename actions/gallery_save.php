<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/gallery.php");
    exit;
}

$title = trim($_POST['title'] ?? '');
$eventId = intval($_POST['event_id'] ?? 0);
$image = null;

if ($title === '') {
    $_SESSION['error'] = "Gallery title is required.";
    header("Location: ../admin/gallery.php");
    exit;
}

if (
    !isset($_FILES['image']) ||
    $_FILES['image']['error'] !== UPLOAD_ERR_OK
) {
    $_SESSION['error'] = "Please upload an image.";
    header("Location: ../admin/gallery.php");
    exit;
}

$uploadDir = "../uploads/gallery/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $allowed, true)) {
    $_SESSION['error'] = "Invalid image format.";
    header("Location: ../admin/gallery.php");
    exit;
}

$image = uniqid('gallery_') . '.' . $ext;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image)) {
    $_SESSION['error'] = "Image upload failed.";
    header("Location: ../admin/gallery.php");
    exit;
}

$stmt = $pdo->prepare("
INSERT INTO gallery
(title, image, event_id, uploaded_by, uploaded_at)
VALUES (?, ?, ?, ?, NOW())
");

$stmt->execute([
    $title,
    $image,
    $eventId > 0 ? $eventId : null,
    $_SESSION['user_id']
]);

$_SESSION['success'] = "Gallery image uploaded successfully.";
header("Location: ../admin/gallery.php");
exit;
