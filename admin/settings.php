<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$settings = $pdo->query("
SELECT *
FROM settings
ORDER BY id ASC
LIMIT 1
")->fetch();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2>Settings</h2>
            <p class="text-muted mb-0">Current platform configuration and system details.</p>
        </div>
        <a href="reports.php" class="btn btn-primary">
            <i class="bi bi-graph-up me-1"></i> Reports
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-admin h-100">
                <h5 class="mb-3">Platform Settings</h5>
                <?php if ($settings): ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <tbody>
                                <tr><th width="240">Website Title</th><td><?= htmlspecialchars($settings['website_title'] ?? '-') ?></td></tr>
                                <tr><th>Membership Fee</th><td><?= htmlspecialchars($settings['membership_fee'] ?? '-') ?></td></tr>
                                <tr><th>Semester</th><td><?= htmlspecialchars($settings['semester'] ?? '-') ?></td></tr>
                                <tr><th>Contact Email</th><td><?= htmlspecialchars($settings['contact_email'] ?? '-') ?></td></tr>
                                <tr><th>Homepage Banner</th><td><?= htmlspecialchars($settings['homepage_banner'] ?? '-') ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">No settings record exists yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-admin h-100">
                <h5 class="mb-3">System Info</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Server</span><strong><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>PHP Version</span><strong><?= htmlspecialchars(PHP_VERSION) ?></strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Timezone</span><strong><?= htmlspecialchars(date_default_timezone_get()) ?></strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Current User</span><strong><?= htmlspecialchars($_SESSION['name']) ?></strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Role</span><strong>Admin</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
