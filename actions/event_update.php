<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/events.php");
    exit;
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../admin/events.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    $_SESSION['error'] = "Event not found.";
    header("Location: ../admin/events.php");
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$club_id = intval($_POST['club_id'] ?? 0);
$venue = trim($_POST['venue'] ?? '');
$event_date = $_POST['event_date'] ?? null;
$start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
$end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
$registration_start = $_POST['registration_start'] ?? null;
$registration_end = $_POST['registration_end'] ?? null;
$max_participants = intval($_POST['max_participants'] ?? 100);
$year_allowed = trim($_POST['year_allowed'] ?? '');
$department_allowed = trim($_POST['department_allowed'] ?? '');
$points = intval($_POST['points'] ?? 0);
$status = trim($_POST['status'] ?? 'Upcoming');
$fcfs = isset($_POST['first_come_first_serve']) ? true : false;
$banner = $event['banner'];

if ($title === '') {
    $_SESSION['error'] = "Event title is required.";
    header("Location: ../admin/events.php");
    exit;
}

if ($club_id <= 0) {
    $_SESSION['error'] = "Select a club.";
    header("Location: ../admin/events.php");
    exit;
}

if (strtotime($registration_end) < strtotime($registration_start)) {
    $_SESSION['error'] = "Registration end must be after registration start.";
    header("Location: ../admin/events.php");
    exit;
}

if (
    isset($_FILES['banner']) &&
    $_FILES['banner']['error'] === UPLOAD_ERR_OK
) {
    $uploadDir = "../uploads/events/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowed, true)) {
        $_SESSION['error'] = "Invalid banner format.";
        header("Location: ../admin/events.php");
        exit;
    }

    $newBanner = uniqid("event_") . "." . $extension;

    if (move_uploaded_file($_FILES['banner']['tmp_name'], $uploadDir . $newBanner)) {
        if (!empty($banner) && is_file($uploadDir . $banner)) {
            unlink($uploadDir . $banner);
        }
        $banner = $newBanner;
    }
}

$stmt = $pdo->prepare("
UPDATE events
SET
title=?,
description=?,
club_id=?,
venue=?,
event_date=?,
registration_start=?,
registration_end=?,
max_participants=?,
year_allowed=?,
department_allowed=?,
first_come_first_serve=?,
points=?,
banner=?,
status=?,
start_time=?,
end_time=?
WHERE id=?
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
    $start_time,
    $end_time,
    $id
]);

$_SESSION['success'] = "Event updated successfully.";
header("Location: ../admin/events.php");
exit;
