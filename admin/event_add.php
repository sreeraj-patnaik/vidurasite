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

<h2>Create Event</h2>

<p class="text-muted">

Create a new VIDURA activity.

</p>

</div>

<a
href="events.php"
class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

</div>

<div class="card-admin">

<form
action="../actions/event_save.php"
method="POST"
enctype="multipart/form-data">

<div class="row">

<div class="col-md-8 mb-3">

<label class="form-label">

Event Title

</label>

<input
type="text"
name="title"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Club

</label>

<select
name="club_id"
class="form-select"
required>

<option value="">Select Club</option>

<?php foreach($clubs as $club): ?>

<option value="<?= $club['id'] ?>">

<?= htmlspecialchars($club['name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-12 mb-3">

<label class="form-label">

Description

</label>

<textarea
name="description"
class="form-control"
rows="5"></textarea>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Venue

</label>

<input
type="text"
name="venue"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Banner Image

</label>

<input
type="file"
name="banner"
class="form-control"
accept="image/*">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Event Date

</label>

<input
type="date"
name="event_date"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Start Time

</label>

<input
type="time"
name="start_time"
class="form-control">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

End Time

</label>

<input
type="time"
name="end_time"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Registration Start

</label>

<input
type="datetime-local"
name="registration_start"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Registration End

</label>

<input
type="datetime-local"
name="registration_end"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Maximum Participants

</label>

<input
type="number"
name="max_participants"
class="form-control"
value="100"
min="1">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Allowed Year

</label>

<select
name="year_allowed"
class="form-select">

<option value="All">All Years</option>

<option value="1">1st Year</option>

<option value="2">2nd Year</option>

<option value="3">3rd Year</option>

<option value="4">4th Year</option>

</select>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Department

</label>

<input
type="text"
name="department_allowed"
class="form-control"
value="Computer Science & Systems Engineering">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Participation Points

</label>

<input
type="number"
name="points"
class="form-control"
value="10"
min="0">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Status

</label>

<select
name="status"
class="form-select">

<option value="Upcoming">

Upcoming

</option>

<option value="Ongoing">

Ongoing

</option>

<option value="Completed">

Completed

</option>

<option value="Cancelled">

Cancelled

</option>

</select>

</div>

<div class="col-md-6 mb-4">

<div class="form-check form-switch mt-4">

<input
class="form-check-input"
type="checkbox"
name="first_come_first_serve"
value="1"
checked>

<label class="form-check-label">

First Come First Serve Registration

</label>

</div>

</div>

<div class="col-md-6 mb-4">

<div class="alert alert-info">

<i class="bi bi-info-circle-fill"></i>

Participants will automatically receive the configured participation points after attendance is marked.

</div>

</div>

<div class="col-12">

<hr class="my-4">

<div class="d-flex justify-content-end gap-2">

<a
href="events.php"
class="btn btn-secondary">

<i class="bi bi-x-circle"></i>

Cancel

</a>

<button
type="reset"
class="btn btn-warning">

<i class="bi bi-arrow-counterclockwise"></i>

Reset

</button>

<button
type="submit"
class="btn btn-primary">

<i class="bi bi-check-circle-fill"></i>

Create Event

</button>

</div>

</div>

</div>

</form>

</div>

</div>

</div>

<?php include 'partials/footer.php'; ?>