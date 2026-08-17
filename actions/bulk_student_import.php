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

if (
    !isset($_FILES['students_csv']) ||
    $_FILES['students_csv']['error'] !== UPLOAD_ERR_OK
) {
    $_SESSION['error'] = 'Please upload a valid CSV file.';
    header("Location: ../admin/bulk_tools.php");
    exit;
}

$extension = strtolower(pathinfo($_FILES['students_csv']['name'], PATHINFO_EXTENSION));

if ($extension !== 'csv') {
    $_SESSION['error'] = 'Only CSV files are supported.';
    header("Location: ../admin/bulk_tools.php");
    exit;
}

try {
    $result = bulkImportStudentsFromCsv($pdo, $_FILES['students_csv']['tmp_name']);
    $_SESSION['success'] = sprintf(
        'Imported %d students. Skipped %d rows.',
        $result['created'],
        $result['skipped']
    );

    if (!empty($result['errors'])) {
        $_SESSION['error'] = implode(' ', array_slice($result['errors'], 0, 5));
    }
} catch (Throwable $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../admin/bulk_tools.php");
exit;
