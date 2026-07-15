<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/student_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* -----------------------------------
   SEARCH & FILTERS
------------------------------------ */

$search = trim($_GET['search'] ?? '');
$club = $_GET['club'] ?? '';
$status = $_GET['status'] ?? '';
$year = $_GET['year'] ?? '';

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search !== '') {

    $where[] = "(LOWER(users.name) LIKE LOWER(?) OR
                 LOWER(users.roll_number) LIKE LOWER(?) OR
                 LOWER(users.email) LIKE LOWER(?))";

    $keyword = "%{$search}%";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
}

if ($club !== '') {
    $where[] = "users.club_id = ?";
    $params[] = $club;
}

if ($status !== '') {
    $where[] = "users.status = ?";
    $params[] = $status;
}

if ($year !== '') {
    $where[] = "users.year = ?";
    $params[] = $year;
}

$whereSQL = "";

if (count($where) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

/* -----------------------------------
   TOTAL RECORDS
------------------------------------ */

$countSQL = "

SELECT COUNT(*)

FROM users

LEFT JOIN clubs
ON clubs.id = users.club_id

$whereSQL

";

$stmt = $pdo->prepare($countSQL);

$stmt->execute($params);

$totalRows = $stmt->fetchColumn();

$totalPages = ceil($totalRows / $limit);

/* -----------------------------------
   FETCH STUDENTS
------------------------------------ */

$sql = "

SELECT

users.*,

clubs.name AS club_name

FROM users

LEFT JOIN clubs
ON clubs.id = users.club_id

$whereSQL

ORDER BY users.created_at DESC

LIMIT $limit

OFFSET $offset

";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$students = $stmt->fetchAll();

/* -----------------------------------
   CLUB LIST
------------------------------------ */

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

Students

</h2>

<p class="text-muted">

Manage all VIDURA members.

</p>

</div>

<a href="student_edit.php"
class="btn btn-primary">

<i class="bi bi-plus-circle"></i>

Add Student

</a>

</div>

<div class="card-admin mb-4">

<form method="GET">

<div class="row g-3">

<div class="col-lg-4">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Name / Roll / Email"
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-lg-2">

<select
name="club"
class="form-select">

<option value="">

All Clubs

</option>

<?php foreach($clubs as $c): ?>

<option
value="<?= $c['id'] ?>"
<?= $club==$c['id'] ? 'selected':'' ?>>

<?= htmlspecialchars($c['name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-lg-2">

<select
name="year"
class="form-select">

<option value="">

All Years

</option>

<option value="1">1st</option>
<option value="2">2nd</option>
<option value="3">3rd</option>
<option value="4">4th</option>

</select>

</div>

<div class="col-lg-2">

<select
name="status"
class="form-select">

<option value="">

All Status

</option>

<option value="approved">

Approved

</option>

<option value="pending">

Pending

</option>

<option value="rejected">

Rejected

</option>

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

<?= $totalRows ?>

Students Found

</h5>
<div>

<a href="student_edit.php"
class="btn btn-success">

<i class="bi bi-person-plus"></i>

New Student

</a>

</div>

</div>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-light">

<tr>

<th width="80">Photo</th>

<th>Roll No.</th>

<th>Name</th>

<th>Club</th>

<th>Year</th>

<th>Points</th>

<th>Status</th>

<th width="220">Actions</th>

</tr>

</thead>

<tbody>

<?php if(count($students)==0): ?>

<tr>

<td colspan="8"
class="text-center py-5 text-muted">

No students found.

</td>

</tr>

<?php endif; ?>

<?php foreach($students as $student): ?>

<tr>

<td>

<?php if(!empty($student['profile_photo'])): ?>

<img
src="../uploads/profiles/<?= htmlspecialchars($student['profile_photo']) ?>"
style="width:52px;height:52px;border-radius:50%;object-fit:cover;">

<?php else: ?>

<div
style="
width:52px;
height:52px;
background:#2563EB;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
font-weight:700;
color:white;
font-size:18px;">

<?= strtoupper(substr($student['name'],0,1)); ?>

</div>

<?php endif; ?>

</td>

<td>

<strong>

<?= htmlspecialchars($student['roll_number']) ?>

</strong>

</td>

<td>

<?= htmlspecialchars($student['name']) ?>

<br>

<small class="text-muted">

<?= htmlspecialchars($student['email']) ?>

</small>

</td>

<td>

<?php if($student['club_name']): ?>

<span class="badge bg-primary">

<?= htmlspecialchars($student['club_name']) ?>

</span>

<?php else: ?>

<span class="badge bg-secondary">

Not Assigned

</span>

<?php endif; ?>

</td>

<td>

<?= $student['year'] ?: '-' ?>

</td>

<td>

<span class="badge bg-success fs-6">

<?= intval($student['points']) ?>

</span>

</td>

<td>

<?php

switch($student['status']){

case 'approved':

echo '

<span class="badge bg-success">

<i class="bi bi-check-circle-fill"></i>

 Approved

</span>';

break;

case 'pending':

echo '

<span class="badge bg-warning text-dark">

<i class="bi bi-clock-fill"></i>

 Pending

</span>';

break;

default:

echo '

<span class="badge bg-danger">

<i class="bi bi-x-circle-fill"></i>

 Rejected

</span>';

}

?>

</td>

<td>

<div class="btn-group">

<a
href="student_view.php?id=<?= $student['id'] ?>"
class="btn btn-outline-success btn-sm"
title="View">

<i class="bi bi-eye-fill"></i>

</a>

<a
href="student_edit.php?id=<?= $student['id'] ?>"
class="btn btn-outline-primary btn-sm"
title="Edit">

<i class="bi bi-pencil-square"></i>

</a>

<a
href="../actions/student_delete.php?id=<?= $student['id'] ?>"
class="btn btn-outline-danger btn-sm"

onclick="return confirm('Delete this student?');"

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

<br>

<div class="d-flex justify-content-between align-items-center">

<div class="text-muted">

Page

<strong>

<?= $page ?>

</strong>

of

<strong>

<?= max(1,$totalPages) ?>

</strong>

</div>

<nav>

<ul class="pagination mb-0">

<?php

$query = $_GET;

unset($query['page']);

?>

<?php if($page>1): ?>

<li class="page-item">

<a
class="page-link"
href="?<?= http_build_query(array_merge($query,['page'=>$page-1])) ?>">

Previous

</a>

</li>

<?php endif; ?>

<?php

$start=max(1,$page-2);
$end=min($totalPages,$page+2);

for($i=$start;$i<=$end;$i++):

?>

<li class="page-item <?= $i==$page ? 'active':'' ?>">

<a
class="page-link"
href="?<?= http_build_query(array_merge($query,['page'=>$i])) ?>">

<?= $i ?>

</a>

</li>

<?php endfor; ?>

<?php if($page<$totalPages): ?>

<li class="page-item">

<a
class="page-link"
href="?<?= http_build_query(array_merge($query,['page'=>$page+1])) ?>">

Next

</a>

</li>

<?php endif; ?>

</ul>

</nav>

</div>

</div>

</div>

<?php include 'partials/footer.php'; ?>