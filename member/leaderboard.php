<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$search = trim($_GET['search'] ?? '');
$club = $_GET['club'] ?? '';

$sql = "
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
";

$params = [];

if ($search !== '') {
    $sql .= "
    AND (
        LOWER(users.name) LIKE LOWER(?)
        OR LOWER(users.roll_number) LIKE LOWER(?)
    )
    ";
    $key = '%' . $search . '%';
    $params[] = $key;
    $params[] = $key;
}

if ($club !== '') {
    $sql .= " AND users.club_id=? ";
    $params[] = $club;
}

$sql .= "
ORDER BY users.points DESC, users.level DESC, users.name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leaders = $stmt->fetchAll();

$clubs = $pdo->query("
SELECT *
FROM clubs
ORDER BY name
")->fetchAll();

$topThree = array_slice($leaders, 0, 3);

$currentUserRank = 0;
foreach ($leaders as $index => $leader) {
    if ((int) $leader['id'] === $userId) {
        $currentUserRank = $index + 1;
        break;
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
            <div>
                <div class="section-kicker">
                    <i class="bi bi-trophy-fill"></i>
                    VIDURA Leaderboard
                </div>
                <h1 class="display-5 fw-bold mb-2">Rank up through activity</h1>
                <p class="text-muted mb-0 fs-5" style="max-width:720px;">
                    A clean view of the top performers across VIDURA. Track your standing and compare growth at a glance.
                </p>
            </div>

            <div class="points-hero">
                <div class="points-icon">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <div class="points-label">Your Rank</div>
                    <div class="points-value"><?= $currentUserRank > 0 ? '#' . $currentUserRank : '-' ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($_SESSION['name']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-custom p-4 mb-4">
        <form method="GET">
            <div class="row g-3">
                <div class="col-md-8">
                    <input
                        type="text"
                        name="search"
                        class="form-control glass-field"
                        placeholder="Search student..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="col-md-2">
                    <select name="club" class="form-select glass-field">
                        <option value="">All Clubs</option>
                        <?php foreach ($clubs as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= $club == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary-custom">Search</button>
                </div>
            </div>
        </form>
    </div>

    <div class="metric-grid mb-4">
        <div class="metric-card">
            <div class="label">Members</div>
            <div class="value"><?= count($leaders) ?></div>
            <div class="text-muted small">Approved participants</div>
        </div>
        <div class="metric-card">
            <div class="label">Your Rank</div>
            <div class="value">#<?= $currentUserRank > 0 ? $currentUserRank : '-' ?></div>
            <div class="text-muted small">Current leaderboard position</div>
        </div>
        <div class="metric-card">
            <div class="label">Top Score</div>
            <div class="value"><?= (int) ($leaders[0]['points'] ?? 0) ?></div>
            <div class="text-muted small">Highest points this term</div>
        </div>
        <div class="metric-card">
            <div class="label">Current User</div>
            <div class="value mono-code" style="font-size:1.2rem;"><?= htmlspecialchars($_SESSION['name']) ?></div>
            <div class="text-muted small">Signed in member</div>
        </div>
    </div>

    <?php if (count($topThree) >= 3): ?>
        <div class="member-panel mb-4">
            <div class="member-panel__inner">
                <div class="section-title-row">
                    <div>
                        <div class="section-kicker">
                            <i class="bi bi-award-fill"></i>
                            Top Performers
                        </div>
                        <h3 class="mb-1">Podium</h3>
                        <p class="text-muted mb-0">The current top three ranked members.</p>
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
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="badge-item h-100 shadow-sm podium-center" style="border-color:#fde68a;">
                            <div class="points-icon mx-auto mb-3" style="width:90px;height:90px;font-size:2rem;">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <h4 class="mb-1" style="font-size:1.75rem;"><?= htmlspecialchars($topThree[0]['name']) ?></h4>
                            <p class="text-muted mb-0 fs-5"><?= (int) $topThree[0]['points'] ?> points</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="badge-item h-100 podium-side">
                            <div class="points-icon mx-auto mb-3" style="background:linear-gradient(135deg,#a16207,#f59e0b);">
                                <i class="bi bi-bookmark-star-fill"></i>
                            </div>
                            <h5 class="mb-1"><?= htmlspecialchars($topThree[2]['name']) ?></h5>
                            <p class="text-muted mb-0"><?= (int) $topThree[2]['points'] ?> points</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="member-panel mb-4">
        <div class="member-panel__inner">
            <div class="section-title-row">
                <div>
                    <div class="section-kicker">
                        <i class="bi bi-list-ol"></i>
                        Full Ranking
                    </div>
                    <h3 class="mb-1">Leaderboard table</h3>
                    <p class="text-muted mb-0">Find your position and compare totals across clubs.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student</th>
                            <th>Club</th>
                            <th>Level</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaders)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">No leaderboard results found.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($leaders as $index => $leader): ?>
                            <?php $rank = $index + 1; $isCurrentUser = ((int) $leader['id'] === $userId); ?>
                            <tr <?= $isCurrentUser ? 'class="table-primary"' : '' ?>>
                                <td class="fw-bold">
                                    <?php if ($rank <= 3): ?>
                                        <span class="star-badge">
                                            <i class="bi bi-star-fill star"></i>
                                            #<?= $rank ?>
                                        </span>
                                    <?php else: ?>
                                        #<?= $rank ?>
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
                                            <strong><?= htmlspecialchars($leader['name']) ?></strong>
                                            <?php if ($isCurrentUser): ?>
                                                <span class="badge bg-success ms-2">You</span>
                                            <?php endif; ?>
                                            <br>
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

    <div class="member-panel">
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
                <div class="col-md-3 col-6">
                    <a href="dashboard.php" class="btn btn-primary-custom w-100 py-4 h-100">
                        <i class="bi bi-speedometer2 d-block fs-4 mb-2"></i>
                        Dashboard
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="events.php" class="btn btn-outline-custom w-100 py-4 h-100">
                        <i class="bi bi-calendar-event d-block fs-4 mb-2"></i>
                        Events
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="badges.php" class="btn btn-outline-custom w-100 py-4 h-100">
                        <i class="bi bi-award-fill d-block fs-4 mb-2"></i>
                        Badges
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="profile.php" class="btn btn-primary-custom w-100 py-4 h-100">
                        <i class="bi bi-person-circle d-block fs-4 mb-2"></i>
                        Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
