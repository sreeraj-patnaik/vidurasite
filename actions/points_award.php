<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/points.php");
    exit;
}

$userId = intval($_POST['user_id'] ?? 0);
$points = intval($_POST['points'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

if ($userId <= 0 || $points === 0 || $reason === '') {
    $_SESSION['error'] = "Please select a member, enter points, and add a reason.";
    header("Location: ../admin/points.php");
    exit;
}

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("
    SELECT id, points
    FROM users
    WHERE id=? AND role='member'
    FOR UPDATE
    ");
    $stmt->execute([$userId]);
    $member = $stmt->fetch();

    if (!$member) {
        throw new Exception("Member not found.");
    }

    $currentPoints = (int) $member['points'];
    $newPoints = $currentPoints + $points;
    $newLevel = max(1, (int) floor($newPoints / 100) + 1);

    $stmt = $pdo->prepare("
    UPDATE users
    SET points=?, level=?
    WHERE id=?
    ");
    $stmt->execute([$newPoints, $newLevel, $userId]);

    $stmt = $pdo->prepare("
    INSERT INTO point_logs
    (user_id, points, reason, added_by, added_at)
    VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $userId,
        $points,
        $reason,
        $_SESSION['user_id']
    ]);

    $pdo->commit();
    $_SESSION['success'] = "Points updated successfully.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Failed to award points.";
}

header("Location: ../admin/points.php");
exit;
