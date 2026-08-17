<?php

function bulkNormalizeIds(array $values): array
{
    $ids = [];

    foreach ($values as $value) {
        $id = (int) $value;

        if ($id > 0) {
            $ids[$id] = $id;
        }
    }

    return array_values($ids);
}

function bulkBuildPlaceholders(array $ids): string
{
    return implode(',', array_fill(0, count($ids), '?'));
}

function bulkImportStudentsFromCsv(PDO $pdo, string $csvPath): array
{
    $result = [
        'processed' => 0,
        'created' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    $handle = fopen($csvPath, 'r');

    if (!$handle) {
        throw new RuntimeException('Unable to read the uploaded CSV file.');
    }

    $headers = fgetcsv($handle);

    if ($headers === false) {
        fclose($handle);
        throw new RuntimeException('The CSV file is empty.');
    }

    $headers = array_map(static function ($value) {
        return strtolower(trim((string) $value));
    }, $headers);

    $headerMap = array_flip($headers);
    $required = ['roll_number', 'name', 'email', 'password'];

    foreach ($required as $field) {
        if (!isset($headerMap[$field])) {
            fclose($handle);
            throw new RuntimeException('Missing required CSV column: ' . $field);
        }
    }

    while (($row = fgetcsv($handle)) !== false) {
        $lineNumber = $result['processed'] + 2;

        $allBlank = true;
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                $allBlank = false;
                break;
            }
        }

        if ($allBlank) {
            continue;
        }

        $result['processed']++;

        $data = [];
        foreach ($headers as $index => $name) {
            $data[$name] = trim((string) ($row[$index] ?? ''));
        }

        $roll = $data['roll_number'] ?? '';
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if ($roll === '' || $name === '' || $email === '' || $password === '') {
            $result['skipped']++;
            $result['errors'][] = "Row {$lineNumber}: required fields are missing.";
            continue;
        }

        $duplicateStmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = ? OR roll_number = ?
            LIMIT 1
        ");
        $duplicateStmt->execute([$email, $roll]);

        if ($duplicateStmt->fetchColumn()) {
            $result['skipped']++;
            $result['errors'][] = "Row {$lineNumber}: duplicate email or roll number.";
            continue;
        }

        $clubId = null;
        if (!empty($data['club_id']) && ctype_digit($data['club_id'])) {
            $clubId = (int) $data['club_id'];
        }

        $year = null;
        if (isset($data['year']) && $data['year'] !== '' && is_numeric($data['year'])) {
            $year = (int) $data['year'];
        }

        $status = strtolower($data['status'] ?? 'approved');
        if (!in_array($status, ['approved', 'pending', 'rejected'], true)) {
            $status = 'approved';
        }

        $role = 'member';

        $phone = $data['phone'] ?? '';
        $department = $data['department'] ?? '';
        $section = $data['section'] ?? '';
        $bio = $data['bio'] ?? '';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users
            (
                roll_number,
                name,
                email,
                password,
                phone,
                year,
                department,
                section,
                club_id,
                bio,
                points,
                level,
                status,
                role,
                joined_at,
                created_at
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1, ?, ?, NOW(), NOW()
            )
        ");

        $stmt->execute([
            $roll,
            $name,
            $email,
            $passwordHash,
            $phone !== '' ? $phone : null,
            $year,
            $department !== '' ? $department : null,
            $section !== '' ? $section : null,
            $clubId,
            $bio !== '' ? $bio : null,
            $status,
            $role,
        ]);

        $result['created']++;
    }

    fclose($handle);

    return $result;
}

