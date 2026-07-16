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

$seed = sha1(($user['roll_number'] ?? $user['id']) . 'VIDURA');
$cells = [];

for ($i = 0; $i < 64; $i++) {
    $cells[] = hexdec($seed[$i % strlen($seed)]) % 2 === 0 ? 1 : 0;
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-lg-center">
            <div>
                <span class="id-badge mb-3">
                    <i class="bi bi-person-badge-fill"></i>
                    VIDURA Digital ID
                </span>
                <h1 class="display-6 fw-bold mb-2"><?= htmlspecialchars($user['name']) ?></h1>
                <p class="text-muted mb-0">
                    Official member identity for the Department of Computer Science & Systems Engineering.
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="profile.php" class="btn btn-outline-custom">Edit Profile</a>
                <a href="dashboard.php" class="btn btn-primary-custom">Dashboard</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="id-card h-100">
                <div class="id-card__top d-flex justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <div class="id-badge mb-3">
                            <i class="bi bi-shield-check"></i>
                            Verified Member
                        </div>
                        <h2 class="mb-1"><?= htmlspecialchars($user['name']) ?></h2>
                        <p class="mb-0 text-white-50"><?= htmlspecialchars($user['club_name'] ?? 'No Club Assigned') ?></p>
                    </div>

                    <div class="text-end">
                        <div class="mono-code small text-white-50">ID #<?= (int) $user['id'] ?></div>
                        <div class="mono-code text-white"><?= htmlspecialchars($user['roll_number']) ?></div>
                    </div>
                </div>

                <div class="row align-items-center g-4 id-card__meta">
                    <div class="col-md-4 text-center">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img
                                src="../uploads/profiles/<?= htmlspecialchars($user['profile_photo']) ?>"
                                alt="<?= htmlspecialchars($user['name']) ?>"
                                class="id-avatar">
                        <?php else: ?>
                            <div class="id-avatar d-flex align-items-center justify-content-center bg-white text-dark mx-auto">
                                <span class="display-5 fw-bold"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="id-badge w-100 justify-content-between">
                                    <span>Level</span>
                                    <strong><?= (int) $user['level'] ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="id-badge w-100 justify-content-between">
                                    <span>Points</span>
                                    <strong><?= (int) $user['points'] ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="id-badge w-100 justify-content-between">
                                    <span>Year</span>
                                    <strong><?= htmlspecialchars($user['year'] ?: '-') ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="id-badge w-100 justify-content-between">
                                    <span>Status</span>
                                    <strong><?= htmlspecialchars(ucfirst($user['status'] ?? '-')) ?></strong>
                                </div>
                            </div>
                        </div>

                        <p class="mt-4 mb-0 text-white-50">
                            <?= htmlspecialchars($user['department'] ?: 'Department details unavailable') ?>
                            <?php if (!empty($user['section'])): ?>
                                <br>Section: <?= htmlspecialchars($user['section']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-end mt-4 id-card__footer">
                    <div>
                        <div class="mono-code text-white-50 small">Joined <?= !empty($user['joined_at']) ? date('d M Y', strtotime($user['joined_at'])) : '-' ?></div>
                        <div class="mono-code text-white-50 small">VIDURA Activity Clubs</div>
                    </div>

                    <div class="id-code shadow-sm">
                        <div class="mono-code fw-semibold mb-2">Member Scan Block</div>
                        <div class="code-grid">
                            <?php foreach ($cells as $index => $cell): ?>
                                <div class="code-cell <?= $cell ? 'active' : '' ?>"></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="section-shell p-4 h-100">
                <h4 class="mb-3">Card Details</h4>
                <div class="timeline-item">
                    <div class="fw-semibold">Roll Number</div>
                    <div class="text-muted"><?= htmlspecialchars($user['roll_number']) ?></div>
                </div>
                <div class="timeline-item">
                    <div class="fw-semibold">Email</div>
                    <div class="text-muted"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="timeline-item">
                    <div class="fw-semibold">Phone</div>
                    <div class="text-muted"><?= htmlspecialchars($user['phone'] ?: '-') ?></div>
                </div>
                <div class="timeline-item">
                    <div class="fw-semibold">Club</div>
                    <div class="text-muted"><?= htmlspecialchars($user['club_name'] ?? 'No Club Assigned') ?></div>
                </div>
                <div class="timeline-item">
                    <div class="fw-semibold">Bio</div>
                    <div class="text-muted"><?= htmlspecialchars($user['bio'] ?: 'No bio added yet.') ?></div>
                </div>

                <div class="panel-stat mt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Digital ID Status</div>
                            <h5 class="mb-0">Active</h5>
                        </div>
                        <span class="badge badge-soft">Verified</span>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="profile.php" class="btn btn-primary-custom">Update Profile</a>
                    <a href="dashboard.php" class="btn btn-outline-custom">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
