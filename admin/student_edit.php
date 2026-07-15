<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

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

<h2>Edit Student</h2>

<p class="text-muted">

Update student details.

</p>

</div>

<a href="students.php" class="btn btn-secondary">

Back

</a>

</div>

<div class="card-admin">

<form
action="../actions/student_update.php"
method="POST"
enctype="multipart/form-data">

<input
type="hidden"
name="id"
value="<?= $student['id'] ?>">

<div class="row">

<div class="col-md-6 mb-3">

<label>Name</label>

<input
type="text"
name="name"
class="form-control"
value="<?= htmlspecialchars($student['name']) ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label>Roll Number</label>

<input
type="text"
name="roll_number"
class="form-control"
value="<?= htmlspecialchars($student['roll_number']) ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
value="<?= htmlspecialchars($student['email']) ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label>Password</label>

<input
type="password"
name="password"
class="form-control">

<small class="text-muted">

Leave blank to keep current password.

</small>

</div>

<div class="col-md-6 mb-3">

<label>Phone</label>

<input
type="text"
name="phone"
class="form-control"
value="<?= htmlspecialchars($student['phone']) ?>">

</div>

<div class="col-md-6 mb-3">

<label>Department</label>

<input
type="text"
name="department"
class="form-control"
value="<?= htmlspecialchars($student['department']) ?>">

</div>

<div class="col-md-4 mb-3">

<label>Year</label>

<select
name="year"
class="form-select">

<?php

for($i=1;$i<=4;$i++){

?>

<option
value="<?= $i ?>"
<?= $student['year']==$i?'selected':'' ?>>

<?= $i ?> Year

</option>

<?php } ?>

</select>

</div>

<div class="col-md-4 mb-3">

<label>Section</label>

<input
type="text"
name="section"
class="form-control"
value="<?= htmlspecialchars($student['section']) ?>">

</div>

<div class="col-md-4 mb-3">

<label>Club</label>

<select
name="club_id"
class="form-select">

<option value="">None</option>

<?php foreach($clubs as $club): ?>

<option
value="<?= $club['id'] ?>"
<?= $student['club_id']==$club['id']?'selected':'' ?>>

<?= htmlspecialchars($club['name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Status</label>

<select
name="status"
class="form-select">

<option value="approved" <?= $student['status']=='approved'?'selected':'' ?>>Approved</option>

<option value="pending" <?= $student['status']=='pending'?'selected':'' ?>>Pending</option>

<option value="rejected" <?= $student['status']=='rejected'?'selected':'' ?>>Rejected</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Role</label>

<select
name="role"
class="form-select">

<option value="member" <?= $student['role']=='member'?'selected':'' ?>>Member</option>

<option value="admin" <?= $student['role']=='admin'?'selected':'' ?>>Admin</option>

</select>

</div>

<div class="col-md-12 mb-3">

<label>Bio</label>

<textarea
name="bio"
rows="4"
class="form-control"><?= htmlspecialchars($student['bio']) ?></textarea>

</div>

<div class="col-md-6 mb-4">

<label>Current Photo</label>

<br><br>

<?php if($student['profile_photo']){ ?>

<img
src="../uploads/profiles/<?= $student['profile_photo'] ?>"
width="120"
class="rounded">

<?php }else{ ?>

<p>No photo uploaded.</p>

<?php } ?>

</div>

<div class="col-md-6 mb-4">

<label>Replace Photo</label>

<input
type="file"
name="profile_photo"
class="form-control">

</div>

<div class="col-12">

<button
class="btn btn-primary">

Update Student

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