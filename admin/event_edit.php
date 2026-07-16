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

$clubs = $pdo->query("
SELECT *
FROM clubs
ORDER BY name
")->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Edit Event</h2>
            <p class="text-muted mb-0">Update event details and registration settings.</p>
        </div>

        <a href="event_view.php?id=<?= (int) $event['id'] ?>" class="btn btn-secondary">Back</a>
    </div>

    <div class="card-admin">
        <form action="../actions/event_update.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Event Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($event['title'] ?? '') ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Club</label>
                    <select name="club_id" class="form-select" required>
                        <option value="">Select Club</option>
                        <?php foreach ($clubs as $club): ?>
                            <option value="<?= (int) $club['id'] ?>" <?= (int) $event['club_id'] === (int) $club['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($club['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Venue</label>
                    <input type="text" name="venue" class="form-control" value="<?= htmlspecialchars($event['venue'] ?? '') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Banner Image</label>
                    <input type="file" name="banner" class="form-control" accept="image/*">
                    <?php if (!empty($event['banner'])): ?>
                        <small class="text-muted">Current banner: <?= htmlspecialchars($event['banner']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Event Date</label>
                    <input type="date" name="event_date" class="form-control" value="<?= !empty($event['event_date']) ? htmlspecialchars(date('Y-m-d', strtotime($event['event_date']))) : '' ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time" class="form-control" value="<?= !empty($event['start_time']) ? htmlspecialchars(date('H:i', strtotime($event['start_time']))) : '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" class="form-control" value="<?= !empty($event['end_time']) ? htmlspecialchars(date('H:i', strtotime($event['end_time']))) : '' ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Registration Start</label>
                    <input type="datetime-local" name="registration_start" class="form-control" value="<?= !empty($event['registration_start']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($event['registration_start']))) : '' ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Registration End</label>
                    <input type="datetime-local" name="registration_end" class="form-control" value="<?= !empty($event['registration_end']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($event['registration_end']))) : '' ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Maximum Participants</label>
                    <input type="number" name="max_participants" class="form-control" min="1" value="<?= (int) ($event['max_participants'] ?? 100) ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Allowed Year</label>
                    <select name="year_allowed" class="form-select">
                        <?php $years = ['All', '1', '2', '3', '4']; ?>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= htmlspecialchars($year) ?>" <?= ($event['year_allowed'] ?? '') === $year ? 'selected' : '' ?>>
                                <?= $year === 'All' ? 'All Years' : $year . ' Year' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Department</label>
                    <input type="text" name="department_allowed" class="form-control" value="<?= htmlspecialchars($event['department_allowed'] ?? '') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Participation Points</label>
                    <input type="number" name="points" class="form-control" min="0" value="<?= (int) ($event['points'] ?? 0) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['Upcoming', 'Ongoing', 'Completed', 'Cancelled'] as $status): ?>
                            <option value="<?= $status ?>" <?= ($event['status'] ?? '') === $status ? 'selected' : '' ?>>
                                <?= $status ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="form-check form-switch mt-4">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="first_come_first_serve"
                            value="1"
                            <?= !empty($event['first_come_first_serve']) ? 'checked' : '' ?>>
                        <label class="form-check-label">First Come First Serve Registration</label>
                    </div>
                </div>

                <div class="col-12">
                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="events.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Event</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
