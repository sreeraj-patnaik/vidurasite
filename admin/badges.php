<?php

require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
$selectedUser = intval($_GET['user'] ?? 0);

$sql = "
SELECT *
FROM badges
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

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$badges = $stmt->fetchAll();

$members = $pdo->query("
SELECT id, name, roll_number
FROM users
WHERE role='member'
ORDER BY name ASC
")->fetchAll();

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Badge Management</h2>
            <p class="text-muted mb-0">Manage all achievement badges.</p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addBadge">
            <i class="bi bi-plus-circle"></i> New Badge
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
                        placeholder="Search badges..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-admin mb-4">
        <h5 class="mb-3">Assign Badge</h5>
        <form method="POST" action="../actions/badge_award.php">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Member</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Select member</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= (int) $member['id'] ?>" <?= $selectedUser === (int) $member['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($member['name']) ?> (<?= htmlspecialchars($member['roll_number']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Badge</label>
                    <select name="badge_id" class="form-select" required>
                        <option value="">Select badge</option>
                        <?php foreach ($badges as $badge): ?>
                            <option value="<?= (int) $badge['id'] ?>">
                                <?= htmlspecialchars($badge['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-success">Assign</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-admin">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="80">Icon</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th width="90">Color</th>
                        <th width="160">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($badges)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">No badges found.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($badges as $badge): ?>
                        <tr>
                            <td>
                                <?php if (!empty($badge['icon'])): ?>
                                    <img
                                        src="../uploads/badges/<?= htmlspecialchars($badge['icon']) ?>"
                                        alt="<?= htmlspecialchars($badge['title'] ?? 'Badge') ?>"
                                        style="width:52px;height:52px;object-fit:contain;">
                                <?php else: ?>
                                    <i class="bi bi-trophy-fill fs-2 text-warning"></i>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($badge['title'] ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($badge['description'] ?? '') ?></td>
                            <td>
                                <div
                                    style="
                                        width:28px;
                                        height:28px;
                                        border-radius:50%;
                                        background:<?= htmlspecialchars($badge['color'] ?? '#0d6efd') ?>;
                                        border:2px solid #ddd;
                                        margin:auto;">
                                </div>
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editBadge<?= (int) $badge['id'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <a
                                    href="../actions/badge_delete.php?id=<?= (int) $badge['id'] ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this badge?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php foreach ($badges as $badge): ?>
        <div class="modal fade" id="editBadge<?= (int) $badge['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="../actions/badge_update.php" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Badge</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?= (int) $badge['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($badge['title'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="4" class="form-control" required><?= htmlspecialchars($badge['description'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Badge Color</label>
                                <input
                                    type="color"
                                    name="color"
                                    class="form-control form-control-color"
                                    value="<?= htmlspecialchars($badge['color'] ?? '#0d6efd') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Replace Icon</label>
                                <input type="file" name="icon" class="form-control" accept=".png,.jpg,.jpeg,.webp,.svg">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Badge</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="modal fade" id="addBadge" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="../actions/badge_save.php" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">New Badge</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="4" class="form-control" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Badge Color</label>
                            <input type="color" name="color" class="form-control form-control-color" value="#0d6efd">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Badge Icon</label>
                            <input
                                type="file"
                                name="icon"
                                class="form-control"
                                accept=".png,.jpg,.jpeg,.webp,.svg">
                            <small class="text-muted">Optional. If no icon is uploaded, a default trophy icon will be shown.</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Create Badge</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'partials/footer.php'; ?>
