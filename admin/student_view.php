<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: students.php");
    exit;
}

/* =====================================
   STUDENT
===================================== */

$stmt = $pdo->prepare("
SELECT
    users.*,
    clubs.name AS club_name,
    clubs.theme_color

FROM users

LEFT JOIN clubs
ON clubs.id = users.club_id

WHERE users.id = ?

LIMIT 1
");

$stmt->execute([$id]);

$student = $stmt->fetch();

if (!$student) {
    die("Student not found.");
}

/* =====================================
   BADGES
===================================== */

$stmt = $pdo->prepare("
SELECT

badges.*

FROM user_badges

JOIN badges

ON badges.id=user_badges.badge_id

WHERE user_badges.user_id=?

ORDER BY awarded_at DESC
");

$stmt->execute([$id]);

$badges = $stmt->fetchAll();

/* =====================================
   POINT HISTORY
===================================== */

$stmt = $pdo->prepare("
SELECT *

FROM point_logs

WHERE user_id=?

ORDER BY added_at DESC

LIMIT 10
");

$stmt->execute([$id]);

$pointHistory = $stmt->fetchAll();

/* =====================================
   EVENTS
===================================== */

$stmt = $pdo->prepare("
SELECT

events.title,

events.event_date,

registrations.attendance

FROM registrations

JOIN events

ON events.id=registrations.event_id

WHERE registrations.user_id=?

ORDER BY events.event_date DESC
");

$stmt->execute([$id]);

$events = $stmt->fetchAll();

/* =====================================
   ATTENDANCE
===================================== */

$stmt = $pdo->prepare("
SELECT

COUNT(*) FILTER (WHERE attendance=true) AS present,

COUNT(*) FILTER (WHERE attendance=false) AS absent

FROM registrations

WHERE user_id=?
");

$stmt->execute([$id]);

$attendance = $stmt->fetch();

$present = intval($attendance['present']);
$absent = intval($attendance['absent']);

$total = $present + $absent;

$percentage = $total > 0
    ? round(($present / $total) * 100)
    : 0;

include 'partials/header.php';
include 'partials/sidebar.php';

?>

<div class="main">

<?php include 'partials/topbar.php'; ?>

<div class="content">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="fw-bold">

Student Profile

</h2>

<p class="text-muted">

Complete member information.

</p>

</div>

<div>

<a href="students.php"
class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

<a href="student_edit.php?id=<?= $student['id'] ?>"
class="btn btn-primary">

<i class="bi bi-pencil"></i>

Edit

</a>

</div>

</div>

<div class="row">

<div class="col-lg-4">

<div class="card-admin text-center">

<?php if(!empty($student['profile_photo'])): ?>

<img

src="../uploads/profiles/<?= htmlspecialchars($student['profile_photo']) ?>"

style="width:170px;
height:170px;
border-radius:50%;
object-fit:cover;">

<?php else: ?>

<div

style="
width:170px;
height:170px;
margin:auto;
border-radius:50%;
background:#2563EB;
display:flex;
align-items:center;
justify-content:center;
font-size:70px;
font-weight:700;
color:white;">

<?= strtoupper(substr($student['name'],0,1)); ?>

</div>

<?php endif; ?>

<br><br>

<h3>

<?= htmlspecialchars($student['name']) ?>

</h3>

<p class="text-muted">

<?= htmlspecialchars($student['roll_number']) ?>

</p>

<span class="badge bg-primary fs-6">

<?= htmlspecialchars($student['club_name'] ?? 'No Club') ?>

</span>

<br><br>

<h2 class="text-success">

<?= intval($student['points']) ?>

</h2>

<small>Total Points</small>

<br><br>
<?php

$status = strtolower($student['status']);

switch($status){

    case 'approved':

        echo '<span class="badge bg-success fs-6">
                <i class="bi bi-check-circle-fill"></i>
                Approved Member
              </span>';

        break;

    case 'pending':

        echo '<span class="badge bg-warning text-dark fs-6">
                <i class="bi bi-clock-fill"></i>
                Pending Approval
              </span>';

        break;

    default:

        echo '<span class="badge bg-danger fs-6">
                <i class="bi bi-x-circle-fill"></i>
                Rejected
              </span>';

}

?>

<hr>

<div class="row text-center">

<div class="col-6">

<h5>

<?= intval($student['level']) ?>

</h5>

<small class="text-muted">

Current Level

</small>

</div>

<div class="col-6">

<h5>

<?= count($badges) ?>

</h5>

<small class="text-muted">

Badges

</small>

</div>

</div>

</div>

</div>

<!-- RIGHT COLUMN -->

<div class="col-lg-8">

<div class="card-admin mb-4">

<h4>

Personal Information

</h4>

<hr>

<div class="row">

<div class="col-md-6 mb-3">

<strong>Email</strong>

<br>

<?= htmlspecialchars($student['email']) ?>

</div>

<div class="col-md-6 mb-3">

<strong>Phone</strong>

<br>

<?= htmlspecialchars($student['phone'] ?: '-') ?>

</div>

<div class="col-md-6 mb-3">

<strong>Department</strong>

<br>

<?= htmlspecialchars($student['department'] ?: '-') ?>

</div>

<div class="col-md-6 mb-3">

<strong>Year</strong>

<br>

<?= htmlspecialchars($student['year'] ?: '-') ?>

</div>

<div class="col-md-6 mb-3">

<strong>Section</strong>

<br>

<?= htmlspecialchars($student['section'] ?: '-') ?>

</div>

<div class="col-md-6 mb-3">

<strong>Joined</strong>

<br>

<?= date("d M Y",strtotime($student['joined_at'])) ?>

</div>

</div>

</div>

<div class="card-admin mb-4">

<h4>

Achievements & Badges

</h4>

<hr>

<?php if(count($badges)==0): ?>

<p class="text-muted">

No badges awarded yet.

</p>

<?php else: ?>

<div class="row">

<?php foreach($badges as $badge): ?>

<div class="col-md-6 mb-3">

<div
class="border rounded-4 p-3 h-100">

<h5>

🏅

<?= htmlspecialchars($badge['title']) ?>

</h5>

<p class="text-muted mb-0">

<?= htmlspecialchars($badge['description']) ?>

</p>

</div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>
<div class="card-admin mb-4">

<h4>

Recent Point History

</h4>

<hr>

<?php if(count($pointHistory)==0): ?>

<p class="text-muted">

No point history available.

</p>

<?php else: ?>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead>

<tr>

<th>Points</th>

<th>Reason</th>

<th>Date</th>

</tr>

</thead>

<tbody>

<?php foreach($pointHistory as $log): ?>

<tr>

<td>

<?php if($log['points']>=0): ?>

<span class="badge bg-success fs-6">

+<?= $log['points'] ?>

</span>

<?php else: ?>

<span class="badge bg-danger fs-6">

<?= $log['points'] ?>

</span>

<?php endif; ?>

</td>

<td>

<?= htmlspecialchars($log['reason']) ?>

</td>

<td>

<?= date("d M Y",strtotime($log['added_at'])) ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>

</div>

<div class="card-admin mb-4">

<h4>

Registered Events

</h4>

<hr>

<?php if(count($events)==0): ?>

<p class="text-muted">

No registered events.

</p>

<?php else: ?>

<div class="table-responsive">

<table class="table table-striped align-middle">

<thead>

<tr>

<th>Event</th>

<th>Date</th>

<th>Attendance</th>

</tr>

</thead>

<tbody>

<?php foreach($events as $event): ?>

<tr>

<td>

<?= htmlspecialchars($event['title']) ?>

</td>

<td>

<?= date("d M Y",strtotime($event['event_date'])) ?>

</td>

<td>

<?php

if($event['attendance']){

echo '<span class="badge bg-success">

Present

</span>';

}else{

echo '<span class="badge bg-danger">

Absent

</span>';

}

?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>

</div>

<div class="card-admin mb-4">

<h4>

Attendance Statistics

</h4>

<hr>

<div class="row text-center">

<div class="col-md-4">

<h2 class="text-success">

<?= $present ?>

</h2>

<small>

Present

</small>

</div>

<div class="col-md-4">

<h2 class="text-danger">

<?= $absent ?>

</h2>

<small>

Absent

</small>

</div>

<div class="col-md-4">

<h2 class="text-primary">

<?= $percentage ?>%

</h2>

<small>

Attendance

</small>

</div>

</div>

</div>

<div class="card-admin">

<h4>

VIDURA Digital Identity

</h4>

<hr>

<div class="row align-items-center">

<div class="col-lg-4 text-center">

<div
style="
width:180px;
height:180px;
margin:auto;
background:#f5f5f5;
display:flex;
align-items:center;
justify-content:center;
border:2px dashed #bbb;
border-radius:12px;">

QR CODE

</div>

<p class="text-muted mt-3">

QR-based Digital ID

</p>

</div>

<div class="col-lg-8">

<p>

Every VIDURA member will receive a secure digital identity
containing their profile, achievements, participation,
badges and points.

</p>

<div class="d-grid gap-2">

<a
href="student_edit.php?id=<?= $student['id'] ?>"
class="btn btn-primary">

<i class="bi bi-pencil-square"></i>

Edit Student

</a>

<a
href="points.php?user=<?= $student['id'] ?>"
class="btn btn-success">

<i class="bi bi-star-fill"></i>

Award Points

</a>

<a
href="badges.php?user=<?= $student['id'] ?>"
class="btn btn-warning">

<i class="bi bi-award-fill"></i>

Assign Badge

</a>

<a
href="../actions/student_delete.php?id=<?= $student['id'] ?>"

class="btn btn-danger"

onclick="return confirm('Delete this student permanently?');">

<i class="bi bi-trash-fill"></i>

Delete Student

</a>

</div>

</div>

</div>

</div>

</div>

</div>

<?php include 'partials/footer.php'; ?>