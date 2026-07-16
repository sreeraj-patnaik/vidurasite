<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("
SELECT
    users.name,
    users.points,
    users.level,
    users.profile_photo,
    clubs.name AS club_name
FROM users
LEFT JOIN clubs ON clubs.id = users.club_id
WHERE users.id=?
LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
SELECT
    badges.*,
    user_badges.awarded_at,
    admins.name AS awarded_by_name
FROM user_badges
JOIN badges ON badges.id = user_badges.badge_id
LEFT JOIN users admins ON admins.id = user_badges.awarded_by
WHERE user_badges.user_id=?
ORDER BY user_badges.awarded_at DESC
");
$stmt->execute([$userId]);
$badges = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-lg-center">
            <div>
                <span class="id-badge mb-3">
                    <i class="bi bi-award-fill"></i>
                    Achievement Center
                </span>
                <h1 class="display-6 fw-bold mb-2">Your earned badges</h1>
                <p class="text-muted mb-0">
                    Badge history, award dates, and the activity behind each achievement.
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-custom">Dashboard</a>
                <a href="idcard.php" class="btn btn-primary-custom">Digital ID</a>
            </div>
        </div>
    </div>

    <div class="metric-grid mb-4">
        <div class="metric-card">
            <div class="label">Badges</div>
            <div class="value"><?= count($badges) ?></div>
            <div class="text-muted small">Earned achievements</div>
        </div>
        <div class="metric-card">
            <div class="label">Level</div>
            <div class="value"><?= (int) ($user['level'] ?? 0) ?></div>
            <div class="text-muted small">Current growth tier</div>
        </div>
        <div class="metric-card">
            <div class="label">Points</div>
            <div class="value"><?= (int) ($user['points'] ?? 0) ?></div>
            <div class="text-muted small">Total contribution score</div>
        </div>
        <div class="metric-card">
            <div class="label">Club</div>
            <div class="value mono-code" style="font-size:1.3rem;"><?= htmlspecialchars($user['club_name'] ?? 'None') ?></div>
            <div class="text-muted small">Current home club</div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="section-shell p-4 h-100">
                <div class="text-center">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img
                            src="../uploads/profiles/<?= htmlspecialchars($user['profile_photo']) ?>"
                            alt="<?= htmlspecialchars($user['name']) ?>"
                            class="rounded-circle mb-3"
                            style="width:118px;height:118px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:118px;height:118px;background:linear-gradient(135deg,#16a34a,#0ea5e9);color:#fff;font-size:52px;font-weight:800;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>

                    <h3 class="mb-1"><?= htmlspecialchars($user['name']) ?></h3>
                    <p class="text-muted mb-0"><?= htmlspecialchars($user['club_name'] ?? 'No Club') ?></p>
                </div>

                <div class="mt-4 row g-3">
                    <div class="col-6">
                        <div class="panel-stat text-center">
                            <div class="mono-code fw-bold"><?= (int) ($user['level'] ?? 0) ?></div>
                            <div class="small text-muted">Level</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="panel-stat text-center">
                            <div class="mono-code fw-bold"><?= (int) ($user['points'] ?? 0) ?></div>
                            <div class="small text-muted">Points</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="section-shell p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-1">Badges Earned</h3>
                        <p class="text-muted mb-0">A visual log of the achievements you have unlocked.</p>
                    </div>
                </div>

                <?php if (empty($badges)): ?>
                    <div class="alert alert-info mb-0">No badges awarded yet.</div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($badges as $badge): ?>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:62px;height:62px;background:<?= htmlspecialchars($badge['color'] ?? '#16a34a') ?>;">
                                            <?php if (!empty($badge['icon'])): ?>
                                                <img
                                                    src="../uploads/badges/<?= htmlspecialchars($badge['icon']) ?>"
                                                    alt="<?= htmlspecialchars($badge['title'] ?? 'Badge') ?>"
                                                    style="width:36px;height:36px;object-fit:contain;">
                                            <?php else: ?>
                                                <i class="bi bi-award-fill text-white fs-3"></i>
                                            <?php endif; ?>
                                        </div>

                                        <div>
                                            <h5 class="mb-1"><?= htmlspecialchars($badge['title'] ?? '') ?></h5>
                                            <small class="text-muted">
                                                <?= !empty($badge['awarded_at']) ? date('d M Y, h:i A', strtotime($badge['awarded_at'])) : 'Award date unavailable' ?>
                                            </small>
                                        </div>
                                    </div>

                                    <p class="mt-3 mb-2 text-muted">
                                        <?= htmlspecialchars($badge['description'] ?? '') ?>
                                    </p>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($badge['awarded_by_name'] ?? 'System') ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
