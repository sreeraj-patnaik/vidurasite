<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/badges.php");
    exit;
}

$userId = intval($_POST['user_id'] ?? 0);
$badgeId = intval($_POST['badge_id'] ?? 0);

if ($userId <= 0 || $badgeId <= 0) {
    $_SESSION['error'] = "Please select both a member and a badge.";
    header("Location: ../admin/badges.php");
    exit;
}

$stmt = $pdo->prepare("
SELECT id
FROM users
WHERE id=? AND role='member'
");
$stmt->execute([$userId]);
if (!$stmt->fetchColumn()) {
    $_SESSION['error'] = "Member not found.";
    header("Location: ../admin/badges.php");
    exit;
}

$stmt = $pdo->prepare("
SELECT id
FROM badges
WHERE id=?
");
$stmt->execute([$badgeId]);
if (!$stmt->fetchColumn()) {
    $_SESSION['error'] = "Badge not found.";
    header("Location: ../admin/badges.php");
    exit;
}

$stmt = $pdo->prepare("
SELECT id
FROM user_badges
WHERE user_id=? AND badge_id=?
");
$stmt->execute([$userId, $badgeId]);
if ($stmt->fetchColumn()) {
    $_SESSION['error'] = "This badge is already assigned to the member.";
    header("Location: ../admin/badges.php?user=" . $userId);
    exit;
}

$stmt = $pdo->prepare("
INSERT INTO user_badges
(user_id, badge_id, awarded_by, awarded_at)
VALUES (?, ?, ?, NOW())
");
$stmt->execute([
    $userId,
    $badgeId,
    $_SESSION['user_id']
]);

$_SESSION['success'] = "Badge assigned successfully.";
header("Location: ../admin/badges.php?user=" . $userId);
exit;
