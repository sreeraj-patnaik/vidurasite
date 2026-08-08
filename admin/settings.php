<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/settings_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$settings = getSiteSettings($pdo);

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2>Settings</h2>
            <p class="text-muted mb-0">Update platform details and replace the homepage/logo images.</p>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="../actions/settings_save.php" method="POST" enctype="multipart/form-data">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card-admin h-100">
                    <h5 class="mb-3">Platform Details</h5>

                    <div class="mb-3">
                        <label class="form-label">Website Title</label>
                        <input type="text" name="website_title" class="form-control" value="<?= htmlspecialchars($settings['website_title'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Membership Fee</label>
                        <input type="number" name="membership_fee" class="form-control" value="<?= htmlspecialchars($settings['membership_fee'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <input type="text" name="semester" class="form-control" value="<?= htmlspecialchars($settings['semester'] ?? '') ?>">
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card-admin h-100">
                    <h5 class="mb-3">Upload Images</h5>
                    <p class="text-muted small mb-4">Leave a file empty to keep the current image.</p>

                    <div class="row g-3">
                        <?php
                        $imageFields = [
                            'homepage_banner' => ['Homepage Banner', 'assets/images/hero.png'],
                            'techkruti_image' => ['TechKruti Image', 'assets/images/techkruti.png'],
                            'khelkruti_image' => ['KhelKruti Image', 'assets/images/khelkruti.png'],
                            'samskruti_image' => ['SamsKruti Image', 'assets/images/samskruti.png'],
                            'liet_logo' => ['LIET Logo', 'assets/images/liet_logo.png'],
                            'vidura_logo' => ['VIDURA Logo', 'assets/images/vidura_logo.png'],
                        ];
                        ?>

                        <?php foreach ($imageFields as $field => [$label, $fallback]): ?>
                            <div class="col-md-6">
                                <label class="form-label"><?= htmlspecialchars($label) ?></label>
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <div class="mb-3 text-center">
                                        <img
                                            src="<?= htmlspecialchars(settingsImageUrl($settings, $field, $fallback)) ?>"
                                            alt="<?= htmlspecialchars($label) ?>"
                                            class="img-fluid rounded"
                                            style="max-height: 140px; object-fit: contain;">
                                    </div>
                                    <input type="file" name="<?= htmlspecialchars($field) ?>" class="form-control" accept="image/*">
                                    <small class="text-muted d-block mt-2">Recommended for <?= htmlspecialchars($label) ?>.</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Settings
                </button>
            </div>
        </div>
    </form>
</div>
</div>

<?php include 'partials/footer.php'; ?>
