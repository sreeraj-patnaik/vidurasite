<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET['search'] ?? '');

$sql = "
SELECT *
FROM announcements
WHERE 1=1
";

$params = [];

if ($search !== '') {
    $sql .= "
    AND (
        LOWER(title) LIKE LOWER(?)
        OR LOWER(description) LIKE LOWER(?)
    )
    ";

    $key = '%' . $search . '%';
    $params[] = $key;
    $params[] = $key;
}

$sql .= "
ORDER BY created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$announcements = $stmt->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Announcements</h2>
            <p class="text-muted mb-0">Publish notices for all VIDURA members.</p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addAnnouncement">
            <i class="bi bi-plus-circle"></i> New Announcement
        </button>
    </div>

    <div class="card-admin mb-4">
        <form method="GET">
            <div class="row g-3">
                <div class="col-md-10">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search announcements..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-admin">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="80">#</th>
                        <th>Title</th>
                        <th>Expires At</th>
                        <th>Created</th>
                        <th width="220">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($announcements)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">No announcements available.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($announcements as $a): ?>
                        <tr>
                            <td><?= (int) $a['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($a['title'] ?? '') ?></strong><br>
                                <small class="text-muted">
                                    <?= htmlspecialchars(substr(strip_tags($a['description'] ?? ''), 0, 100)) ?>
                                    <?= strlen(strip_tags($a['description'] ?? '')) > 100 ? '...' : '' ?>
                                </small>
                            </td>
                            <td>
                                <?php if (!empty($a['expires_at'])): ?>
                                    <?= date('d M Y, h:i A', strtotime($a['expires_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">No expiry</span>
                                <?php endif; ?>
                            </td>
                            <td><?= !empty($a['created_at']) ? date('d M Y', strtotime($a['created_at'])) : '-' ?></td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editAnnouncement<?= (int) $a['id'] ?>">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>

                                <a
                                    href="../actions/announcement_delete.php?id=<?= (int) $a['id'] ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this announcement?');">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php foreach ($announcements as $a): ?>
        <div class="modal fade" id="editAnnouncement<?= (int) $a['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="../actions/announcement_update.php">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Announcement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input
                                    type="text"
                                    name="title"
                                    class="form-control"
                                    value="<?= htmlspecialchars($a['title'] ?? '') ?>"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea
                                    name="description"
                                    class="form-control"
                                    rows="6"
                                    required><?= htmlspecialchars($a['description'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Expires At</label>
                                <input
                                    type="datetime-local"
                                    name="expires_at"
                                    class="form-control"
                                    value="<?= !empty($a['expires_at']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($a['expires_at']))) : '' ?>">
                                <small class="text-muted">Leave blank if the announcement should not expire.</small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="modal fade" id="addAnnouncement" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="../actions/announcement_save.php">
                    <div class="modal-header">
                        <h5 class="modal-title">New Announcement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="7" class="form-control" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Expires At</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                            <small class="text-muted">Leave blank if the announcement should not expire.</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Publish</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
