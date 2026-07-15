<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../admin/events.php");
    exit;
}

/* ===========================
   READ FORM
=========================== */

$title = trim($_POST['title']);
$description = trim($_POST['description']);
$club_id = intval($_POST['club_id']);
$venue = trim($_POST['venue']);

$event_date = $_POST['event_date'];

$start_time = !empty($_POST['start_time'])
    ? $_POST['start_time']
    : null;

$end_time = !empty($_POST['end_time'])
    ? $_POST['end_time']
    : null;

$registration_start = $_POST['registration_start'];
$registration_end = $_POST['registration_end'];

$max_participants = intval($_POST['max_participants']);

$year_allowed = $_POST['year_allowed'];

$department_allowed = trim($_POST['department_allowed']);

$points = intval($_POST['points']);

$status = $_POST['status'];

$fcfs = isset($_POST['first_come_first_serve']) ? true : false;

$created_by = $_SESSION['user_id'];

$banner = null;

/* ===========================
   VALIDATION
=========================== */

if ($title == '') {
    die("Event title is required.");
}

if ($club_id <= 0) {
    die("Select a club.");
}

if (strtotime($registration_end) < strtotime($registration_start)) {
    die("Registration End must be after Registration Start.");
}

/* ===========================
   BANNER UPLOAD
=========================== */

if (
    isset($_FILES['banner']) &&
    $_FILES['banner']['error'] == 0
) {

    $uploadDir = "../uploads/events/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = strtolower(
        pathinfo(
            $_FILES['banner']['name'],
            PATHINFO_EXTENSION
        )
    );

    $allowed = ['jpg','jpeg','png','webp'];

    if (!in_array($extension, $allowed)) {
        die("Invalid banner format.");
    }

    $banner = uniqid("event_") . "." . $extension;

    move_uploaded_file(
        $_FILES['banner']['tmp_name'],
        $uploadDir . $banner
    );

}
/* ===========================
   INSERT EVENT
=========================== */

$stmt = $pdo->prepare("

INSERT INTO events
(

title,

description,

club_id,

venue,

event_date,

registration_start,

registration_end,

max_participants,

year_allowed,

department_allowed,

first_come_first_serve,

points,

banner,

status,

created_by,

created_at,

start_time,

end_time

)

VALUES
(

?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?

)

");

$stmt->execute([

$title,

$description,

$club_id,

$venue,

$event_date,

$registration_start,

$registration_end,

$max_participants,

$year_allowed,

$department_allowed,

$fcfs,

$points,

$banner,

$status,

$created_by,

date("Y-m-d H:i:s"),

$start_time,

$end_time

]);

$_SESSION['success'] = "Event created successfully.";

header("Location: ../admin/events.php");

exit;