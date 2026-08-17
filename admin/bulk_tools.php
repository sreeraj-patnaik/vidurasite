<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/bulk_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$clubs = $pdo->query("
    SELECT id, name
    FROM clubs
    ORDER BY name
")->fetchAll();

$events = $pdo->query("
    SELECT id, title, event_date
    FROM events
    ORDER BY COALESCE(event_date, created_at) DESC, id DESC
")->fetchAll();

$studentSearch = trim($_GET['student_search'] ?? '');
$studentStatus = trim($_GET['student_status'] ?? 'pending');
$studentClub = (int) ($_GET['student_club'] ?? 0);

$studentSql = "
    SELECT
        users.id,
        users.name,
        users.roll_number,
        users.email,
        users.status,
        users.year,
        users.created_at,
        clubs.name AS club_name
    FROM users
    LEFT JOIN clubs ON clubs.id = users.club_id
    WHERE users.role <> 'admin'
";
$studentParams = [];

if ($studentSearch !== '') {
    $studentSql .= "
        AND (
            LOWER(users.name) LIKE LOWER(?)
            OR LOWER(users.roll_number) LIKE LOWER(?)
            OR LOWER(users.email) LIKE LOWER(?)
        )
    ";
    $key = '%' . $studentSearch . '%';
    $studentParams[] = $key;
    $studentParams[] = $key;
    $studentParams[] = $key;
}

if ($studentStatus !== '') {
    $studentSql .= " AND users.status = ? ";
    $studentParams[] = $studentStatus;
}

if ($studentClub > 0) {
    $studentSql .= " AND users.club_id = ? ";
    $studentParams[] = $studentClub;
}

$studentSql .= " ORDER BY users.created_at DESC LIMIT 100 ";

$studentStmt = $pdo->prepare($studentSql);
$studentStmt->execute($studentParams);
$studentRows = $studentStmt->fetchAll();

$registerEventId = (int) ($_GET['register_event'] ?? 0);
$registerSearch = trim($_GET['register_search'] ?? '');

$registrationStudents = [];
if ($registerEventId > 0) {
    $registerSql = "
        SELECT
            users.id,
            users.name,
            users.roll_number,
            users.status,
            clubs.name AS club_name
        FROM users
        LEFT JOIN clubs ON clubs.id = users.club_id
        WHERE users.role = 'member'
        AND users.status = 'approved'
        AND users.id NOT IN (
            SELECT user_id
            FROM registrations
            WHERE event_id = ?
        )
    ";
    $registerParams = [$registerEventId];

    if ($registerSearch !== '') {
        $registerSql .= "
            AND (
                LOWER(users.name) LIKE LOWER(?)
                OR LOWER(users.roll_number) LIKE LOWER(?)
            )
        ";
        $key = '%' . $registerSearch . '%';
        $registerParams[] = $key;
        $registerParams[] = $key;
    }

    $registerSql .= " ORDER BY users.name ASC LIMIT 100 ";

    $registerStmt = $pdo->prepare($registerSql);
    $registerStmt->execute($registerParams);
    $registrationStudents = $registerStmt->fetchAll();
}

$attendanceEventId = (int) ($_GET['attendance_event'] ?? 0);
$attendanceSearch = trim($_GET['attendance_search'] ?? '');

$attendanceRows = [];
if ($attendanceEventId > 0) {
    $attendanceSql = "
        SELECT
            registrations.id,
            users.name,
            users.roll_number,
            COALESCE(registrations.attended, registrations.attendance, FALSE) AS attended,
            COALESCE(registrations.points_awarded, FALSE) AS points_awarded
        FROM registrations
        LEFT JOIN users ON users.id = registrations.user_id
        WHERE registrations.event_id = ?
    ";
    $attendanceParams = [$attendanceEventId];

    if ($attendanceSearch !== '') {
        $attendanceSql .= "
            AND (
                LOWER(users.name) LIKE LOWER(?)
                OR LOWER(users.roll_number) LIKE LOWER(?)
            )
        ";
        $key = '%' . $attendanceSearch . '%';
        $attendanceParams[] = $key;
        $attendanceParams[] = $key;
    }

    $attendanceSql .= " ORDER BY users.name ASC LIMIT 100 ";

    $attendanceStmt = $pdo->prepare($attendanceSql);
    $attendanceStmt->execute($attendanceParams);
    $attendanceRows = $attendanceStmt->fetchAll();
}

$pointsEventId = (int) ($_GET['points_event'] ?? 0);
$pointsSearch = trim($_GET['points_search'] ?? '');

$pointsRows = [];
if ($pointsEventId > 0) {
    $pointsSql = "
        SELECT
            registrations.id,
            users.name,
            users.roll_number,
            COALESCE(registrations.attended, registrations.attendance, FALSE) AS attended,
            COALESCE(registrations.points_awarded, FALSE) AS points_awarded,
            events.title AS event_title,
            events.points AS event_points
        FROM registrations
        LEFT JOIN users ON users.id = registrations.user_id
        LEFT JOIN events ON events.id = registrations.event_id
        WHERE registrations.event_id = ?
    ";
    $pointsParams = [$pointsEventId];

    if ($pointsSearch !== '') {
        $pointsSql .= "
            AND (
                LOWER(users.name) LIKE LOWER(?)
                OR LOWER(users.roll_number) LIKE LOWER(?)
            )
        ";
        $key = '%' . $pointsSearch . '%';
        $pointsParams[] = $key;
        $pointsParams[] = $key;
    }

    $pointsSql .= " ORDER BY users.name ASC LIMIT 100 ";

    $pointsStmt = $pdo->prepare($pointsSql);
    $pointsStmt->execute($pointsParams);
    $pointsRows = $pointsStmt->fetchAll();
}

$flashSuccess = $_SESSION['success'] ?? '';
$flashError = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2>Bulk Operations</h2>
            <p class="text-muted mb-0">Upload students, approve members, register for events, mark attendance, and award points in batches.</p>
        </div>
    </div>

    <?php if ($flashSuccess !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="card-admin mb-4">
        <h5 class="mb-3">Bulk Upload Students</h5>
        <p class="text-muted mb-3">
            CSV columns: <code>roll_number,name,email,password,phone,department,year,section,club_id,status,bio</code>.
            Required: <code>roll_number</code>, <code>name</code>, <code>email</code>, <code>password</code>.
            Imported users are created as members only.
        </p>

        <form action="../actions/bulk_student_import.php" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
            <div class="col-lg-8">
                <label class="form-label">Student CSV</label>
                <input type="file" name="students_csv" class="form-control" accept=".csv,text/csv" required>
            </div>
            <div class="col-lg-4 d-grid">
                <button type="submit" class="btn btn-primary">Upload Students</button>
            </div>
        </form>
    </div>

    <div class="card-admin mb-4" id="student-approval">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h5 class="mb-1">Bulk Student Approval</h5>
                <p class="text-muted mb-0">Select students and change their status in one step.</p>
            </div>
        </div>

        <form method="GET" class="row g-3 mb-3">
            <div class="col-lg-4">
                <input type="text" name="student_search" class="form-control" placeholder="Search name, roll number, or email" value="<?= htmlspecialchars($studentSearch) ?>">
            </div>
            <div class="col-lg-3">
                <select name="student_status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= $studentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $studentStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $studentStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-lg-3">
                <select name="student_club" class="form-select">
                    <option value="">All Clubs</option>
                    <?php foreach ($clubs as $club): ?>
                        <option value="<?= (int) $club['id'] ?>" <?= $studentClub === (int) $club['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($club['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 d-grid">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
            </div>
        </form>

        <form action="../actions/bulk_student_status.php" method="POST">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <select name="status" class="form-select" style="max-width: 220px;" required>
                    <option value="">Choose status</option>
                    <option value="approved">Approve</option>
                    <option value="pending">Set Pending</option>
                    <option value="rejected">Reject</option>
                </select>
                <button type="submit" class="btn btn-primary">Apply to Selected</button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" data-select-all="student-status">
                            </th>
                            <th>Student</th>
                            <th>Club</th>
                            <th>Status</th>
                            <th>Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($studentRows)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">No students found for the selected filters.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($studentRows as $student): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input" data-select-item="student-status" name="student_ids[]" value="<?= (int) $student['id'] ?>">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($student['name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($student['roll_number']) ?> | <?= htmlspecialchars($student['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($student['club_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars(ucfirst($student['status'] ?? 'pending')) ?></td>
                                <td><?= htmlspecialchars((string) ($student['year'] ?? '-')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <div class="card-admin mb-4" id="event-registration">
        <h5 class="mb-1">Bulk Event Registration</h5>
        <p class="text-muted mb-3">Choose an event, load approved students, then register everyone you select.</p>

        <form method="GET" class="row g-3 mb-3">
            <div class="col-lg-5">
                <select name="register_event" class="form-select" required>
                    <option value="">Select Event</option>
                    <?php foreach ($events as $event): ?>
                        <option value="<?= (int) $event['id'] ?>" <?= $registerEventId === (int) $event['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($event['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-5">
                <input type="text" name="register_search" class="form-control" placeholder="Search approved students" value="<?= htmlspecialchars($registerSearch) ?>">
            </div>
            <div class="col-lg-2 d-grid">
                <button type="submit" class="btn btn-outline-primary">Load Students</button>
            </div>
        </form>

        <?php if ($registerEventId > 0): ?>
            <form action="../actions/bulk_event_register.php" method="POST">
                <input type="hidden" name="event_id" value="<?= $registerEventId ?>">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="submit" class="btn btn-primary">Register Selected Students</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" data-select-all="event-register">
                                </th>
                                <th>Student</th>
                                <th>Club</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($registrationStudents)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">No eligible students found for this event.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($registrationStudents as $student): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" data-select-item="event-register" name="student_ids[]" value="<?= (int) $student['id'] ?>">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($student['name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($student['roll_number']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($student['club_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars(ucfirst($student['status'] ?? 'approved')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <div class="card-admin mb-4" id="bulk-attendance">
        <h5 class="mb-1">Bulk Attendance</h5>
        <p class="text-muted mb-3">Select registrations and mark attendance for an event.</p>

        <form method="GET" class="row g-3 mb-3">
            <div class="col-lg-5">
                <select name="attendance_event" class="form-select" required>
                    <option value="">Select Event</option>
                    <?php foreach ($events as $event): ?>
                        <option value="<?= (int) $event['id'] ?>" <?= $attendanceEventId === (int) $event['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($event['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-5">
                <input type="text" name="attendance_search" class="form-control" placeholder="Search registered students" value="<?= htmlspecialchars($attendanceSearch) ?>">
            </div>
            <div class="col-lg-2 d-grid">
                <button type="submit" class="btn btn-outline-primary">Load Registrations</button>
            </div>
        </form>

        <?php if ($attendanceEventId > 0): ?>
            <form action="../actions/bulk_attendance.php" method="POST">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="submit" class="btn btn-primary">Mark Selected Present</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" data-select-all="bulk-attendance">
                                </th>
                                <th>Student</th>
                                <th>Attendance</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendanceRows)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">No registrations found for this event.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($attendanceRows as $row): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" data-select-item="bulk-attendance" name="registration_ids[]" value="<?= (int) $row['id'] ?>">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['name'] ?? 'Unknown') ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['roll_number'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <?= !empty($row['attended']) ? '<span class="badge bg-success">Present</span>' : '<span class="badge bg-secondary">Pending</span>' ?>
                                    </td>
                                    <td>
                                        <?= !empty($row['points_awarded']) ? '<span class="badge bg-info">Awarded</span>' : '<span class="badge bg-light text-dark">Pending</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <div class="card-admin" id="bulk-points">
        <h5 class="mb-1">Bulk Points Awarding</h5>
        <p class="text-muted mb-3">Select attended registrations and award event points or a custom amount.</p>

        <form method="GET" class="row g-3 mb-3">
            <div class="col-lg-5">
                <select name="points_event" class="form-select" required>
                    <option value="">Select Event</option>
                    <?php foreach ($events as $event): ?>
                        <option value="<?= (int) $event['id'] ?>" <?= $pointsEventId === (int) $event['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($event['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-5">
                <input type="text" name="points_search" class="form-control" placeholder="Search attendees" value="<?= htmlspecialchars($pointsSearch) ?>">
            </div>
            <div class="col-lg-2 d-grid">
                <button type="submit" class="btn btn-outline-primary">Load Attendees</button>
            </div>
        </form>

        <?php if ($pointsEventId > 0): ?>
            <form action="../actions/bulk_points_award.php" method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-lg-2">
                        <label class="form-label">Custom Points</label>
                        <input type="number" name="points" class="form-control" placeholder="Use event points" step="1">
                    </div>
                    <div class="col-lg-8">
                        <label class="form-label">Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="Optional reason">
                    </div>
                    <div class="col-lg-2 d-grid align-self-end">
                        <button type="submit" class="btn btn-primary">Award Selected</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" data-select-all="bulk-points">
                                </th>
                                <th>Student</th>
                                <th>Status</th>
                                <th>Event Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pointsRows)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">No attendees found for this event.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($pointsRows as $row): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" data-select-item="bulk-points" name="registration_ids[]" value="<?= (int) $row['id'] ?>">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['name'] ?? 'Unknown') ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['roll_number'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['points_awarded'])): ?>
                                            <span class="badge bg-info">Awarded</span>
                                        <?php elseif (!empty($row['attended'])): ?>
                                            <span class="badge bg-success">Attended</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int) ($row['event_points'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
</div>

<script>
document.querySelectorAll('[data-select-all]').forEach(function(master) {
    master.addEventListener('change', function() {
        var key = master.getAttribute('data-select-all');
        document.querySelectorAll('[data-select-item="' + key + '"]').forEach(function(box) {
            box.checked = master.checked;
        });
    });
});
</script>

<?php include 'partials/footer.php'; ?>
