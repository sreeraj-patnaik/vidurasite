<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$clubs = $pdo->query("
SELECT *
FROM clubs
ORDER BY name
")->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';

?>

<div class="main">

<?php include 'partials/topbar.php'; ?>

<div class="content">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2>Add Student</h2>

<p class="text-muted">

Create a new VIDURA member.

</p>

</div>

<a href="students.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

</div>

<div class="card-admin">

<form
action="../actions/student_save.php"
method="POST"
enctype="multipart/form-data">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">Name</label>

<input
type="text"
name="name"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">Roll Number</label>

<input
type="text"
name="roll_number"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">Phone</label>

<input
type="text"
name="phone"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">Department</label>

<input
type="text"
name="department"
class="form-control"
value="Computer Science & Systems Engineering">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">Year</label>

<select
name="year"
class="form-select">

<option value="1">1st Year</option>
<option value="2">2nd Year</option>
<option value="3">3rd Year</option>
<option value="4">4th Year</option>

</select>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">Section</label>

<input
type="text"
name="section"
class="form-control">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">Club</label>

<select
name="club_id"
class="form-select">

<option value="">Select Club</option>

<?php foreach($clubs as $club): ?>

<option value="<?= $club['id'] ?>">

<?= htmlspecialchars($club['name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">Status</label>

<select
name="status"
class="form-select">

<option value="approved">Approved</option>
<option value="pending">Pending</option>
<option value="rejected">Rejected</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">Role</label>

<select
name="role"
class="form-select">

<option value="member">Member</option>
<option value="admin">Admin</option>

</select>

</div>

<div class="col-md-12 mb-3">

<label class="form-label">Bio</label>

<textarea
name="bio"
class="form-control"
rows="4"></textarea>

</div>

<div class="col-md-12 mb-4">

<label class="form-label">Profile Photo</label>

<input
type="file"
name="profile_photo"
class="form-control"
accept="image/*">

</div>

<div class="col-12">

<button
class="btn btn-primary">

<i class="bi bi-check-circle"></i>

Save Student

</button>

<a
href="students.php"
class="btn btn-secondary">

Cancel

</a>

</div>

</div>

</form>

</div>

</div>

</div>

<?php include 'partials/footer.php'; ?>