<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/settings_functions.php';

$team = getOrganizingTeamConfig($pdo);
$people = getOrganizingTeamUserMap($pdo, $team);

$photoUrl = static function (?string $path, string $fallback): string {
    if (!empty($path)) {
        return BASE_URL . '/' . ltrim($path, '/');
    }

    return BASE_URL . '/' . ltrim($fallback, '/');
};

$personFromUserId = static function ($userId) use ($people, $photoUrl): array {
    $userId = (int) $userId;

    if ($userId > 0 && isset($people[$userId])) {
        $profilePhoto = !empty($people[$userId]['profile_photo'])
            ? BASE_URL . '/uploads/profiles/' . rawurlencode(basename($people[$userId]['profile_photo']))
            : '';

        return [
            'name' => $people[$userId]['name'] ?? 'TBD',
            'roll_number' => $people[$userId]['roll_number'] ?? '',
            'photo' => !empty($profilePhoto)
                ? $profilePhoto
                : $photoUrl(null, 'assets/images/team-avatar.svg'),
        ];
    }

    return [
        'name' => 'TBD',
        'roll_number' => '',
        'photo' => $photoUrl(null, 'assets/images/team-avatar.svg'),
    ];
};

$coordinatorPhoto = static function (array $club): string {
    if (!empty($club['coordinator']['photo'])) {
        return BASE_URL . '/' . ltrim($club['coordinator']['photo'], '/');
    }

    return BASE_URL . '/assets/images/team-avatar.svg';
};

$patron = $team['top']['patron'] ?? [];
$faculty = $team['top']['faculty_coordinator'] ?? [];
$studentPresident = $personFromUserId($team['top']['student_president']['user_id'] ?? 0);
$financeSecretary = $personFromUserId($team['top']['finance_secretary']['user_id'] ?? 0);

