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
    users.*,
    clubs.name AS club_name,
    clubs.theme_color
FROM users
LEFT JOIN clubs ON clubs.id = users.club_id
WHERE users.id=?
LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

$stmt = $pdo->prepare("
SELECT *
FROM point_logs
WHERE user_id=?
ORDER BY added_at DESC
LIMIT 5
");
$stmt->execute([$userId]);
$activities = $stmt->fetchAll();

$stmt = $pdo->prepare("
SELECT events.*
FROM registrations
JOIN events ON events.id = registrations.event_id
WHERE registrations.user_id=?
AND events.status='Upcoming'
ORDER BY events.event_date
LIMIT 4
");
$stmt->execute([$userId]);
$events = $stmt->fetchAll();

$stmt = $pdo->prepare("
SELECT
    badges.*,
    user_badges.awarded_at
FROM user_badges
JOIN badges ON badges.id = user_badges.badge_id
WHERE user_badges.user_id=?
ORDER BY user_badges.awarded_at DESC
LIMIT 4
");
$stmt->execute([$userId]);
$badges = $stmt->fetchAll();

$stmt = $pdo->query("
SELECT id, title, description, created_at, expires_at
FROM announcements
WHERE expires_at IS NULL OR expires_at >= NOW()
ORDER BY created_at DESC
LIMIT 4
");
$announcements = $stmt->fetchAll();

$stmt = $pdo->query("
SELECT id
FROM users
ORDER BY points DESC, created_at ASC
");

$rank = 1;
while ($row = $stmt->fetch()) {
    if ((int) $row['id'] === $userId) {
        break;
    }
    $rank++;
}

$nextLevel = max(100, ((int) $user['level']) * 100);
$progress = min(100, (($user['points'] ?? 0) / $nextLevel) * 100);

$userPoints = (int) ($user['points'] ?? 0);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <?php if (!empty($_SESSION['success']) || !empty($_SESSION['error'])): ?>
        <div class="mb-4">
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success mb-2"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger mb-0"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php endif; ?>
            <?php unset($_SESSION['success'], $_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
            <div>
                <div class="section-kicker">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Member Dashboard
                </div>
                <h1 class="display-5 fw-bold mb-2">
                    Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>
                </h1>
                <p class="text-muted mb-0 fs-5" style="max-width:760px;">
                    Track your points, events, badges, and announcements in a clean workspace built around clarity and momentum.
                </p>
            </div>

            <div class="points-hero">
                <div class="points-icon">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <div class="points-label">Total Points</div>
                    <div class="points-value"><?= $userPoints ?></div>
                    <div class="small text-muted">Level <?= (int) $user['level'] ?>, Rank #<?= (int) $rank ?></div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="idcard.php" class="btn btn-primary-custom px-4 py-3">Digital ID</a>
            <a href="profile.php" class="btn btn-outline-custom px-4 py-3">Edit Profile</a>
            <a href="events.php" class="btn btn-outline-custom px-4 py-3">Browse Events</a>
        </div>
    </div>

    <div class="member-grid">
        <aside class="member-sidebar">
            <div class="member-panel">
                <div class="member-panel__inner">
                    <div class="text-center">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img
                                src="../uploads/profiles/<?= htmlspecialchars($user['profile_photo']) ?>"
                                alt="<?= htmlspecialchars($user['name']) ?>"
                                class="rounded-circle mb-3"
                                style="width:120px;height:120px;object-fit:cover;">
                        <?php else: ?>
                            <div
                                class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                style="width:120px;height:120px;background:linear-gradient(135deg,#16a34a,#0ea5e9);color:#fff;font-size:54px;font-weight:800;">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <h3 class="mb-1"><?= htmlspecialchars($user['name']) ?></h3>
                        <p class="text-muted mb-3"><?= htmlspecialchars($user['club_name'] ?? 'No Club') ?></p>
                        <span class="rank-badge">Verified member</span>
                    </div>

                    <div class="row g-3 mt-4">
                        <div class="col-6">
                            <div class="mini-metric">
                                <div class="label">Year</div>
                                <div class="value"><?= htmlspecialchars($user['year'] ?: '-') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mini-metric">
                                <div class="label">Section</div>
                                <div class="value"><?= htmlspecialchars($user['section'] ?: '-') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mini-metric">
                                <div class="label">Level</div>
                                <div class="value"><?= (int) $user['level'] ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mini-metric">
                                <div class="label">Rank</div>
                                <div class="value">#<?= (int) $rank ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Progress to next level</span>
                            <span class="mono-code fw-semibold"><?= (int) $progress ?>%</span>
                        </div>
                        <div class="progress progress-soft">
                            <div class="progress-bar" style="width: <?= $progress ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 small text-muted">
                            <span><?= $userPoints ?> points</span>
                            <span>Next at <?= $nextLevel ?> points</span>
                        </div>
                    </div>

                    <div class="panel-stat mt-4">
                        <div class="small text-muted mb-1">Bio</div>
                        <div><?= htmlspecialchars($user['bio'] ?: 'No bio added yet.') ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="member-content">
            <div class="row g-4 mb-4">
                <div class="col-md-3 col-6">
                    <div class="mini-metric h-100">
                        <div class="label">Points</div>
                        <div class="value"><?= $userPoints ?></div>
                        <div class="small text-muted">Earn through participation</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-metric h-100">
                        <div class="label">Badges</div>
                        <div class="value"><?= count($badges) ?></div>
                        <div class="small text-muted">Achievements unlocked</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-metric h-100">
                        <div class="label">Events</div>
                        <div class="value"><?= count($events) ?></div>
                        <div class="small text-muted">Upcoming registrations</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="mini-metric h-100">
                        <div class="label">Announcements</div>
                        <div class="value"><?= count($announcements) ?></div>
                        <div class="small text-muted">Active notices</div>
                    </div>
                </div>
            </div>

            <div class="member-panel mb-4">
                <div class="member-panel__inner">
                    <div class="section-title-row">
                        <div>
                            <div class="section-kicker">
                                <i class="bi bi-megaphone-fill"></i>
                                Latest Announcements
                            </div>
                            <h3 class="mb-1">Stay in the loop</h3>
                            <p class="text-muted mb-0">Official notices from admins and clubs.</p>
                        </div>
                        <a href="../public/gallery.php" class="btn btn-outline-custom">Gallery</a>
                    </div>

                    <?php if (empty($announcements)): ?>
                        <div class="alert alert-info mb-0">No active announcements right now.</div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="col-md-6">
                                    <div class="announcement-item h-100">
                                        <h5 class="mb-2"><?= htmlspecialchars($announcement['title']) ?></h5>
                                        <p class="text-muted mb-3">
                                            <?= htmlspecialchars(substr(strip_tags($announcement['description'] ?? ''), 0, 120)) ?>
                                            <?= strlen(strip_tags($announcement['description'] ?? '')) > 120 ? '...' : '' ?>
                                        </p>
                                        <span class="star-badge">
                                            <i class="bi bi-calendar3 star"></i>
                                            <?php if (!empty($announcement['expires_at'])): ?>
                                                Expires <?= date('d M Y, h:i A', strtotime($announcement['expires_at'])) ?>
                                            <?php else: ?>
                                                No expiry
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="member-panel h-100">
                        <div class="member-panel__inner">
                            <div class="section-title-row">
                                <div>
                                    <div class="section-kicker">
                                        <i class="bi bi-calendar-event-fill"></i>
                                        Upcoming Events
                                    </div>
                                    <h3 class="mb-1">Registered events</h3>
                                    <p class="text-muted mb-0">Events you are currently enrolled in.</p>
                                </div>
                                <a href="events.php" class="btn btn-outline-custom">Browse</a>
                            </div>

                            <?php if (empty($events)): ?>
                                <div class="alert alert-info mb-0">No upcoming events registered.</div>
                            <?php else: ?>
                                <div class="d-grid gap-3">
                                    <?php foreach ($events as $event): ?>
                                        <div class="event-item">
                                            <div class="d-flex justify-content-between gap-3 align-items-start">
                                                <div>
                                                    <h5 class="mb-1"><?= htmlspecialchars($event['title']) ?></h5>
                                                    <div class="text-muted small mb-2"><?= htmlspecialchars($event['venue'] ?? '-') ?></div>
                                                    <span class="star-badge">
                                                        <i class="bi bi-star-fill star"></i>
                                                        <?= (int) ($event['points'] ?? 0) ?> points
                                                    </span>
                                                </div>
                                                <div class="text-end mono-code">
                                                    <div class="fw-bold"><?= !empty($event['event_date']) ? date('d M', strtotime($event['event_date'])) : '-' ?></div>
                                                    <small class="text-muted"><?= !empty($event['start_time']) ? date('h:i A', strtotime($event['start_time'])) : '-' ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="member-panel h-100">
                        <div class="member-panel__inner">
                            <div class="section-title-row">
                                <div>
                                    <div class="section-kicker">
                                        <i class="bi bi-clock-history"></i>
                                        Recent Activity
                                    </div>
                                    <h3 class="mb-1">Points log</h3>
                                    <p class="text-muted mb-0">A compact history of your recent point changes.</p>
                                </div>
                            </div>

                            <?php if (empty($activities)): ?>
                                <div class="alert alert-info mb-0">No recent activity.</div>
                            <?php else: ?>
                                <div class="d-grid gap-3">
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="activity-item">
                                            <div>
                                                <div class="fw-semibold mb-1"><?= htmlspecialchars($activity['reason'] ?? '') ?></div>
                                                <div class="text-muted small"><?= !empty($activity['added_at']) ? date('d M Y, h:i A', strtotime($activity['added_at'])) : '-' ?></div>
                                            </div>
                                            <?php if ((int) $activity['points'] >= 0): ?>
                                                <span class="badge bg-success rounded-pill px-3 py-2">+<?= (int) $activity['points'] ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger rounded-pill px-3 py-2"><?= (int) $activity['points'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="member-panel h-100">
                        <div class="member-panel__inner">
                            <div class="section-title-row">
                                <div>
                                    <div class="section-kicker">
                                        <i class="bi bi-award-fill"></i>
                                        Latest Badges
                                    </div>
                                    <h3 class="mb-1">Your achievements</h3>
                                    <p class="text-muted mb-0">Badges earned and the dates they were awarded.</p>
                                </div>
                                <a href="badges.php" class="btn btn-outline-custom">All Badges</a>
                            </div>

                            <?php if (empty($badges)): ?>
                                <div class="alert alert-info mb-0">No badges earned yet.</div>
                            <?php else: ?>
                                <div class="d-grid gap-3">
                                    <?php foreach ($badges as $badge): ?>
                                        <div class="badge-item">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:60px;height:60px;background:<?= htmlspecialchars($badge['color'] ?? '#16a34a') ?>;">
                                                    <?php if (!empty($badge['icon'])): ?>
                                                        <img
                                                            src="../uploads/badges/<?= htmlspecialchars($badge['icon']) ?>"
                                                            alt="<?= htmlspecialchars($badge['title'] ?? 'Badge') ?>"
                                                            style="width:34px;height:34px;object-fit:contain;">
                                                    <?php else: ?>
                                                        <i class="bi bi-award-fill text-white fs-4"></i>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between gap-3">
                                                        <div>
                                                            <h5 class="mb-1"><?= htmlspecialchars($badge['title'] ?? '') ?></h5>
                                                            <div class="text-muted small"><?= htmlspecialchars($badge['description'] ?? '') ?></div>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="rank-badge mb-2">Awarded</span>
                                                            <div class="text-muted small">
                                                                <?= !empty($badge['awarded_at']) ? date('d M Y', strtotime($badge['awarded_at'])) : '' ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="member-panel h-100">
                        <div class="member-panel__inner">
                            <div class="section-title-row">
                                <div>
                                    <div class="section-kicker">
                                        <i class="bi bi-lightning-charge-fill"></i>
                                        Quick Actions
                                    </div>
                                    <h3 class="mb-1">Jump right in</h3>
                                    <p class="text-muted mb-0">Fast access to the most-used member pages.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6 col-6">
                                    <a href="events.php" class="btn btn-primary-custom w-100 py-4 h-100">
                                        <i class="bi bi-calendar-event d-block fs-4 mb-2"></i>
                                        Events
                                    </a>
                                </div>
                                <div class="col-md-6 col-6">
                                    <a href="leaderboard.php" class="btn btn-outline-custom w-100 py-4 h-100">
                                        <i class="bi bi-trophy-fill d-block fs-4 mb-2"></i>
                                        Leaderboard
                                    </a>
                                </div>
                                <div class="col-md-6 col-6">
                                    <a href="badges.php" class="btn btn-outline-custom w-100 py-4 h-100">
                                        <i class="bi bi-award-fill d-block fs-4 mb-2"></i>
                                        Badges
                                    </a>
                                </div>
                                <div class="col-md-6 col-6">
                                    <a href="idcard.php" class="btn btn-primary-custom w-100 py-4 h-100">
                                        <i class="bi bi-person-badge-fill d-block fs-4 mb-2"></i>
                                        Digital ID
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
