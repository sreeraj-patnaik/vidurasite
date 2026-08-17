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

try {
    $count = bulkMarkAttendance($pdo, $registrationIds);

    if ($count > 0) {
        $_SESSION['success'] = sprintf('%d registrations marked as attended.', $count);
    } else {
        $_SESSION['error'] = 'No registrations were selected.';
    }
} catch (Throwable $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../admin/bulk_tools.php#bulk-attendance");
exit;
