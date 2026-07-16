<?php
require_once '../config/config.php';
require_once '../config/database.php';

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
        <div class="section-kicker"><i class="bi bi-diagram-3-fill"></i> Clubs</div>
        <h1 class="display-5 fw-bold mb-2">Explore the active student clubs</h1>
        <p class="text-muted fs-5 mb-0">Every club page is connected to the same member system and event pipeline.</p>
    </div>

    <div class="row g-4">
        <?php foreach ($clubs as $club): ?>
            <div class="col-lg-4 col-md-6">
                <div class="member-panel h-100">
                    <div class="member-panel__inner">
                        <div class="points-hero mb-3">
                            <div class="points-icon" style="background:<?= htmlspecialchars($club['theme_color'] ?? '#16a34a') ?>;">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <div class="points-label">Club</div>
                                <div class="points-value" style="font-size:1.7rem;"><?= htmlspecialchars($club['name']) ?></div>
                            </div>
                        </div>
                        <p class="text-muted"><?= htmlspecialchars($club['description'] ?? 'No description available.') ?></p>
                        <a href="<?= BASE_URL ?>/public/<?= strtolower($club['name']) ?>.php" class="btn btn-outline-custom">Open Club Page</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
