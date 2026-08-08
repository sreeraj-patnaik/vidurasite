<?php

require_once __DIR__ . '/settings_functions.php';

$siteSettings = $settings ?? [];
?>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top">

    <div class="container">

        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">
            <img src="<?= htmlspecialchars(settingsImageUrl($siteSettings, 'liet_logo', 'assets/images/liet_logo.png')) ?>" alt="LIET" height="45" class="me-3">
            <img src="<?= htmlspecialchars(settingsImageUrl($siteSettings, 'vidura_logo', 'assets/images/vidura_logo.png')) ?>" alt="VIDURA" height="45">
        </a>

        <button class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            <ul class="navbar-nav mx-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/about.php">About</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Clubs
                    </a>
                    <ul class="dropdown-menu shadow border-0">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/techkruti.php">
                                <i class="bi bi-cpu me-2"></i>TechKruti
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/khelkruti.php">
                                <i class="bi bi-trophy me-2"></i>KhelKruti
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/samskruti.php">
                                <i class="bi bi-music-note-beamed me-2"></i>SamsKruti
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/events.php">Events</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/gallery.php">Gallery</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/leaderboard.php">Leaderboard</a>
                </li>

            </ul>

            <?php if(isset($_SESSION['user_id'])): ?>

            <div class="dropdown ms-lg-3">
                <button class="btn btn-primary-custom dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($_SESSION['name']) ?>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0">

                    <?php if($_SESSION['role']=="admin"): ?>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/admin/students.php">
                                <i class="bi bi-people me-2"></i>
                                Students
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/admin/events.php">
                                <i class="bi bi-calendar-event me-2"></i>
                                Events
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/admin/registrations.php">
                                <i class="bi bi-check2-square me-2"></i>
                                Registrations
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/admin/points.php">
                                <i class="bi bi-star me-2"></i>
                                Points
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/admin/gallery.php">
                                <i class="bi bi-images me-2"></i>
                                Gallery
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/admin/announcements.php">
                                <i class="bi bi-megaphone me-2"></i>
                                Announcements
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/member/dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/member/profile.php">
                                <i class="bi bi-person me-2"></i>
                                Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/member/idcard.php">
                                <i class="bi bi-person-badge me-2"></i>
                                Digital ID
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/member/events.php">
                                <i class="bi bi-calendar-event me-2"></i>
                                My Events
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/member/leaderboard.php">
                                <i class="bi bi-trophy me-2"></i>
                                Leaderboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/member/badges.php">
                                <i class="bi bi-award me-2"></i>
                                My Badges
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/member/gallery.php">
                                <i class="bi bi-images me-2"></i>
                                Gallery
                            </a>
                        </li>
                    <?php endif; ?>

                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Logout
                        </a>
                    </li>

                </ul>
            </div>

            <?php else: ?>

            <div class="d-flex ms-lg-3 gap-2">
                <a class="btn btn-outline-custom" href="<?= BASE_URL ?>/login.php">Login</a>
                <a class="btn btn-primary-custom" href="<?= BASE_URL ?>/register.php">Join VIDURA</a>
            </div>

            <?php endif; ?>

        </div>

    </div>

</nav>
