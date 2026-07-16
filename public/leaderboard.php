<?php
require_once '../config/config.php';
require_once '../config/database.php';

$leaders = $pdo->query("
SELECT
    users.name,
    users.roll_number,
    users.points,
    users.level,
    clubs.name AS club_name,
    clubs.theme_color
FROM users
LEFT JOIN clubs ON clubs.id = users.club_id
WHERE users.status='approved'
ORDER BY users.points DESC, users.level DESC, users.name ASC
LIMIT 20
")->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="section-kicker"><i class="bi bi-trophy-fill"></i> Leaderboard</div>
        <h1 class="display-5 fw-bold mb-2">Top members across VIDURA</h1>
        <p class="text-muted fs-5 mb-0">Public view of the highest ranked members and their current score.</p>
    </div>

    <div class="member-panel">
        <div class="member-panel__inner">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Rank</th><th>Member</th><th>Club</th><th>Level</th><th>Points</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaders)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-5">No public ranking data found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($leaders as $i => $leader): ?>
                            <tr>
                                <td>#<?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($leader['name']) ?><br><small class="text-muted"><?= htmlspecialchars($leader['roll_number']) ?></small></td>
                                <td><span class="badge" style="background:<?= htmlspecialchars($leader['theme_color'] ?? '#16a34a') ?>;"><?= htmlspecialchars($leader['club_name'] ?? '-') ?></span></td>
                                <td><?= (int) $leader['level'] ?></td>
                                <td><strong class="text-success"><?= (int) $leader['points'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
