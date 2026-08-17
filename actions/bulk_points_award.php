<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/bulk_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/bulk_tools.php");
    exit;
}

$registrationIds = $_POST['registration_ids'] ?? [];
$pointsInput = trim($_POST['points'] ?? '');
$reason = trim($_POST['reason'] ?? '');

$overridePoints = null;
if ($pointsInput !== '') {
    $overridePoints = (int) $pointsInput;
    if ($overridePoints <= 0) {
        $overridePoints = null;
    }
}

try {
    $result = bulkAwardPoints(
        $pdo,
        $registrationIds,
        $overridePoints,
        $reason,
        (int) $_SESSION['user_id']
    );

    $_SESSION['success'] = sprintf(
        'Awarded points for %d registrations. Skipped %d rows.',
        $result['awarded'],
        $result['skipped']
    );
} catch (Throwable $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../admin/bulk_tools.php#bulk-points");
exit;
