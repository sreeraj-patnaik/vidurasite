<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/settings_functions.php';

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

$teamRoles = getOrganizingTeamRoleLabels($pdo, $userId);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-lg-center">
            <div>
                <span class="id-badge mb-3">
                    <i class="bi bi-person-gear"></i>
                    Member Profile
                </span>
                <h1 class="display-6 fw-bold mb-2">Edit your VIDURA profile</h1>
                <p class="text-muted mb-0">
                    Keep your phone number, bio, password, and profile photo up to date.
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-custom">Dashboard</a>
                <a href="idcard.php" class="btn btn-primary-custom">Digital ID</a>
            </div>
        </div>
    </div>

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

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="section-shell p-4 h-100">
                <div class="text-center">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img
                            src="../uploads/profiles/<?= htmlspecialchars($user['profile_photo']) ?>"
                            alt="<?= htmlspecialchars($user['name']) ?>"
                            class="rounded-circle mb-3"
                            style="width:128px;height:128px;object-fit:cover;">
                    <?php else: ?>
                        <div
                            class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width:128px;height:128px;background:linear-gradient(135deg,#16a34a,#0ea5e9);color:#fff;font-size:54px;font-weight:800;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>

                    <h3 class="mb-1"><?= htmlspecialchars($user['name']) ?></h3>
                    <p class="text-muted mb-3"><?= htmlspecialchars($user['club_name'] ?? 'No Club') ?></p>
                    <div class="badge badge-soft mb-3"><?= htmlspecialchars($user['roll_number']) ?></div>
                    <?php if (!empty($teamRoles)): ?>
                        <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                            <?php foreach ($teamRoles as $roleLabel): ?>
                                <span class="badge bg-primary-subtle text-primary border"><?= htmlspecialchars($roleLabel) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="panel-stat text-center">
                            <div class="mono-code fw-bold"><?= (int) $user['points'] ?></div>
                            <div class="small text-muted">Points</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="panel-stat text-center">
                            <div class="mono-code fw-bold"><?= (int) $user['level'] ?></div>
                            <div class="small text-muted">Level</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="panel-stat">
                            <div class="small text-muted mb-1">Member Since</div>
                            <div class="fw-semibold">
                                <?= !empty($user['joined_at']) ? date('d M Y', strtotime($user['joined_at'])) : '-' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="small text-muted mb-2">Bio</div>
                    <div class="text-body-secondary">
                        <?= htmlspecialchars($user['bio'] ?: 'No bio added yet.') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="section-shell p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-1">Profile Settings</h3>
                        <p class="text-muted mb-0">Update your visible information and keep your account secure.</p>
                    </div>
                </div>

                <form action="../actions/profile_update.php" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control glass-field" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control glass-field" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control glass-field" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Roll Number</label>
                            <input type="text" class="form-control glass-field" value="<?= htmlspecialchars($user['roll_number']) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control glass-field" value="<?= htmlspecialchars($user['department'] ?? '') ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Club</label>
                            <input type="text" class="form-control glass-field" value="<?= htmlspecialchars($user['club_name'] ?? 'No Club') ?>" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" rows="5" class="form-control glass-field"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="profile_photo" class="form-control glass-field" accept="image/*">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control glass-field" placeholder="Leave blank to keep current password">
                        </div>

                        <div class="col-12">
                            <hr class="my-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="dashboard.php" class="btn btn-outline-custom">Cancel</a>
                                <button type="submit" class="btn btn-primary-custom">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
