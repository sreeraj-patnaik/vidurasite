<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET['search'] ?? '');

$sql = "
SELECT
    gallery.*,
    events.title AS event_title
FROM gallery
LEFT JOIN events ON events.id = gallery.event_id
WHERE 1=1
";

$params = [];

if ($search !== '') {
    $sql .= "
    AND (
        LOWER(gallery.title) LIKE LOWER(?)
        OR LOWER(events.title) LIKE LOWER(?)
    )
    ";
    $key = '%' . $search . '%';
    $params[] = $key;
    $params[] = $key;
}

$sql .= "
ORDER BY gallery.uploaded_at DESC
LIMIT 24
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$galleryItems = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="section-kicker">
            <i class="bi bi-images"></i>
            Member Gallery
        </div>
        <h1 class="display-5 fw-bold mb-2">Captured moments from VIDURA</h1>
        <p class="text-muted fs-5 mb-0" style="max-width:780px;">
            Explore event highlights, club memories, and student activity in a clean gallery view.
        </p>
    </div>

    <div class="card-custom p-4 mb-4">
        <form method="GET">
            <div class="row g-3">
                <div class="col-md-10">
                    <input
                        type="text"
                        name="search"
                        class="form-control glass-field"
                        placeholder="Search gallery..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary-custom">Search</button>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <?php if (empty($galleryItems)): ?>
            <div class="col-12">
                <div class="member-panel">
                    <div class="member-panel__inner text-center py-5">
                        <i class="bi bi-images fs-1 text-muted d-block mb-3"></i>
                        <h4 class="mb-2">No gallery images yet</h4>
                        <p class="text-muted mb-0">Photos will appear here when admins upload event highlights.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($galleryItems as $item): ?>
            <div class="col-lg-4 col-md-6">
                <div class="member-panel h-100 overflow-hidden">
                    <?php if (!empty($item['image'])): ?>
                        <img
                            src="../uploads/gallery/<?= htmlspecialchars($item['image']) ?>"
                            alt="<?= htmlspecialchars($item['title'] ?? 'Gallery image') ?>"
                            style="width:100%;height:230px;object-fit:cover;">
                    <?php endif; ?>
                    <div class="member-panel__inner">
                        <h5 class="mb-2"><?= htmlspecialchars($item['title'] ?? 'Gallery highlight') ?></h5>
                        <p class="text-muted mb-2"><?= htmlspecialchars($item['event_title'] ?? 'General gallery') ?></p>
                        <small class="text-muted">
                            <?= !empty($item['uploaded_at']) ? date('d M Y', strtotime($item['uploaded_at'])) : '' ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
