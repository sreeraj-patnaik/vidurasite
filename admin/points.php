<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
$selectedUser = intval($_GET['user'] ?? ($_GET['user_id'] ?? 0));

$memberSql = "
SELECT id, name, roll_number, points, level
FROM users
WHERE role='member'
";
$memberParams = [];

if ($search !== '') {
    $memberSql .= "
    AND (
        LOWER(name) LIKE LOWER(?)
        OR LOWER(roll_number) LIKE LOWER(?)
    )
    ";
    $key = '%' . $search . '%';
    $memberParams[] = $key;
    $memberParams[] = $key;
}

$memberSql .= " ORDER BY name ASC";

$stmt = $pdo->prepare($memberSql);
$stmt->execute($memberParams);
$members = $stmt->fetchAll();

$logSql = "
SELECT
    point_logs.*,
    u.name AS member_name,
    u.roll_number,
    a.name AS added_by_name
FROM point_logs
LEFT JOIN users u ON u.id = point_logs.user_id
LEFT JOIN users a ON a.id = point_logs.added_by
ORDER BY point_logs.added_at DESC
LIMIT 20
";
$pointLogs = $pdo->query($logSql)->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Points Management</h2>
            <p class="text-muted mb-0">Award points, review logs, and keep member levels in sync.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card-admin h-100">
                <h5 class="mb-3">Award Points</h5>

                <form method="POST" action="../actions/points_award.php">
                    <div class="mb-3">
                        <label class="form-label">Member</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select member</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= (int) $member['id'] ?>" <?= $selectedUser === (int) $member['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($member['name']) ?> (<?= htmlspecialchars($member['roll_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Points</label>
                        <input
                            type="number"
                            name="points"
                            class="form-control"
                            value="5"
                            step="1"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Save Points</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card-admin h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Recent Point Logs</h5>
                    <form method="GET" class="d-flex gap-2">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search members..."
                            value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="user" value="<?= $selectedUser > 0 ? (int) $selectedUser : '' ?>">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Points</th>
                                <th>Reason</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pointLogs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No point history yet.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($pointLogs as $log): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($log['member_name'] ?? 'Unknown') ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($log['roll_number'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <?php if ((int) $log['points'] >= 0): ?>
                                            <span class="badge bg-success fs-6">+<?= (int) $log['points'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger fs-6"><?= (int) $log['points'] ?></span>
                                        <?php endif; ?>
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
</div>
</div>

<?php include 'partials/footer.php'; ?>
