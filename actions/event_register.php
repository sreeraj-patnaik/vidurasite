<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$eventId = intval($_GET['id'] ?? 0);

if ($eventId <= 0) {
    header("Location: ../member/events.php");
    exit;
}

/* ==========================
   EVENT DETAILS
========================== */

$stmt = $pdo->prepare("
SELECT *,
(
    SELECT COUNT(*)
    FROM registrations
    WHERE event_id = events.id
) AS registered
FROM events
WHERE id=?
LIMIT 1
");

$stmt->execute([$eventId]);

$event = $stmt->fetch();

if (!$event) {
    $_SESSION['error'] = "Event not found.";
    header("Location: ../member/events.php");
    exit;
}

/* ==========================
   REGISTRATION WINDOW
========================== */

$now = time();

if (
    $now < strtotime($event['registration_start']) ||
    $now > strtotime($event['registration_end'])
) {

    $_SESSION['error'] = "Registration is closed.";

    header("Location: ../member/events.php");

    exit;
}

/* ==========================
   SEAT CHECK
========================== */

if ($event['registered'] >= $event['max_participants']) {

    $_SESSION['error'] = "This event is full.";

    header("Location: ../member/events.php");

    exit;
}

/* ==========================
   DUPLICATE CHECK
========================== */

$stmt = $pdo->prepare("
SELECT id
FROM registrations
WHERE user_id=?
AND event_id=?
");

$stmt->execute([$userId, $eventId]);

if ($stmt->fetch()) {

    $_SESSION['error'] = "You are already registered.";

    header("Location: ../member/events.php");

    exit;
}

/* ==========================
   REGISTER
========================== */

$stmt = $pdo->prepare("
INSERT INTO registrations
(
    user_id,
    event_id,
    status,
    attended,
    points_awarded,
    registered_at
)
VALUES
(
    ?,?,
    'Pending',
    FALSE,
    FALSE,
    NOW()
)
");

$stmt->execute([
    $userId,
    $eventId
]);

$_SESSION['success'] = "Registration submitted successfully.";

header("Location: ../member/events.php");

exit;