$clubs = $team['clubs'] ?? [];
$years = $team['year_coordinators'] ?? [];

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5 org-page">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="section-kicker">
            <i class="bi bi-diagram-3-fill"></i>
            Organizing Team
        </div>
        <h1 class="display-5 fw-bold mb-0">Organizing Team</h1>
    </div>

    <div class="org-tree">
        <div class="org-stack org-stack--top">
            <div class="org-node org-node--top">
                <div class="org-node__media">
                    <img src="<?= htmlspecialchars($photoUrl($patron['photo'] ?? '', 'assets/images/team-avatar.svg')) ?>" alt="<?= htmlspecialchars($patron['name'] ?? 'Patron') ?>">
                </div>
                <div class="org-node__body">
                    <div class="org-node__label"><?= htmlspecialchars($patron['label'] ?? 'Patron (HoD)') ?></div>
                    <div class="org-node__name"><?= htmlspecialchars($patron['name'] ?? 'TBD') ?></div>
                </div>
            </div>

            <div class="org-connector org-connector--vertical"></div>

            <div class="org-node org-node--top">
                <div class="org-node__media">
                    <img src="<?= htmlspecialchars($photoUrl($faculty['photo'] ?? '', 'assets/images/team-avatar.svg')) ?>" alt="<?= htmlspecialchars($faculty['name'] ?? 'Faculty Coordinator') ?>">
                </div>
                <div class="org-node__body">
                    <div class="org-node__label"><?= htmlspecialchars($faculty['label'] ?? 'Faculty Coordinator') ?></div>
                    <div class="org-node__name"><?= htmlspecialchars($faculty['name'] ?? 'TBD') ?></div>
                </div>
            </div>

            <div class="org-connector org-connector--vertical"></div>

            <div class="org-stack org-stack--student">
                <div class="org-node org-node--student org-node--student-primary">
                    <div class="org-node__media org-node__media--round">
                        <img src="<?= htmlspecialchars($studentPresident['photo']) ?>" alt="<?= htmlspecialchars($studentPresident['name']) ?>">
                    </div>
                    <div class="org-node__body">
                        <div class="org-node__label"><?= htmlspecialchars($team['top']['student_president']['label'] ?? 'Student President') ?></div>
                        <div class="org-node__name"><?= htmlspecialchars($studentPresident['name']) ?></div>
                    </div>
                </div>

                <div class="org-connector org-connector--vertical org-connector--short"></div>

                <div class="org-node org-node--student org-node--student-secondary">
                    <div class="org-node__media org-node__media--round">
                        <img src="<?= htmlspecialchars($financeSecretary['photo']) ?>" alt="<?= htmlspecialchars($financeSecretary['name']) ?>">
                    </div>
                    <div class="org-node__body">
                        <div class="org-node__label"><?= htmlspecialchars($team['top']['finance_secretary']['label'] ?? 'Finance Secretary') ?></div>
                        <div class="org-node__name"><?= htmlspecialchars($financeSecretary['name']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="org-connector org-connector--down"></div>

        <div class="org-clubs">
            <?php foreach ($clubs as $clubKey => $club): ?>
                <?php
                    $accent = $club['accent'] ?? '#1d4ed8';
                    $coordinator = $personFromUserId($club['coordinator']['user_id'] ?? 0);
                ?>
                <div class="org-club" style="--accent: <?= htmlspecialchars($accent) ?>;">
                    <div class="org-club__header">
                        <span class="org-club__title"><?= htmlspecialchars($club['title'] ?? strtoupper($clubKey)) ?></span>
                    </div>
                    <div class="org-club__body">
                        <div class="org-node org-node--club">
                            <div class="org-node__media org-node__media--round org-node__media--small">
                                <img src="<?= htmlspecialchars($coordinatorPhoto($club)) ?>" alt="<?= htmlspecialchars($club['title'] ?? 'Club Coordinator') ?>">
                            </div>
                            <div class="org-node__body">
                                <div class="org-node__label"><?= htmlspecialchars($club['coordinator']['label'] ?? 'Coordinator') ?></div>
                                <div class="org-node__name"><?= htmlspecialchars($coordinator['name']) ?></div>
                            </div>
                        </div>

                        <div class="org-club__connector"></div>

                        <div class="org-club__roles">
                            <?php foreach (($club['roles'] ?? []) as $role): ?>
                                <?php $rolePerson = $personFromUserId($role['user_id'] ?? 0); ?>
                                <div class="org-role">
                                    <div class="org-role__icon">
                                        <img src="<?= htmlspecialchars($rolePerson['photo']) ?>" alt="<?= htmlspecialchars($rolePerson['name']) ?>">
                                    </div>
                                    <div class="org-role__label"><?= htmlspecialchars($role['label'] ?? 'Role') ?></div>
                                    <div class="org-role__name"><?= htmlspecialchars($rolePerson['name']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="org-connector org-connector--down"></div>

        <div class="org-year-band">
            <div class="org-year-band__title">YEAR COORDINATORS (COMMON BODY)</div>
            <div class="org-years">
                <?php foreach ($years as $yearRole): ?>
                    <?php $yearPerson = $personFromUserId($yearRole['user_id'] ?? 0); ?>
                    <div class="org-year">
                        <div class="org-year__icon">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div class="org-year__label"><?= htmlspecialchars($yearRole['label'] ?? 'Year Coordinator') ?></div>
                        <div class="org-year__name"><?= htmlspecialchars($yearPerson['name']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="org-connector org-connector--down"></div>

        <div class="org-members">
            <div class="org-members__bar">
                <i class="bi bi-people-fill me-2"></i>
                <?= htmlspecialchars($team['members_label'] ?? 'STUDENT MEMBERS') ?>
            </div>
        </div>
    </div>
</div>

<style>
.org-page .org-tree{
    max-width: 1320px;
    margin: 0 auto;
}

.org-page .org-stack{
    display:flex;
    flex-direction:column;
    align-items:center;
}

.org-page .org-node{
    display:flex;
    align-items:center;
    gap:1rem;
    background:#fff;
    border:2px solid rgba(15,23,42,.18);
    border-left:8px solid var(--accent, #0f172a);
    border-radius:20px;
    box-shadow:0 12px 30px rgba(15,23,42,.08);
    overflow:hidden;
}

.org-page .org-node--top{
    width:min(460px, 100%);
    padding: .85rem 1rem;
}

.org-page .org-node--student{
    width:min(460px, 100%);
    padding: .8rem .95rem;
}

.org-page .org-node--club{
    width:100%;
    justify-content:center;
    padding:.9rem 1rem;
    background: linear-gradient(180deg, rgba(255,255,255,.95), #fff);
    border-left-color: var(--accent);
}

.org-page .org-node__media{
    flex:0 0 80px;
    width:80px;
    height:80px;
    border-radius:18px;
    overflow:hidden;
    background:linear-gradient(135deg,#e2e8f0,#f8fafc);
}

.org-page .org-node__media--round{
    border-radius: 999px;
}

.org-page .org-node__media--small{
    flex: 0 0 58px;
    width: 58px;
    height: 58px;
    border-radius: 16px;
}

.org-page .org-node__media img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.org-page .org-node__label{
    font-size:.9rem;
    letter-spacing:.02em;
    text-transform:uppercase;
    font-weight:700;
    color:var(--accent, #0f172a);
}

.org-page .org-node__name{
    font-size:1.45rem;
    font-weight:800;
    color:#0f172a;
    line-height:1.15;
}

.org-page .org-connector{
    margin: .2rem auto;
    background: linear-gradient(to bottom, rgba(15,23,42,.5), rgba(15,23,42,.08));
}

.org-page .org-connector--vertical{
    width:2px;
    height:34px;
}

.org-page .org-connector--short{
    height:20px;
}

.org-page .org-connector--down{
    width:min(90%, 1050px);
    height:2px;
    margin: 1rem auto 1.5rem;
    background: linear-gradient(90deg, rgba(15,23,42,.42), rgba(15,23,42,.1));
}

.org-page .org-stack--student{
    display:flex;
    flex-direction:column;
    align-items:center;
}

.org-page .org-clubs{
    display:grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.2rem;
}

.org-page .org-club{
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 14px 34px rgba(15,23,42,.08);
    overflow:hidden;
    border: 2px solid rgba(15,23,42,.12);
}

.org-page .org-club__header{
    padding: 1rem 1.1rem;
    background: var(--accent, #0f172a);
    color: #fff;
    text-align:center;
}

.org-page .org-club__title{
    font-size:1.5rem;
    font-weight:900;
    letter-spacing:.06em;
}

.org-page .org-club__body{
    padding: 1rem;
}

.org-page .org-club__connector{
    width:2px;
    height:28px;
    background: linear-gradient(to bottom, rgba(15,23,42,.34), rgba(15,23,42,.1));
    margin: .6rem auto;
}

.org-page .org-club__roles{
    display:grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .8rem;
}

.org-page .org-role{
    border: 2px solid rgba(15, 23, 42, .14);
    border-radius: 18px;
    padding: .9rem .65rem;
    text-align:center;
    background: linear-gradient(180deg, rgba(255,255,255,.98), #fff);
    min-height: 145px;
}

.org-page .org-role__icon{
    width:62px;
    height:62px;
    margin: 0 auto .6rem;
    border-radius: 18px;
    overflow:hidden;
    background: linear-gradient(135deg, rgba(255,255,255,.95), #eef2ff);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.4rem;
    color:var(--accent);
}

.org-page .org-role__icon img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.org-page .org-role__label{
    font-size:.92rem;
    font-weight:700;
    color:#334155;
    line-height:1.2;
}

.org-page .org-role__name{
    font-weight:800;
    color: var(--accent);
    margin-top:.3rem;
}

.org-page .org-year-band{
    text-align:center;
    margin-top: .25rem;
}

.org-page .org-year-band__title{
    display:inline-block;
    background:#1f2937;
    color:#fff;
    font-weight:900;
    letter-spacing:.04em;
    padding:.7rem 1.3rem;
    border-radius: 14px;
    box-shadow:0 10px 24px rgba(15,23,42,.18);
    margin-bottom: 1rem;
}

.org-page .org-years{
    display:grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.org-page .org-year{
    background:#fff;
    border:2px solid rgba(15,23,42,.16);
    border-radius: 18px;
    padding: .9rem;
    text-align:center;
    box-shadow:0 10px 26px rgba(15,23,42,.06);
}

.org-page .org-year__icon{
    width:52px;
    height:52px;
    border-radius: 16px;
    margin: 0 auto .55rem;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#0f172a;
    color:#fff;
    font-size:1.25rem;
}

.org-page .org-year__label{
    font-weight:700;
    color:#334155;
}

.org-page .org-year__name{
    font-size:1.15rem;
    font-weight:800;
    color:#0f172a;
    margin-top:.2rem;
}

.org-page .org-members{
    display:flex;
    justify-content:center;
}

.org-page .org-members__bar{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:.5rem;
    background:#0f172a;
    color:#fff;
    font-weight:900;
    letter-spacing:.05em;
    padding: .95rem 2rem;
    border-radius: 16px;
    box-shadow:0 12px 28px rgba(15,23,42,.18);
    min-width: 320px;
}

@media (max-width: 1199.98px){
    .org-page .org-clubs{
        grid-template-columns: 1fr;
    }

    .org-page .org-club__roles{
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px){
    .org-page .org-years{
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .org-page .org-node__name{
        font-size: 1.25rem;
    }
}

@media (max-width: 767.98px){
    .org-page .org-node,
    .org-page .org-node--top,
    .org-page .org-node--student{
        width:100%;
    }

    .org-page .org-club__roles,
    .org-page .org-years{
        grid-template-columns: 1fr;
    }

    .org-page .org-members__bar{
        width: 100%;
        min-width: 0;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
