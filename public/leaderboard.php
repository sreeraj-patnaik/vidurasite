<?php

require_once '../config/config.php';
require_once '../config/database.php';

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
WHERE users.status='approved'
ORDER BY users.points DESC, users.level DESC, users.name ASC
LIMIT 20
")->fetchAll();

$topThree = array_slice($leaders, 0, 3);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
            <div>
                <div class="section-kicker">
                    <i class="bi bi-trophy-fill"></i>
                    Public Leaderboard
                </div>
                <h1 class="display-5 fw-bold mb-2">Top members across VIDURA</h1>
                <p class="text-muted mb-0 fs-5" style="max-width:760px;">
                    See who is leading the activity board across clubs, events, and participation points.
                </p>
            </div>

            <div class="points-hero">
                <div class="points-icon">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <div class="points-label">Members Ranked</div>
                    <div class="points-value"><?= count($leaders) ?></div>
                    <div class="small text-muted">Approved public leaderboard entries</div>
                </div>
            </div>
        </div>
    </div>

    <?php if (count($topThree) >= 3): ?>
        <div class="member-panel mb-4">
            <div class="member-panel__inner">
                <div class="section-title-row">
                    <div>
                        <div class="section-kicker">
                            <i class="bi bi-award-fill"></i>
                            Podium
                        </div>
                        <h3 class="mb-1">Top three performers</h3>
                        <p class="text-muted mb-0">The leading members based on points and level.</p>
                    </div>
                </div>

                <div class="row g-3 text-center podium-row">
                    <div class="col-md-4">
                        <div class="badge-item h-100 podium-side">
                            <div class="points-icon mx-auto mb-3" style="background:linear-gradient(135deg,#64748b,#94a3b8);">
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <h5 class="mb-1"><?= htmlspecialchars($topThree[1]['name']) ?></h5>
                            <p class="text-muted mb-0"><?= (int) $topThree[1]['points'] ?> points</p>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark border">2nd</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="badge-item h-100 shadow-sm podium-center" style="border-color:#fde68a;">
                            <div class="points-icon mx-auto mb-3" style="width:90px;height:90px;font-size:2rem;">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <h4 class="mb-1" style="font-size:1.75rem;"><?= htmlspecialchars($topThree[0]['name']) ?></h4>
                            <p class="text-muted mb-0 fs-5"><?= (int) $topThree[0]['points'] ?> points</p>
                            <div class="mt-3">
                                <span class="badge bg-warning text-dark">1st</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="badge-item h-100 podium-side">
                            <div class="points-icon mx-auto mb-3" style="background:linear-gradient(135deg,#a16207,#f59e0b);">
                                <i class="bi bi-bookmark-star-fill"></i>
                            </div>
                            <h5 class="mb-1"><?= htmlspecialchars($topThree[2]['name']) ?></h5>
                            <p class="text-muted mb-0"><?= (int) $topThree[2]['points'] ?> points</p>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark border">3rd</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="member-panel">
        <div class="member-panel__inner">
            <div class="section-title-row">
                <div>
                    <div class="section-kicker">
                        <i class="bi bi-list-ol"></i>
                        Full Ranking
                    </div>
                    <h3 class="mb-1">Leaderboard table</h3>
                    <p class="text-muted mb-0">A clean snapshot of the top 20 approved members.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                                <td colspan="5" class="text-center text-muted py-5">No public ranking data found.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($leaders as $index => $leader): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?php if ($index < 3): ?>
                                        <span class="star-badge">
                                            <i class="bi bi-star-fill star"></i>
                                            #<?= $index + 1 ?>
                                        </span>
                                    <?php else: ?>
                                        #<?= $index + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($leader['profile_photo'])): ?>
                                            <img
                                                src="../uploads/profiles/<?= htmlspecialchars($leader['profile_photo']) ?>"
                                                alt="<?= htmlspecialchars($leader['name']) ?>"
                                                style="width:52px;height:52px;border-radius:50%;object-fit:cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:linear-gradient(135deg,#16a34a,#0ea5e9);color:#fff;font-weight:800;">
                                                <?= strtoupper(substr($leader['name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="ms-3">
                                            <strong><?= htmlspecialchars($leader['name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($leader['roll_number']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background:<?= htmlspecialchars($leader['theme_color'] ?? '#16a34a') ?>">
                                        <?= htmlspecialchars($leader['club_name'] ?? '-') ?>
                                    </span>
                                </td>
                                <td><span class="badge bg-primary fs-6">Level <?= (int) $leader['level'] ?></span></td>
                                <td><strong class="text-success fs-5"><?= number_format((int) $leader['points']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
