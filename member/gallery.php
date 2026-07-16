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
    events.title AS event_title,
    users.name AS uploaded_by_name
FROM gallery
LEFT JOIN events ON events.id = gallery.event_id
LEFT JOIN users ON users.id = gallery.uploaded_by
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
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$galleryItems = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Gallery</h2>
            <p class="text-muted mb-0">Highlights from VIDURA activities and events.</p>
        </div>
    </div>

    <div class="card-custom p-4 mb-4">
        <form method="GET">
            <div class="row g-3">
                <div class="col-md-10">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search gallery..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <?php if (empty($galleryItems)): ?>
            <div class="col-12">
                <div class="alert alert-info">No gallery images available yet.</div>
            </div>
        <?php endif; ?>

        <?php foreach ($galleryItems as $item): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card-custom h-100 overflow-hidden">
                    <img
                        src="../uploads/gallery/<?= htmlspecialchars($item['image']) ?>"
                        alt="<?= htmlspecialchars($item['title'] ?? 'Gallery image') ?>"
                        style="width:100%;height:240px;object-fit:cover;">
                    <div class="p-4">
                        <h5 class="mb-2"><?= htmlspecialchars($item['title'] ?? '') ?></h5>
                        <p class="text-muted mb-2"><?= htmlspecialchars($item['event_title'] ?? 'General gallery') ?></p>
                        <small class="text-muted d-block">
                            Uploaded by <?= htmlspecialchars($item['uploaded_by_name'] ?? 'System') ?>
                            <?php if (!empty($item['uploaded_at'])): ?>
                                on <?= date('d M Y', strtotime($item['uploaded_at'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
