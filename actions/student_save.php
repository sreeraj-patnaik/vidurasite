<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../admin/students.php");
    exit;
}

/* -----------------------------
   Read Form Data
------------------------------*/

$name        = trim($_POST['name']);
$roll        = trim($_POST['roll_number']);
$email       = trim($_POST['email']);
$password    = password_hash($_POST['password'], PASSWORD_DEFAULT);
$phone       = trim($_POST['phone']);
$department  = trim($_POST['department']);
$year        = intval($_POST['year']);
$section     = trim($_POST['section']);
$club_id     = !empty($_POST['club_id']) ? intval($_POST['club_id']) : null;
$status      = $_POST['status'];
$role        = $_POST['role'];
$bio         = trim($_POST['bio']);

$profilePhoto = null;

/* -----------------------------
   Upload Profile Photo
------------------------------*/

if (
    isset($_FILES['profile_photo']) &&
    $_FILES['profile_photo']['error'] == 0
) {

    $uploadDir = "../uploads/profiles/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = strtolower(pathinfo(
        $_FILES['profile_photo']['name'],
        PATHINFO_EXTENSION
    ));

    $allowed = ['jpg','jpeg','png','webp'];

    if (in_array($extension, $allowed)) {

        $profilePhoto =
            uniqid("profile_") . "." . $extension;

        move_uploaded_file(
            $_FILES['profile_photo']['tmp_name'],
            $uploadDir . $profilePhoto
        );

    }

}

/* -----------------------------
   Duplicate Checks
------------------------------*/

$stmt = $pdo->prepare("
SELECT id
FROM users
WHERE email = ?
");

$stmt->execute([$email]);

if ($stmt->fetch()) {

    die("Email already exists.");

}

$stmt = $pdo->prepare("
SELECT id
FROM users
WHERE roll_number = ?
");

$stmt->execute([$roll]);

if ($stmt->fetch()) {

    die("Roll Number already exists.");

}

/* -----------------------------
   Insert Student
------------------------------*/

$stmt = $pdo->prepare("

INSERT INTO users
(

roll_number,

name,

email,

password,

phone,

department,

year,

section,

club_id,

profile_photo,

bio,

points,

level,

status,

role,

joined_at,

created_at

)

VALUES
(

?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?

)

");

$stmt->execute([

$roll,

$name,

$email,

$password,

$phone,

$department,

$year,

$section,

$club_id,

$profilePhoto,

$bio,

0,

1,

$status,

$role,

date("Y-m-d H:i:s"),

date("Y-m-d H:i:s")

]);

header("Location: ../admin/students.php");

exit;