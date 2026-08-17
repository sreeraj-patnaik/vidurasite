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

$eventId = (int) ($_POST['event_id'] ?? 0);
$studentIds = $_POST['student_ids'] ?? [];

try {
    $result = bulkRegisterStudentsToEvent($pdo, $eventId, $studentIds);
    $_SESSION['success'] = sprintf(
        'Registered %d students. Skipped %d rows.',
        $result['registered'],
        $result['skipped']
    );
} catch (Throwable $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../admin/bulk_tools.php#event-registration");
exit;
