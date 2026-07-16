<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/event_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$event = getEvent($pdo, $id);

if (!$event) {
    die("Event not found.");
}

$stmt = $pdo->prepare("
SELECT COUNT(*) FROM registrations WHERE event_id=?
");
$stmt->execute([$id]);
$registrations = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
SELECT name
FROM clubs
WHERE id=?
");
$stmt->execute([$event['club_id']]);
$clubName = $stmt->fetchColumn();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><?= htmlspecialchars($event['title']) ?></h2>
            <p class="text-muted mb-0">Event overview and registration stats.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="events.php" class="btn btn-secondary">Back</a>
            <a href="event_edit.php?id=<?= (int) $event['id'] ?>" class="btn btn-primary">Edit</a>
            <a
                href="../actions/event_delete.php?id=<?= (int) $event['id'] ?>"
                class="btn btn-danger"
                onclick="return confirm('Delete this event?');">
                Delete
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-admin">
                <?php if (!empty($event['banner'])): ?>
                    <img
                        src="../uploads/events/<?= htmlspecialchars($event['banner']) ?>"
                        alt="<?= htmlspecialchars($event['title']) ?>"
                        class="img-fluid rounded-4 mb-4">
                <?php endif; ?>

                <h5>Details</h5>
                <p class="text-muted"><?= nl2br(htmlspecialchars($event['description'] ?? '')) ?></p>

                <div class="row g-3">
                    <div class="col-md-4"><strong>Club:</strong><br><?= htmlspecialchars($clubName ?: '-') ?></div>
                    <div class="col-md-4"><strong>Date:</strong><br><?= !empty($event['event_date']) ? date('d M Y', strtotime($event['event_date'])) : '-' ?></div>
                    <div class="col-md-4"><strong>Status:</strong><br><?= htmlspecialchars($event['status'] ?? '-') ?></div>
                    <div class="col-md-4"><strong>Venue:</strong><br><?= htmlspecialchars($event['venue'] ?? '-') ?></div>
                    <div class="col-md-4"><strong>Points:</strong><br><?= (int) ($event['points'] ?? 0) ?></div>
                    <div class="col-md-4"><strong>Registrations:</strong><br><?= $registrations ?></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-admin mb-4">
                <h5 class="mb-3">Timing</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><strong>Registration Start:</strong><br><?= !empty($event['registration_start']) ? date('d M Y, h:i A', strtotime($event['registration_start'])) : '-' ?></li>
                    <li class="mb-2"><strong>Registration End:</strong><br><?= !empty($event['registration_end']) ? date('d M Y, h:i A', strtotime($event['registration_end'])) : '-' ?></li>
                    <li class="mb-2"><strong>Start Time:</strong><br><?= !empty($event['start_time']) ? date('h:i A', strtotime($event['start_time'])) : '-' ?></li>
                    <li><strong>End Time:</strong><br><?= !empty($event['end_time']) ? date('h:i A', strtotime($event['end_time'])) : '-' ?></li>
                </ul>
            </div>

            <div class="card-admin">
                <h5 class="mb-3">Registration Policy</h5>
                <p class="mb-2"><strong>Max Participants:</strong> <?= (int) ($event['max_participants'] ?? 0) ?></p>
                <p class="mb-2"><strong>Year Allowed:</strong> <?= htmlspecialchars($event['year_allowed'] ?? '-') ?></p>
                <p class="mb-2"><strong>Department:</strong> <?= htmlspecialchars($event['department_allowed'] ?? '-') ?></p>
                <p class="mb-0"><strong>FCFS:</strong> <?= !empty($event['first_come_first_serve']) ? 'Yes' : 'No' ?></p>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
