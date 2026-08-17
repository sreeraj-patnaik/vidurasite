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

$status = trim($_POST['status'] ?? '');
$studentIds = $_POST['student_ids'] ?? [];

try {
    $count = bulkUpdateStudentStatus($pdo, $studentIds, $status);

    if ($count > 0) {
        $_SESSION['success'] = sprintf('%d student records updated.', $count);
    } else {
        $_SESSION['error'] = 'No students were selected or updated.';
    }
} catch (Throwable $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../admin/bulk_tools.php#student-approval");
exit;
