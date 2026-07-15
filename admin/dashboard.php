<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Dashboard Statistics

$totalMembers = $pdo->query("
SELECT COUNT(*)
FROM users
WHERE role='member'
")->fetchColumn();

$pendingMembers = $pdo->query("
SELECT COUNT(*)
FROM users
WHERE status='pending'
")->fetchColumn();

$totalEvents = $pdo->query("
SELECT COUNT(*)
FROM events
")->fetchColumn();

$totalPoints = $pdo->query("
SELECT COALESCE(SUM(points),0)
FROM point_logs
")->fetchColumn();

$recentRegistrations = $pdo->query("
SELECT
users.name,
users.roll_number,
clubs.name AS club_name,
users.created_at

FROM users

LEFT JOIN clubs
ON clubs.id = users.club_id

ORDER BY users.created_at DESC

LIMIT 5

")->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';

?>

<div class="main">

<?php include 'partials/topbar.php'; ?>

<div class="content">

<h3 class="mb-4">

Welcome back,
<?= htmlspecialchars($_SESSION['name']) ?>

</h3>

<div class="row g-4">

<div class="col-lg-3">

<div class="stat-card">

<small>Total Members</small>

<h2><?= $totalMembers ?></h2>

</div>

</div>

<div class="col-lg-3">

<div class="stat-card">

<small>Pending Members</small>

<h2><?= $pendingMembers ?></h2>

</div>

</div>

<div class="col-lg-3">

<div class="stat-card">

<small>Total Events</small>

<h2><?= $totalEvents ?></h2>

</div>

</div>

<div class="col-lg-3">

<div class="stat-card">

<small>Total Points</small>

<h2><?= $totalPoints ?></h2>

</div>

</div>

</div>

<div class="row mt-4 g-4">

<div class="col-lg-8">

<div class="card-admin">

<h5 class="mb-3">

Recent Registrations

</h5>

<table class="table align-middle">

<thead>

<tr>

<th>Name</th>

<th>Roll No.</th>

<th>Club</th>

<th>Date</th>

</tr>

</thead>

<tbody>

<?php

if(count($recentRegistrations)>0){

foreach($recentRegistrations as $row){

?>

<tr>

<td><?= htmlspecialchars($row['name']) ?></td>

<td><?= htmlspecialchars($row['roll_number']) ?></td>

<td><?= htmlspecialchars($row['club_name'] ?? '-') ?></td>

<td><?= date('d M Y',strtotime($row['created_at'])) ?></td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="4" class="text-center text-muted">

No registrations yet.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

<div class="col-lg-4">

<div class="card-admin">

<h5 class="mb-3">

Quick Actions

</h5>

<div class="d-grid gap-2">

<a href="events.php" class="btn btn-primary">

➕ Create Event

</a>

<a href="students.php" class="btn btn-success">

👥 Add Student

</a>

<a href="points.php" class="btn btn-warning">

⭐ Award Points

</a>

<a href="gallery.php" class="btn btn-dark">

🖼 Upload Gallery

</a>

<a href="announcements.php" class="btn btn-info text-white">

📢 New Announcement

</a>

</div>

</div>

<br>

<div class="card-admin">

<h5>

System Status

</h5>

<hr>

<p class="mb-2">

🟢 Database Connected

</p>

<p class="mb-2">

🟢 Admin Logged In

</p>

<p class="mb-0">

🟢 Website Operational

</p>

</div>

</div>

</div>

</div>

</div>

<?php include 'partials/footer.php'; ?>