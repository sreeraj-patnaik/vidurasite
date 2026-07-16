<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../member/profile.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("
SELECT *
FROM users
WHERE id=?
LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: ../member/profile.php");
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$password = trim($_POST['password'] ?? '');
$profilePhoto = $user['profile_photo'];

if ($name === '') {
    $_SESSION['error'] = "Name is required.";
    header("Location: ../member/profile.php");
    exit;
}

if (
    isset($_FILES['profile_photo']) &&
    $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK
) {
    $uploadDir = "../uploads/profiles/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($extension, $allowed, true)) {
        $newPhoto = uniqid('profile_') . '.' . $extension;

        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . $newPhoto)) {
            if (!empty($profilePhoto) && is_file($uploadDir . $profilePhoto)) {
                unlink($uploadDir . $profilePhoto);
            }
            $profilePhoto = $newPhoto;
        }
    } else {
        $_SESSION['error'] = "Invalid photo format.";
        header("Location: ../member/profile.php");
        exit;
    }
}

$sql = "
UPDATE users
SET
name=?,
phone=?,
bio=?,
profile_photo=?
";

$params = [$name, $phone, $bio, $profilePhoto];

if ($password !== '') {
    $sql .= ", password=?";
    $params[] = password_hash($password, PASSWORD_DEFAULT);
}

$sql .= "
WHERE id=?
";
$params[] = $userId;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$_SESSION['name'] = $name;
$_SESSION['success'] = "Profile updated successfully.";

header("Location: ../member/profile.php");
exit;
