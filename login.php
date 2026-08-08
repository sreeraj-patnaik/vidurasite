<?php

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/settings_functions.php';

if(isset($_SESSION['user_id'])){
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: member/dashboard.php");
    }

    exit;
}

$settings = getSiteSettings($pdo);

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="py-5">

<div class="container">

<div class="row justify-content-center">

<div class="col-lg-5">

<div class="card shadow-lg border-0 rounded-4">

<div class="card-body p-5">

<h2 class="text-center mb-4">

VIDURA Login

</h2>

<form action="actions/login.php" method="POST">

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-4">

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button
class="btn btn-primary w-100">

Login

</button>

</form>

</div>

</div>

</div>

</div>

</div>

</section>

<?php include 'includes/footer.php'; ?>
