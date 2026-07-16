<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$leaders = $pdo->query("
SELECT
    users.id,
    users.name,
    users.roll_number,
    users.points,
    users.level,
    users.profile_photo,
    clubs.name AS club_name,
    clubs.theme_color
FROM users
LEFT JOIN clubs ON clubs.id = users.club_id
WHERE users.role='member'
  AND users.status='approved'
ORDER BY users.points DESC, users.level DESC, users.name ASC
LIMIT 100
")->fetchAll();

$topScore = (int) ($leaders[0]['points'] ?? 0);
$memberCount = count($leaders);

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2>Leaderboard</h2>
            <p class="text-muted mb-0">A quick ranking of approved members by points and level.</p>
        </div>
        <a href="points.php" class="btn btn-warning">
            <i class="bi bi-star-fill me-1"></i> Points
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <small>Members Ranked</small>
                <h2><?= $memberCount ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <small>Top Score</small>
                <h2><?= number_format($topScore) ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <small>Ranking Basis</small>
                <h2>Points</h2>
            </div>
        </div>
    </div>

    <div class="card-admin">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Member</th>
                        <th>Club</th>
                        <th>Level</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaders)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">No approved members found.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($leaders as $index => $leader): ?>
                        <tr>
                            <td class="fw-bold">#<?= $index + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($leader['name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($leader['roll_number']) ?></small>
                            </td>
                            <td>
                                <span class="badge" style="background:<?= htmlspecialchars($leader['theme_color'] ?? '#0d6efd') ?>">
                                    <?= htmlspecialchars($leader['club_name'] ?? '-') ?>
                                </span>
                            </td>
                            <td>Level <?= (int) $leader['level'] ?></td>
                            <td><strong class="text-success"><?= number_format((int) $leader['points']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
