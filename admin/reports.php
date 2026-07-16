<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$summary = [
    'members' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='member'")->fetchColumn(),
    'approved' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='member' AND status='approved'")->fetchColumn(),
    'pending' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='member' AND status='pending'")->fetchColumn(),
    'events' => (int) $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn(),
    'registrations' => (int) $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn(),
    'points' => (int) $pdo->query("SELECT COALESCE(SUM(points), 0) FROM point_logs")->fetchColumn(),
];

$recentLogs = $pdo->query("
SELECT
    point_logs.points,
    point_logs.reason,
    point_logs.added_at,
    users.name AS member_name,
    users.roll_number
FROM point_logs
LEFT JOIN users ON users.id = point_logs.user_id
ORDER BY point_logs.added_at DESC
LIMIT 10
")->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2>Reports</h2>
            <p class="text-muted mb-0">High-level system totals and the latest point activity.</p>
        </div>
        <a href="settings.php" class="btn btn-outline-primary">
            <i class="bi bi-gear me-1"></i> Settings
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="stat-card"><small>Members</small><h2><?= $summary['members'] ?></h2></div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card"><small>Approved</small><h2><?= $summary['approved'] ?></h2></div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card"><small>Pending</small><h2><?= $summary['pending'] ?></h2></div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card"><small>Events</small><h2><?= $summary['events'] ?></h2></div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card"><small>Registrations</small><h2><?= $summary['registrations'] ?></h2></div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card"><small>Total Points</small><h2><?= number_format($summary['points']) ?></h2></div>
        </div>
    </div>

    <div class="card-admin">
        <h5 class="mb-3">Recent Point Activity</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Points</th>
                        <th>Reason</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentLogs)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">No point logs recorded yet.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($log['member_name'] ?? 'Unknown') ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($log['roll_number'] ?? '-') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-success fs-6">
                                    <?= (int) $log['points'] >= 0 ? '+' . (int) $log['points'] : (int) $log['points'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($log['reason'] ?? '') ?></td>
                            <td><?= !empty($log['added_at']) ? date('d M Y, h:i A', strtotime($log['added_at'])) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