function bulkUpdateStudentStatus(PDO $pdo, array $studentIds, string $status): int
{
    $ids = bulkNormalizeIds($studentIds);

    if (empty($ids)) {
        return 0;
    }

    if (!in_array($status, ['approved', 'pending', 'rejected'], true)) {
        throw new RuntimeException('Invalid student status.');
    }

    $placeholders = bulkBuildPlaceholders($ids);

    $stmt = $pdo->prepare("
        UPDATE users
        SET status = ?
        WHERE id IN ($placeholders)
        AND role <> 'admin'
    ");

    $stmt->execute(array_merge([$status], $ids));

    return $stmt->rowCount();
}

function bulkRegisterStudentsToEvent(PDO $pdo, int $eventId, array $studentIds): array
{
    $ids = bulkNormalizeIds($studentIds);

    if ($eventId <= 0) {
        throw new RuntimeException('Please choose an event.');
    }

    if (empty($ids)) {
        return [
            'registered' => 0,
            'skipped' => 0,
        ];
    }

    $eventStmt = $pdo->prepare("
        SELECT id, title, COALESCE(max_participants, capacity, 0) AS max_participants
        FROM events
        WHERE id = ?
        LIMIT 1
    ");
    $eventStmt->execute([$eventId]);
    $event = $eventStmt->fetch();

    if (!$event) {
        throw new RuntimeException('Event not found.');
    }

    $countStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM registrations
        WHERE event_id = ?
    ");
    $countStmt->execute([$eventId]);
    $registeredCount = (int) $countStmt->fetchColumn();

    $registered = 0;
    $skipped = 0;

    $pdo->beginTransaction();

    try {
        $existingStmt = $pdo->prepare("
            SELECT id
            FROM registrations
            WHERE event_id = ?
            AND user_id = ?
            LIMIT 1
        ");

        $studentStmt = $pdo->prepare("
            SELECT id, role, status
            FROM users
            WHERE id = ?
            LIMIT 1
        ");

        $insertStmt = $pdo->prepare("
            INSERT INTO registrations
            (
                event_id,
                user_id,
                status,
                attendance,
                attended,
                points_awarded,
                registered_at
            )
            VALUES
            (
                ?, ?, 'Approved', FALSE, FALSE, FALSE, NOW()
            )
        ");

        foreach ($ids as $studentId) {
            if ((int) $event['max_participants'] > 0 && $registeredCount >= (int) $event['max_participants']) {
                $skipped++;
                continue;
            }

            $studentStmt->execute([$studentId]);
            $student = $studentStmt->fetch();

            if (
                !$student ||
                $student['role'] === 'admin' ||
                strtolower((string) $student['status']) !== 'approved'
            ) {
                $skipped++;
                continue;
            }

            $existingStmt->execute([$eventId, $studentId]);

            if ($existingStmt->fetchColumn()) {
                $skipped++;
                continue;
            }

            $insertStmt->execute([$eventId, $studentId]);
            $registered++;
            $registeredCount++;
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return [
        'registered' => $registered,
        'skipped' => $skipped,
    ];
}

function bulkMarkAttendance(PDO $pdo, array $registrationIds): int
{
    $ids = bulkNormalizeIds($registrationIds);

    if (empty($ids)) {
        return 0;
    }

    $placeholders = bulkBuildPlaceholders($ids);

    $stmt = $pdo->prepare("
        UPDATE registrations
        SET
            attendance = TRUE,
            attended = TRUE
        WHERE id IN ($placeholders)
    ");

    $stmt->execute($ids);

    return $stmt->rowCount();
}

function bulkAwardPoints(PDO $pdo, array $registrationIds, ?int $overridePoints, string $reason, int $addedBy): array
{
    $ids = bulkNormalizeIds($registrationIds);

    if (empty($ids)) {
        return [
            'awarded' => 0,
            'skipped' => 0,
        ];
    }

    $awarded = 0;
    $skipped = 0;

    $pdo->beginTransaction();

    try {
        $registrationStmt = $pdo->prepare("
            SELECT
                r.id,
                r.user_id,
                COALESCE(r.attended, r.attendance, FALSE) AS attended,
                COALESCE(r.points_awarded, FALSE) AS points_awarded,
                e.id AS event_id,
                e.title AS event_title,
                e.points AS event_points
            FROM registrations r
            LEFT JOIN events e ON e.id = r.event_id
            WHERE r.id = ?
            LIMIT 1
        ");

        $userLockStmt = $pdo->prepare("
            SELECT points
            FROM users
            WHERE id = ?
            FOR UPDATE
        ");

        $userUpdateStmt = $pdo->prepare("
            UPDATE users
            SET points = ?, level = ?
            WHERE id = ?
        ");

        $logStmt = $pdo->prepare("
            INSERT INTO point_logs
            (user_id, event_id, points, reason, added_by, added_at)
            VALUES
            (?, ?, ?, ?, ?, NOW())
        ");

        $registrationUpdateStmt = $pdo->prepare("
            UPDATE registrations
            SET points_awarded = TRUE
            WHERE id = ?
        ");

        foreach ($ids as $registrationId) {
            $registrationStmt->execute([$registrationId]);
            $registration = $registrationStmt->fetch();

            if (
                !$registration ||
                !$registration['attended'] ||
                $registration['points_awarded']
            ) {
                $skipped++;
                continue;
            }

            $pointsToAward = $overridePoints;

            if ($pointsToAward === null) {
                $pointsToAward = (int) $registration['event_points'];
            }

            if ($pointsToAward <= 0) {
                $skipped++;
                continue;
            }

            $userLockStmt->execute([$registration['user_id']]);
            $currentPoints = (int) $userLockStmt->fetchColumn();
            $newPoints = $currentPoints + $pointsToAward;
            $newLevel = max(1, (int) floor($newPoints / 100) + 1);

            $userUpdateStmt->execute([
                $newPoints,
                $newLevel,
                $registration['user_id'],
            ]);

            $logReason = trim($reason);
            if ($logReason === '') {
                $logReason = 'Participation - Event';
            }
            if (!empty($registration['event_title'])) {
                $logReason .= ' - ' . $registration['event_title'];
            }

            $logStmt->execute([
                $registration['user_id'],
                $registration['event_id'],
                $pointsToAward,
                $logReason,
                $addedBy > 0 ? $addedBy : null,
            ]);

            $registrationUpdateStmt->execute([$registrationId]);
            $awarded++;
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return [
        'awarded' => $awarded,
        'skipped' => $skipped,
    ];
}
