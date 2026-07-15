<?php

require_once 'config/config.php';
require_once 'config/database.php';

$email = "admin@vidura.in";

$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);

if ($check->fetch()) {
    die("<h2>Admin already exists.</h2>");
}

$password = password_hash("admin123", PASSWORD_DEFAULT);

$sql = $pdo->prepare("
INSERT INTO users
(
roll_number,
name,
email,
password,
role,
status
)
VALUES
(
?,
?,
?,
?,
?,
?
)
");

$sql->execute([
    "ADMIN001",
    "VIDURA Administrator",
    $email,
    $password,
    "admin",
    "approved"
]);

echo "<h2>✅ Admin account created successfully.</h2>";
echo "<br>";
echo "Email : admin@vidura.in";
echo "<br>";
echo "Password : admin123";