<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/event_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ============================
   SEARCH & FILTERS
============================ */

$search = trim($_GET['search'] ?? '');
$club = $_GET['club'] ?? '';
$status = $_GET['status'] ?? '';

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$events = getEvents($pdo, $search, $club, $status);

$totalEvents = totalEvents($pdo);
$upcomingEvents = upcomingEvents($pdo);
$completedEvents = completedEvents($pdo);

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

<h2 class="fw-bold">

Events

</h2>

<p class="text-muted">

Manage all VIDURA activities.

</p>

</div>

<a
href="event_add.php"
class="btn btn-primary">

<i class="bi bi-plus-circle"></i>

Create Event

</a>

</div>

<!-- Dashboard Cards -->

<div class="row g-4 mb-4">

<div class="col-md-4">

<div class="stat-card">

<small>Total Events</small>

<h2><?= $totalEvents ?></h2>

</div>

</div>

<div class="col-md-4">

<div class="stat-card">

<small>Upcoming</small>

<h2><?= $upcomingEvents ?></h2>

</div>

</div>

<div class="col-md-4">

<div class="stat-card">

<small>Completed</small>

<h2><?= $completedEvents ?></h2>

</div>

</div>

</div>

<!-- Search -->

<div class="card-admin mb-4">

<form method="GET">

<div class="row g-3">

<div class="col-lg-5">

<input
type="text"
name="search"
class="form-control"
placeholder="Search event title or venue"
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-lg-3">

<select
name="club"
class="form-select">

<option value="">

All Clubs

</option>

<?php foreach($clubs as $c): ?>

<option
value="<?= $c['id'] ?>"
<?= $club==$c['id']?'selected':'' ?>>

