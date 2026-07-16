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

$stmt = $pdo->prepare("
SELECT points
FROM users
WHERE id=?
LIMIT 1
");
$stmt->execute([$userId]);
$userPoints = (int) ($stmt->fetchColumn() ?: 0);

$sql = "
SELECT
    events.*,
    clubs.name AS club_name,
    clubs.theme_color,
    (
        SELECT COUNT(*)
        FROM registrations
        WHERE registrations.event_id = events.id
    ) AS registered,
    EXISTS(
        SELECT 1
        FROM registrations
        WHERE registrations.event_id = events.id
        AND registrations.user_id = ?
    ) AS already_registered
FROM events
LEFT JOIN clubs ON clubs.id = events.club_id
WHERE events.status='Upcoming'
";

$params = [$userId];

if ($search !== '') {
    $sql .= "
    AND (
        LOWER(events.title) LIKE LOWER(?)
        OR LOWER(events.venue) LIKE LOWER(?)
    )
    ";
    $key = '%' . $search . '%';
    $params[] = $key;
    $params[] = $key;
}

if ($club !== '') {
    $sql .= " AND events.club_id=? ";
    $params[] = $club;
}

$sql .= " ORDER BY events.event_date ASC ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

$clubs = $pdo->query("
SELECT *
FROM clubs
ORDER BY name
")->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-lg-center">
            <div>
                <span class="id-badge mb-3">
                    <i class="bi bi-calendar-event-fill"></i>
                    Event Hub
                </span>
                <h1 class="display-6 fw-bold mb-2">Upcoming VIDURA events</h1>
                <p class="text-muted mb-0">Register, participate, and earn points through active involvement.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-custom">Dashboard</a>
                <a href="leaderboard.php" class="btn btn-primary-custom">Leaderboard</a>
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
                        placeholder="Search events..."
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

    <div class="row g-4">
        <?php if (empty($events)): ?>
            <div class="col-12">
                <div class="alert alert-info">No upcoming events found.</div>
            </div>
        <?php endif; ?>

        <?php foreach ($events as $event): ?>
            <div class="col-lg-4 col-md-6">
                <div class="section-shell h-100 overflow-hidden">
                    <?php if (!empty($event['banner'])): ?>
                        <img
                            src="../uploads/events/<?= htmlspecialchars($event['banner']) ?>"
                            alt="<?= htmlspecialchars($event['title']) ?>"
                            style="width:100%;height:220px;object-fit:cover;">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center" style="height:220px;background:linear-gradient(135deg,#e2e8f0,#f8fafc);">
                            <i class="bi bi-calendar2-event text-success" style="font-size:3.5rem;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="p-4">
                        <span class="badge mb-3" style="background:<?= htmlspecialchars($event['theme_color'] ?? '#16a34a') ?>">
                            <?= htmlspecialchars($event['club_name'] ?? 'General') ?>
                        </span>

                        <h4 class="mb-2"><?= htmlspecialchars($event['title']) ?></h4>
                        <p class="text-muted mb-3">
                            <?= htmlspecialchars(substr(strip_tags($event['description'] ?? ''), 0, 120)) ?>
                            <?= strlen(strip_tags($event['description'] ?? '')) > 120 ? '...' : '' ?>
                        </p>

                        <div class="d-grid gap-2">
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2"><i class="bi bi-calendar-event text-success me-2"></i><?= !empty($event['event_date']) ? date('d M Y', strtotime($event['event_date'])) : '-' ?></li>
                                <li class="mb-2"><i class="bi bi-clock text-primary me-2"></i><?= !empty($event['start_time']) ? date('h:i A', strtotime($event['start_time'])) : '-' ?><?= !empty($event['end_time']) ? ' - ' . date('h:i A', strtotime($event['end_time'])) : '' ?></li>
                                <li class="mb-2"><i class="bi bi-geo-alt-fill text-danger me-2"></i><?= htmlspecialchars($event['venue'] ?? '-') ?></li>
                                <li class="mb-2"><i class="bi bi-people-fill text-warning me-2"></i><?= (int) $event['registered'] ?> / <?= (int) $event['max_participants'] ?> registered</li>
                                <li><i class="bi bi-star-fill text-warning me-2"></i><?= (int) $event['points'] ?> participation points</li>
                            </ul>

                            <div class="progress" style="height:8px;">
                                <?php
                                $percent = 0;
                                if ((int) $event['max_participants'] > 0) {
                                    $percent = min(100, ((int) $event['registered'] / (int) $event['max_participants']) * 100);
                                }
                                ?>
                                <div class="progress-bar bg-success" style="width:<?= $percent ?>%"></div>
                            </div>

                            <a href="event_view.php?id=<?= (int) $event['id'] ?>" class="btn btn-outline-custom">View Details</a>

                            <?php
                            $registrationOpen =
                                strtotime(date('Y-m-d H:i:s')) >= strtotime($event['registration_start']) &&
                                strtotime(date('Y-m-d H:i:s')) <= strtotime($event['registration_end']);
                            $isFull = (int) $event['registered'] >= (int) $event['max_participants'];
                            ?>

                            <?php if ($event['already_registered']): ?>
                                <button class="btn btn-success" disabled>Already Registered</button>
                            <?php elseif (!$registrationOpen): ?>
                                <button class="btn btn-secondary" disabled>Registration Closed</button>
                            <?php elseif ($isFull): ?>
                                <button class="btn btn-danger" disabled>Event Full</button>
                            <?php else: ?>
                                <a
                                    href="../actions/event_register.php?id=<?= (int) $event['id'] ?>"
                                    class="btn btn-primary-custom"
                                    onclick="return confirm('Register for this event?');">
                                    Register Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-md-4">
            <div class="metric-card">
                <div class="label">Available Events</div>
                <div class="value"><?= count($events) ?></div>
                <div class="text-muted small">Current filtered list</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="metric-card">
                <div class="label">Registered Events</div>
                <div class="value">
                    <?php
                    $registeredCount = 0;
                    foreach ($events as $e) {
                        if (!empty($e['already_registered'])) {
                            $registeredCount++;
                        }
                    }
                    echo $registeredCount;
                    ?>
                </div>
                <div class="text-muted small">Events you are in</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="metric-card">
                <div class="label">Current Points</div>
                <div class="value"><?= $userPoints ?></div>
                <div class="text-muted small">Profile points total</div>
            </div>
        </div>
    </div>

    <div class="section-shell p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Quick Actions</h4>
            <span class="text-muted small">Student workspace</span>
        </div>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <a href="dashboard.php" class="btn btn-primary-custom w-100 py-3">
                    <i class="bi bi-speedometer2 d-block fs-4 mb-2"></i>
                    Dashboard
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="leaderboard.php" class="btn btn-outline-custom w-100 py-3">
                    <i class="bi bi-trophy-fill d-block fs-4 mb-2"></i>
                    Leaderboard
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="badges.php" class="btn btn-outline-custom w-100 py-3">
                    <i class="bi bi-award-fill d-block fs-4 mb-2"></i>
                    Badges
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="profile.php" class="btn btn-primary-custom w-100 py-3">
                    <i class="bi bi-person-circle d-block fs-4 mb-2"></i>
                    Profile
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
