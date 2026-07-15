<?php

require_once '../config/config.php';
require_once '../config/database.php';

$email = trim($_POST['email']);
$password = $_POST['password'];

$stmt = $pdo->prepare("
SELECT *
FROM users
WHERE email=?
LIMIT 1
");

$stmt->execute([$email]);

$user = $stmt->fetch();

if(!$user){

die("Invalid Email");

}

if(!password_verify($password,$user['password'])){

die("Wrong Password");

}

$_SESSION['user_id']=$user['id'];
$_SESSION['name']=$user['name'];
$_SESSION['role']=$user['role'];

if($user['role']=="admin"){

header("Location: ../admin/dashboard.php");

}else{

header("Location: ../member/dashboard.php");

}

exit;