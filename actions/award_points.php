<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("
SELECT
r.*,
e.points
FROM registrations r
JOIN events e
ON e.id=r.event_id
WHERE r.id=?
");

$stmt->execute([$id]);

$reg = $stmt->fetch();

if (!$reg) {
    die("Registration not found.");
}

if ($reg['points_awarded']) {
    header("Location: ../admin/registrations.php");
    exit;
}

if (!$reg['attended']) {
    die("Attendance must be marked first.");
}

$pdo->beginTransaction();

try {

    $stmt = $pdo->prepare("
    SELECT points
    FROM users
    WHERE id=?
    FOR UPDATE
    ");

    $stmt->execute([$reg['user_id']]);
    $currentPoints = (int) $stmt->fetchColumn();
    $newPoints = $currentPoints + (int) $reg['points'];
    $newLevel = (int) floor($newPoints / 100) + 1;

    $stmt = $pdo->prepare("
    UPDATE users
    SET
    points = ?,
    level = ?
    WHERE id=?
    ");

    $stmt->execute([
        $newPoints,
        $newLevel,
        $reg['user_id']
    ]);

    $stmt = $pdo->prepare("
    INSERT INTO point_logs
    (user_id, points, reason, added_at)
    VALUES (?,?,?,NOW())
    ");

    $stmt->execute([
        $reg['user_id'],
        $reg['points'],
        'Participation - Event'
    ]);

    $stmt = $pdo->prepare("
    UPDATE registrations
    SET points_awarded=true
    WHERE id=?
    ");

    $stmt->execute([$id]);

    $pdo->commit();

} catch(Exception $e){

    $pdo->rollBack();
    die($e->getMessage());

}

header("Location: ../admin/registrations.php");
exit;
