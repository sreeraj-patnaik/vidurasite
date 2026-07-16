<?php
require_once '../config/config.php';
require_once '../config/database.php';

$events = $pdo->query("
SELECT
    events.*,
    clubs.name AS club_name,
    clubs.theme_color
FROM events
LEFT JOIN clubs ON clubs.id = events.club_id
ORDER BY event_date DESC
")->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="section-kicker"><i class="bi bi-calendar-event-fill"></i> Events</div>
        <h1 class="display-5 fw-bold mb-2">Public event board</h1>
        <p class="text-muted fs-5 mb-0">Browse the latest VIDURA activities, club events, and upcoming programs.</p>
    </div>

    <div class="row g-4">
        <?php if (empty($events)): ?>
            <div class="col-12"><div class="alert alert-info">No events available right now.</div></div>
        <?php endif; ?>

        <?php foreach ($events as $event): ?>
            <div class="col-lg-4 col-md-6">
                <div class="member-panel h-100">
                    <?php if (!empty($event['banner'])): ?>
                        <img src="../uploads/events/<?= htmlspecialchars($event['banner']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" style="width:100%;height:220px;object-fit:cover;">
                    <?php else: ?>
                        <div style="height:220px;background:linear-gradient(135deg,#e2e8f0,#f8fafc);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-calendar2-event text-success" style="font-size:3.5rem;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="member-panel__inner">
                        <span class="badge mb-3" style="background:<?= htmlspecialchars($event['theme_color'] ?? '#16a34a') ?>;"><?= htmlspecialchars($event['club_name'] ?? 'General') ?></span>
                        <h4 class="mb-2"><?= htmlspecialchars($event['title']) ?></h4>
                        <p class="text-muted mb-3"><?= htmlspecialchars(substr(strip_tags($event['description'] ?? ''), 0, 120)) ?><?= strlen(strip_tags($event['description'] ?? '')) > 120 ? '...' : '' ?></p>
                        <div class="d-flex justify-content-between small text-muted">
                            <span><?= !empty($event['event_date']) ? date('d M Y', strtotime($event['event_date'])) : '-' ?></span>
                            <span><?= !empty($event['venue']) ? htmlspecialchars($event['venue']) : '-' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
