<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
$event = $_GET['event'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "

SELECT

registrations.*,

users.name,

users.roll_number,

events.title

FROM registrations

JOIN users
ON users.id = registrations.user_id

JOIN events
ON events.id = registrations.event_id

WHERE 1=1

";

$params=[];

if($search!=''){

$sql.="

AND (

LOWER(users.name) LIKE LOWER(?)

OR

LOWER(users.roll_number) LIKE LOWER(?)

)

";

$key="%".$search."%";

$params[]=$key;
$params[]=$key;

}

if($event!=''){

$sql.="

AND registrations.event_id=?

";

$params[]=$event;

}

if($status!=''){

$sql.="

AND registrations.status=?

";

$params[]=$status;

}

$sql.="

ORDER BY registrations.registered_at DESC

";

$stmt=$pdo->prepare($sql);

$stmt->execute($params);

$registrations=$stmt->fetchAll();

$events=$pdo->query("

SELECT id,title

FROM events

ORDER BY event_date DESC

")->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';

?>

<div class="main">

<?php include 'partials/topbar.php'; ?>

<div class="content">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2>

Registrations

</h2>

<p class="text-muted">

Manage event registrations.

</p>

</div>

</div>

<div class="card-admin mb-4">

<form method="GET">

<div class="row g-3">

<div class="col-lg-4">

<input

type="text"

name="search"

class="form-control"

placeholder="Search Student"

value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-lg-4">

<select

name="event"

class="form-select">

<option value="">

All Events

</option>

<?php foreach($events as $e): ?>

<option

value="<?= $e['id'] ?>"

<?= $event==$e['id']?'selected':'' ?>>

<?= htmlspecialchars($e['title']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-lg-2">

<select

name="status"

class="form-select">

<option value="">All</option>

<option value="Pending">Pending</option>

<option value="Approved">Approved</option>

<option value="Rejected">Rejected</option>

</select>

</div>

<div class="col-lg-2 d-grid">

<button class="btn btn-primary">

Search

</button>

</div>

</div>

</form>

</div>

<div class="card-admin">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead>

<tr>

<th>Student</th>

<th>Event</th>

<th>Status</th>

<th>Attendance</th>

<th>Points</th>

<th>Actions</th>

</tr>

</thead>

<tbody>
<?php if(count($registrations)==0): ?>

<tr>

<td colspan="6" class="text-center py-5">

No registrations found.

</td>

</tr>

<?php endif; ?>

<?php foreach($registrations as $registration): ?>

<tr>

<td>

<strong>

<?= htmlspecialchars($registration['name']) ?>

</strong>

<br>

<small class="text-muted">

<?= htmlspecialchars($registration['roll_number']) ?>

</small>

</td>

<td>

<?= htmlspecialchars($registration['title']) ?>

</td>

<td>

<?php

switch($registration['status']){

case 'Approved':

echo '<span class="badge bg-success">

Approved

</span>';

break;

case 'Rejected':

echo '<span class="badge bg-danger">

Rejected

</span>';

break;

default:

echo '<span class="badge bg-warning text-dark">

Pending

</span>';

}

?>

</td>

<td>

<?php

if($registration['attended']){

echo '

<span class="badge bg-success">

Present

</span>';

}else{

echo '

<span class="badge bg-secondary">

Not Marked

</span>';

}

?>

</td>

<td>

<?php

if($registration['points_awarded']){

echo '

<span class="badge bg-success">

Awarded

</span>';

}else{

echo '

<span class="badge bg-warning text-dark">

Pending

</span>';

}

?>

</td>

<td>

<div class="btn-group">

<a

href="../actions/registration_approve.php?id=<?= $registration['id'] ?>"

class="btn btn-success btn-sm"

title="Approve">

<i class="bi bi-check-lg"></i>

</a>

<a

href="../actions/registration_reject.php?id=<?= $registration['id'] ?>"

class="btn btn-warning btn-sm"

title="Reject">

<i class="bi bi-x-lg"></i>

</a>

<a

href="../actions/attendance_toggle.php?id=<?= $registration['id'] ?>"

class="btn btn-primary btn-sm"

title="Attendance">

<i class="bi bi-person-check-fill"></i>

</a>

<a

href="../actions/award_points.php?id=<?= $registration['id'] ?>"

class="btn btn-info btn-sm text-white"

title="Award Points">

<i class="bi bi-star-fill"></i>

</a>

<a

href="../actions/registration_delete.php?id=<?= $registration['id'] ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Delete this registration?')"

title="Delete">

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

<!-- Registration Statistics -->

<?php

$totalRegistrations = count($registrations);

$approved = 0;
$pending = 0;
$rejected = 0;
$attended = 0;

foreach($registrations as $r){

    if($r['status']=="Approved") $approved++;

    if($r['status']=="Pending") $pending++;

    if($r['status']=="Rejected") $rejected++;

    if($r['attended']) $attended++;

}

?>

<div class="row mt-4">

<div class="col-md-3">

<div class="card-admin text-center">

<h3><?= $totalRegistrations ?></h3>

<p class="text-muted mb-0">

Total Registrations

</p>

</div>

</div>

<div class="col-md-3">

<div class="card-admin text-center">

<h3 class="text-success">

<?= $approved ?>

</h3>

<p class="text-muted mb-0">

Approved

</p>

</div>

</div>

<div class="col-md-3">

<div class="card-admin text-center">

<h3 class="text-warning">

<?= $pending ?>

</h3>

<p class="text-muted mb-0">

Pending

</p>

</div>

</div>

<div class="col-md-3">

<div class="card-admin text-center">

<h3 class="text-primary">

<?= $attended ?>

</h3>

<p class="text-muted mb-0">

Attendance Marked

</p>

</div>

</div>

</div>

<?php

$page = max(1, intval($_GET['page'] ?? 1));

$totalPages = max(1, ceil($totalRegistrations / 20));

?>

<nav class="mt-4">

<ul class="pagination justify-content-center">

<?php if($page>1): ?>

<li class="page-item">

<a
class="page-link"

href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&event=<?= urlencode($event) ?>&status=<?= urlencode($status) ?>">

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

href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&event=<?= urlencode($event) ?>&status=<?= urlencode($status) ?>">

<?= $i ?>

</a>

</li>

<?php endfor; ?>

<?php if($page<$totalPages): ?>

<li class="page-item">

<a
class="page-link"

href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&event=<?= urlencode($event) ?>&status=<?= urlencode($status) ?>">

Next

</a>

</li>

<?php endif; ?>

</ul>

</nav>

</div>

</div>

<!-- Quick Actions -->

<div class="row mt-5">

<div class="col-lg-6">

<div class="card-admin">

<h5 class="mb-3">

<i class="bi bi-lightning-charge-fill text-warning"></i>

Quick Actions

</h5>

<div class="d-grid gap-2">

<a href="events.php" class="btn btn-primary">

<i class="bi bi-calendar-event"></i>

Manage Events

</a>

<a href="points.php" class="btn btn-success">

<i class="bi bi-star-fill"></i>

Manage Points

</a>

<a href="leaderboard.php" class="btn btn-warning">

<i class="bi bi-trophy-fill"></i>

Leaderboard

</a>

</div>

</div>

</div>

<div class="col-lg-6">

<div class="card-admin">

<h5 class="mb-3">

Registration Summary

</h5>

<table class="table">

<tr>

<td>Total Registrations</td>

<td class="text-end">

<strong><?= $totalRegistrations ?></strong>

</td>

</tr>

<tr>

<td>Approved</td>

<td class="text-end">

<strong><?= $approved ?></strong>

</td>

</tr>

<tr>

<td>Pending</td>

<td class="text-end">

<strong><?= $pending ?></strong>

</td>

</tr>

<tr>

<td>Rejected</td>

<td class="text-end">

<strong><?= $rejected ?></strong>

</td>

</tr>

<tr>

<td>Attendance Marked</td>

<td class="text-end">

<strong><?= $attended ?></strong>

</td>

</tr>

</table>

</div>

</div>

</div>

</div>

</div>

<?php include 'partials/footer.php'; ?>