<?= htmlspecialchars($c['name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-lg-2">

<select
name="status"
class="form-select">

<option value="">

All Status

</option>

<option value="Upcoming">Upcoming</option>

<option value="Ongoing">Ongoing</option>

<option value="Completed">Completed</option>

<option value="Cancelled">Cancelled</option>

</select>

</div>

<div class="col-lg-2 d-grid">

<button
class="btn btn-primary">

<i class="bi bi-search"></i>

Search

</button>

</div>

</div>

</form>

</div>

<div class="card-admin">

<div class="d-flex justify-content-between align-items-center mb-3">

<h5>

<?= count($events) ?>

Events Found

</h5>

</div>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead>

<tr>

<th>Banner</th>

<th>Title</th>

<th>Club</th>

<th>Date</th>

<th>Venue</th>

<th>Registrations</th>

<th>Status</th>

<th>Actions</th>

</tr>

</thead>

<tbody>


<?php if(count($events)==0): ?>

<tr>

<td colspan="8" class="text-center py-5">

No events found.

</td>

</tr>

<?php endif; ?>

<?php foreach($events as $event): ?>

<tr>

<td width="110">

<?php if(!empty($event['banner'])): ?>

<img
src="../uploads/events/<?= htmlspecialchars($event['banner']) ?>"
style="width:90px;height:60px;object-fit:cover;border-radius:8px;">

<?php else: ?>

<div
style="
width:90px;
height:60px;
background:#ECEFF1;
border-radius:8px;
display:flex;
align-items:center;
justify-content:center;
color:#777;">

<i class="bi bi-image fs-3"></i>

</div>

<?php endif; ?>

</td>

<td>

<strong>

<?= htmlspecialchars($event['title']) ?>

</strong>

<br>

<small class="text-muted">

<?= htmlspecialchars($event['description']) ?>

</small>

</td>

<td>

<span class="badge bg-primary">

<?= htmlspecialchars($event['club_name']) ?>

</span>

</td>

<td>

<?= date("d M Y", strtotime($event['event_date'])) ?>

<br>

<small class="text-muted">

<?= date("h:i A", strtotime($event['start_time'])) ?>

</small>

</td>

<td>

<?= htmlspecialchars($event['venue']) ?>

</td>

<td>

<span class="badge bg-success fs-6">

<?= $event['registrations'] ?>

/

<?= $event['max_participants'] ?>

</span>

</td>

<td>

<?php

switch($event['status']){

case 'Upcoming':

echo '<span class="badge bg-primary">
Upcoming
</span>';

break;

case 'Ongoing':

echo '<span class="badge bg-warning text-dark">
Ongoing
</span>';

break;

case 'Completed':

echo '<span class="badge bg-success">
Completed
</span>';

break;

default:

echo '<span class="badge bg-danger">
Cancelled
</span>';

}

?>

</td>

<td>

<div class="btn-group">

<a
href="event_view.php?id=<?= $event['id'] ?>"
class="btn btn-success btn-sm">

<i class="bi bi-eye-fill"></i>

</a>

<a
href="event_edit.php?id=<?= $event['id'] ?>"
class="btn btn-primary btn-sm">

<i class="bi bi-pencil-fill"></i>

</a>

<a
href="registrations.php?event=<?= $event['id'] ?>"
class="btn btn-warning btn-sm">

<i class="bi bi-people-fill"></i>

</a>

<a
href="../actions/event_delete.php?id=<?= $event['id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this event?');">

<i class="bi bi-trash-fill"></i>

</a>

</div>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<!-- Bottom Statistics -->

<div class="row mt-4">

<div class="col-md-3">

<div class="card-admin text-center">

<h3><?= $totalEvents ?></h3>

<p class="text-muted mb-0">

Total Events

</p>

</div>

</div>

<div class="col-md-3">

<div class="card-admin text-center">

<h3><?= $upcomingEvents ?></h3>

<p class="text-muted mb-0">

Upcoming

</p>

</div>

</div>

<div class="col-md-3">

<div class="card-admin text-center">

<h3><?= $completedEvents ?></h3>

<p class="text-muted mb-0">

Completed

</p>

</div>

</div>

<div class="col-md-3">

<div class="card-admin text-center">

<h3><?= count($events) ?></h3>

<p class="text-muted mb-0">

Filtered Results

</p>

</div>

</div>

</div>

<?php

$totalPages = max(1, ceil(count($events) / $limit));

?>

<nav class="mt-4">

<ul class="pagination justify-content-center">

<?php if($page>1): ?>

<li class="page-item">

<a
class="page-link"
href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&club=<?= urlencode($club) ?>&status=<?= urlencode($status) ?>">

Previous

</a>

</li>

<?php endif; ?>

<?php

$start=max(1,$page-2);
$end=min($totalPages,$page+2);

for($i=$start;$i<=$end;$i++):

?>

<li class="page-item <?= $i==$page?'active':'' ?>">

<a
class="page-link"
href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&club=<?= urlencode($club) ?>&status=<?= urlencode($status) ?>">

<?= $i ?>

</a>

</li>

<?php endfor; ?>

<?php if($page<$totalPages): ?>

<li class="page-item">

<a
class="page-link"
href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&club=<?= urlencode($club) ?>&status=<?= urlencode($status) ?>">

Next

</a>

</li>

<?php endif; ?>

</ul>

</nav>

</div>

</div>

<!-- Quick Event Insights -->

<div class="row mt-5">

<div class="col-lg-6">

<div class="card-admin">

<h5 class="mb-3">

<i class="bi bi-lightning-charge-fill text-warning"></i>

Quick Actions

</h5>

<div class="d-grid gap-2">

<a href="event_add.php" class="btn btn-primary">

<i class="bi bi-plus-circle"></i>

Create New Event

</a>

<a href="registrations.php" class="btn btn-success">

<i class="bi bi-people-fill"></i>

Manage Registrations

</a>

<a href="gallery.php" class="btn btn-info text-white">

<i class="bi bi-images"></i>

Manage Gallery

</a>

</div>

</div>

</div>

<div class="col-lg-6">

<div class="card-admin">

<h5 class="mb-3">

<i class="bi bi-bar-chart-fill text-primary"></i>

Event Summary

</h5>

<table class="table">

<tr>

<td>Total Events</td>

<td class="text-end">

<strong><?= $totalEvents ?></strong>

</td>

</tr>

<tr>

<td>Upcoming</td>

<td class="text-end">

<strong><?= $upcomingEvents ?></strong>

</td>

</tr>

<tr>

<td>Completed</td>

<td class="text-end">

<strong><?= $completedEvents ?></strong>

</td>

</tr>

<tr>

<td>Visible</td>

<td class="text-end">

<strong><?= count($events) ?></strong>

</td>

</tr>

</table>

</div>

</div>

</div>

</div>

</div>

<?php include 'partials/footer.php'; ?>