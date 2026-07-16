<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
$eventId = (int) ($_GET['event'] ?? 0);

$events = $pdo->query("
SELECT id, title, event_date
FROM events
ORDER BY COALESCE(event_date, created_at) DESC, id DESC
")->fetchAll();

$statsSql = "
SELECT
    COUNT(*) AS total_registrations,
    COUNT(*) FILTER (WHERE COALESCE(attended, attendance, false) = true) AS attended_count,
    COUNT(*) FILTER (WHERE COALESCE(attended, attendance, false) = false) AS pending_count
FROM registrations
WHERE 1=1
";
$statsParams = [];

if ($eventId > 0) {
    $statsSql .= " AND event_id = ? ";
    $statsParams[] = $eventId;
}

$statsStmt = $pdo->prepare($statsSql);
$statsStmt->execute($statsParams);
$stats = $statsStmt->fetch() ?: [
    'total_registrations' => 0,
    'attended_count' => 0,
    'pending_count' => 0,
];

$listSql = "
SELECT
    registrations.id,
    registrations.event_id,
    registrations.registered_at,
    registrations.remarks,
    COALESCE(registrations.attended, registrations.attendance, false) AS attended,
    users.name AS member_name,
    users.roll_number,
    events.title AS event_title,
    events.event_date
FROM registrations
LEFT JOIN users ON users.id = registrations.user_id
LEFT JOIN events ON events.id = registrations.event_id
WHERE 1=1
";
$listParams = [];

if ($eventId > 0) {
    $listSql .= " AND registrations.event_id = ? ";
    $listParams[] = $eventId;
}

if ($search !== '') {
    $listSql .= "
    AND (
        LOWER(users.name) LIKE LOWER(?)
        OR LOWER(users.roll_number) LIKE LOWER(?)
        OR LOWER(events.title) LIKE LOWER(?)
    )
    ";
    $key = '%' . $search . '%';
    $listParams[] = $key;
    $listParams[] = $key;
    $listParams[] = $key;
}

$listSql .= "
ORDER BY registrations.registered_at DESC, registrations.id DESC
LIMIT 50
";

$listStmt = $pdo->prepare($listSql);
$listStmt->execute($listParams);
$registrations = $listStmt->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2>Attendance</h2>
            <p class="text-muted mb-0">Review registered members and attendance status across events.</p>
        </div>
        <a href="registrations.php" class="btn btn-primary">
            <i class="bi bi-clipboard-check me-1"></i> Registrations
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <small>Total Registrations</small>
                <h2><?= (int) $stats['total_registrations'] ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <small>Attendance Marked</small>
                <h2><?= (int) $stats['attended_count'] ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <small>Pending Attendance</small>
                <h2><?= (int) $stats['pending_count'] ?></h2>
            </div>
        </div>
    </div>

    <div class="card-admin mb-4">
        <form method="GET">
            <div class="row g-3">
                <div class="col-md-6">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search student or event..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <select name="event" class="form-select">
                        <option value="">All Events</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= (int) $event['id'] ?>" <?= $eventId === (int) $event['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($event['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-admin">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Event</th>
                        <th>Attendance</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registrations)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($registrations as $row): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($row['member_name'] ?? 'Unknown') ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($row['roll_number'] ?? '-') ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['event_title'] ?? '-') ?><br>
                                <small class="text-muted">
                                    <?= !empty($row['event_date']) ? date('d M Y, h:i A', strtotime($row['event_date'])) : '-' ?>
                                </small>
                            </td>
                            <td>
                                <?php if (!empty($row['attended'])): ?>
                                    <span class="badge bg-success">Present</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?= !empty($row['registered_at']) ? date('d M Y, h:i A', strtotime($row['registered_at'])) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
