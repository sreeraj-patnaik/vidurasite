<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET['search'] ?? '');

$events = $pdo->query("
SELECT id, title
FROM events
ORDER BY event_date DESC, title ASC
")->fetchAll();

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

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Gallery</h2>
            <p class="text-muted mb-0">Upload and manage event media for the VIDURA gallery.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card-admin h-100">
                <h5 class="mb-3">Upload Image</h5>

                <form method="POST" action="../actions/gallery_save.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Event</label>
                        <select name="event_id" class="form-select">
                            <option value="0">No event</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?= (int) $event['id'] ?>">
                                    <?= htmlspecialchars($event['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-admin h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Gallery Items</h5>
                    <form method="GET" class="d-flex gap-2">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search gallery..."
                            value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-outline-primary">Search</button>
                    </form>
                </div>

                <div class="row g-3">
                    <?php if (empty($galleryItems)): ?>
                        <div class="col-12">
                            <div class="alert alert-info mb-0">No gallery images found.</div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($galleryItems as $item): ?>
                        <div class="col-md-6">
                            <div class="border rounded-4 overflow-hidden h-100">
                                <?php if (!empty($item['image'])): ?>
                                    <img
                                        src="../uploads/gallery/<?= htmlspecialchars($item['image']) ?>"
                                        alt="<?= htmlspecialchars($item['title'] ?? 'Gallery image') ?>"
                                        style="width:100%;height:220px;object-fit:cover;">
                                <?php endif; ?>

                                <div class="p-3">
                                    <h6 class="mb-1"><?= htmlspecialchars($item['title'] ?? '') ?></h6>
                                    <p class="text-muted mb-2">
                                        <?= htmlspecialchars($item['event_title'] ?? 'General gallery') ?>
                                    </p>
                                    <small class="text-muted d-block mb-3">
                                        Uploaded by <?= htmlspecialchars($item['uploaded_by_name'] ?? 'System') ?>
                                        <?php if (!empty($item['uploaded_at'])): ?>
                                            on <?= date('d M Y', strtotime($item['uploaded_at'])) ?>
                                        <?php endif; ?>
                                    </small>

                                    <a
                                        href="../actions/gallery_delete.php?id=<?= (int) $item['id'] ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this gallery image?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
