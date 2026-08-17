<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/settings_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$settings = getSiteSettings($pdo);
$team = getOrganizingTeamConfig($pdo);

$members = $pdo->query("
    SELECT id, name, roll_number, status
    FROM users
    WHERE role = 'member'
    ORDER BY CASE WHEN status = 'approved' THEN 0 ELSE 1 END, name ASC
")->fetchAll();

function renderTeamOptions(array $members, $selectedId): string
{
    $selectedId = (int) $selectedId;
    $html = '<option value="">Select student</option>';

    foreach ($members as $member) {
        $id = (int) $member['id'];
        $label = $member['name'] . ' (' . $member['roll_number'] . ')' . (!empty($member['status']) ? ' - ' . ucfirst($member['status']) : '');
        $html .= '<option value="' . $id . '"' . ($selectedId === $id ? ' selected' : '') . '>' . htmlspecialchars($label) . '</option>';
    }

    return $html;
}

function teamPhotoUrl(?string $path, string $fallback = 'assets/images/team-avatar.svg'): string
{
    if (!empty($path)) {
        return BASE_URL . '/' . ltrim($path, '/');
    }

    return BASE_URL . '/' . ltrim($fallback, '/');
}

$extraHead = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">';

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main">
<?php include 'partials/topbar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2>Organizing Team</h2>
            <p class="text-muted mb-0">Edit the full hierarchy shown on the public organizing team page.</p>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="../actions/organizing_team_save.php" method="POST" enctype="multipart/form-data">
        <div class="row g-4">
            <div class="col-12">
                <div class="card-admin">
                    <h5 class="mb-3">Top Leadership</h5>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100 bg-light">
                                <h6 class="mb-3">Patron</h6>
                                <div class="mb-3">
                                    <label class="form-label">Role Name</label>
                                    <input type="text" name="organizing_team[top][patron][label]" class="form-control" value="<?= htmlspecialchars($team['top']['patron']['label'] ?? 'Patron (HoD)') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="organizing_team[top][patron][name]" class="form-control" value="<?= htmlspecialchars($team['top']['patron']['name'] ?? '') ?>" placeholder="Dr. R. Rajender">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Photo</label>
                                    <div class="mb-3 text-center">
                                        <img
                                            src="<?= htmlspecialchars(!empty($team['top']['patron']['photo']) ? (BASE_URL . '/' . ltrim($team['top']['patron']['photo'], '/')) : settingsImageUrl($settings, 'patron_photo', 'assets/images/team-avatar.svg')) ?>"
                                            alt="Patron"
                                            class="img-fluid rounded-circle border bg-white"
                                            style="width:120px;height:120px;object-fit:cover;">
                                    </div>
                                    <input type="file" name="team_photos[patron]" class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100 bg-light">
                                <h6 class="mb-3">Faculty Coordinator</h6>
                                <div class="mb-3">
                                    <label class="form-label">Role Name</label>
                                    <input type="text" name="organizing_team[top][faculty_coordinator][label]" class="form-control" value="<?= htmlspecialchars($team['top']['faculty_coordinator']['label'] ?? 'Faculty Coordinator') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="organizing_team[top][faculty_coordinator][name]" class="form-control" value="<?= htmlspecialchars($team['top']['faculty_coordinator']['name'] ?? '') ?>" placeholder="Mr. S. Swaroop">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Photo</label>
                                    <div class="mb-3 text-center">
                                        <img
                                            src="<?= htmlspecialchars(!empty($team['top']['faculty_coordinator']['photo']) ? (BASE_URL . '/' . ltrim($team['top']['faculty_coordinator']['photo'], '/')) : BASE_URL . '/assets/images/team-avatar.svg') ?>"
                                            alt="Faculty Coordinator"
                                            class="img-fluid rounded-circle border bg-white"
                                            style="width:120px;height:120px;object-fit:cover;">
                                    </div>
                                    <input type="file" name="team_photos[faculty_coordinator]" class="form-control" accept="image/*">
                                </div>
                                <small class="text-muted">Faculty coordinator is typed manually, as requested.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card-admin">
                    <h5 class="mb-3">Student Leadership</h5>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100">
                                <label class="form-label">Student President Role</label>
                                <input type="text" name="organizing_team[top][student_president][label]" class="form-control mb-3" value="<?= htmlspecialchars($team['top']['student_president']['label'] ?? 'Student President') ?>">
                                <label class="form-label">Select Student</label>
                                <select name="organizing_team[top][student_president][user_id]" class="form-select team-user-select" data-placeholder="Search student name or roll number">
                                    <?= renderTeamOptions($members, $team['top']['student_president']['user_id'] ?? 0) ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100">
                                <label class="form-label">Finance Secretary Role</label>
                                <input type="text" name="organizing_team[top][finance_secretary][label]" class="form-control mb-3" value="<?= htmlspecialchars($team['top']['finance_secretary']['label'] ?? 'Finance Secretary') ?>">
                                <label class="form-label">Select Student</label>
                                <select name="organizing_team[top][finance_secretary][user_id]" class="form-select team-user-select" data-placeholder="Search student name or roll number">
                                    <?= renderTeamOptions($members, $team['top']['finance_secretary']['user_id'] ?? 0) ?>
                                </select>
                                <p class="small text-muted mt-3 mb-0">Finance Secretary is placed directly under Student President in the public hierarchy.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card-admin">
                    <h5 class="mb-3">Club Leadership</h5>
                    <div class="row g-4">
                        <?php foreach (($team['clubs'] ?? []) as $clubKey => $club): ?>
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100" style="border-left: 6px solid <?= htmlspecialchars($club['accent'] ?? '#1d4ed8') ?> !important;">
                                    <div class="mb-3">
                                        <label class="form-label">Club Title</label>
                                        <input type="text" name="organizing_team[clubs][<?= htmlspecialchars($clubKey) ?>][title]" class="form-control" value="<?= htmlspecialchars($club['title'] ?? strtoupper($clubKey)) ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Coordinator Role</label>
                                        <input type="text" name="organizing_team[clubs][<?= htmlspecialchars($clubKey) ?>][coordinator][label]" class="form-control mb-2" value="<?= htmlspecialchars($club['coordinator']['label'] ?? 'Coordinator') ?>">
                                        <div class="mb-2 text-center">
                                            <img
                                                src="<?= htmlspecialchars(teamPhotoUrl($club['coordinator']['photo'] ?? '')) ?>"
                                                alt="<?= htmlspecialchars($club['title'] ?? strtoupper($clubKey)) ?> Coordinator"
                                                class="img-fluid rounded-circle border bg-white"
                                                style="width:96px;height:96px;object-fit:cover;">
                                        </div>
                                        <input type="file" name="team_photos[clubs][<?= htmlspecialchars($clubKey) ?>][coordinator]" class="form-control mb-2" accept="image/*">
                                        <select name="organizing_team[clubs][<?= htmlspecialchars($clubKey) ?>][coordinator][user_id]" class="form-select team-user-select" data-placeholder="Search student name or roll number">
                                            <?= renderTeamOptions($members, $club['coordinator']['user_id'] ?? 0) ?>
                                        </select>
                                    </div>

                                    <?php foreach (($club['roles'] ?? []) as $index => $role): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Role <?= $index + 1 ?></label>
                                            <input type="text" name="organizing_team[clubs][<?= htmlspecialchars($clubKey) ?>][roles][<?= $index ?>][label]" class="form-control mb-2" value="<?= htmlspecialchars($role['label'] ?? '') ?>">
                                            <select name="organizing_team[clubs][<?= htmlspecialchars($clubKey) ?>][roles][<?= $index ?>][user_id]" class="form-select team-user-select" data-placeholder="Search student name or roll number">
                                                <?= renderTeamOptions($members, $role['user_id'] ?? 0) ?>
                                            </select>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card-admin">
                    <h5 class="mb-3">Year Coordinators</h5>
                    <div class="row g-3">
                        <?php foreach (($team['year_coordinators'] ?? []) as $index => $role): ?>
                            <div class="col-lg-3 col-md-6">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <input type="text" name="organizing_team[year_coordinators][<?= $index ?>][label]" class="form-control mb-3" value="<?= htmlspecialchars($role['label'] ?? '') ?>">
                                    <select name="organizing_team[year_coordinators][<?= $index ?>][user_id]" class="form-select team-user-select" data-placeholder="Search student name or roll number">
                                        <?= renderTeamOptions($members, $role['user_id'] ?? 0) ?>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card-admin">
                    <h5 class="mb-3">Footer Label</h5>
                    <label class="form-label">Members Bar Title</label>
                    <input type="text" name="organizing_team[members_label]" class="form-control" value="<?= htmlspecialchars($team['members_label'] ?? 'STUDENT MEMBERS') ?>">
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Organizing Team
                </button>
            </div>
        </div>
    </form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
document.querySelectorAll('.team-user-select').forEach(function(selectEl) {
    new Choices(selectEl, {
        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false,
        placeholder: true,
        placeholderValue: selectEl.getAttribute('data-placeholder') || 'Search...'
    });
});
</script>

<?php include 'partials/footer.php'; ?>
