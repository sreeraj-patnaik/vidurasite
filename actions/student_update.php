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

$id = intval($_POST['id']);

$stmt = $pdo->prepare("
SELECT *
FROM users
WHERE id=?
LIMIT 1
");

$stmt->execute([$id]);

$student = $stmt->fetch();

if(!$student){
    die("Student not found.");
}

$name        = trim($_POST['name']);
$roll        = trim($_POST['roll_number']);
$email       = trim($_POST['email']);
$phone       = trim($_POST['phone']);
$department  = trim($_POST['department']);
$year        = intval($_POST['year']);
$section     = trim($_POST['section']);
$club_id     = !empty($_POST['club_id']) ? intval($_POST['club_id']) : null;
$status      = $_POST['status'];
$role        = $_POST['role'];
$bio         = trim($_POST['bio']);

$password = $student['password'];

if(!empty($_POST['password'])){
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
}

$profilePhoto = $student['profile_photo'];

if(isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error']==0){

    $uploadDir="../uploads/profiles/";

    $ext=strtolower(pathinfo($_FILES['profile_photo']['name'],PATHINFO_EXTENSION));

    $allowed=['jpg','jpeg','png','webp'];

    if(in_array($ext,$allowed)){

        if(
            !empty($profilePhoto) &&
            file_exists($uploadDir.$profilePhoto)
        ){
            unlink($uploadDir.$profilePhoto);
        }

        $profilePhoto=uniqid().".".$ext;

        move_uploaded_file(
            $_FILES['profile_photo']['tmp_name'],
            $uploadDir.$profilePhoto
        );

    }

}

$stmt=$pdo->prepare("

UPDATE users

SET

roll_number=?,
name=?,
email=?,
password=?,
phone=?,
department=?,
year=?,
section=?,
club_id=?,
profile_photo=?,
bio=?,
status=?,
role=?

WHERE id=?

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
$status,
$role,
$id

]);

header("Location: ../admin/student_view.php?id=".$id);

exit;