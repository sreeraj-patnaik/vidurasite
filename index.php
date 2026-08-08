<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/home_functions.php';
require_once 'includes/settings_functions.php';

$totalMembers = totalMembers($pdo);
$totalEvents = totalEvents($pdo);
$totalClubs = totalClubs($pdo);
$totalRegistrations = totalRegistrations($pdo);
$settings = getSiteSettings($pdo);

include 'includes/header.php';
include 'includes/navbar.php';

?>

<!-- HERO -->

<section class="hero">

    <div class="container-custom">

        <div class="row align-items-center">

            <div class="col-lg-6 fade-up">

                <h2>Department of Computer Science & Systems Engineering</h2>

                <h1>
                    Welcome to
                    <span style="color:var(--accent);">VIDURA</span>
                </h1>

                <p class="mt-4">

                    We Discover. We Develop.

                    <br><br>

                    VIDURA is the official activity platform of the
                    Department of Computer Science & Systems Engineering,
                    providing students opportunities to learn,
                    compete, innovate, collaborate and lead through
                    technical, sports and cultural activities.

                </p>

                <div class="mt-5">

                    <a href="register.php"
                        class="btn btn-primary-custom me-3">

                        Join VIDURA

                    </a>

                    <a href="public/clubs.php"
                        class="btn btn-outline-custom">

                        Explore Clubs

                    </a>

                </div>

            </div>

            <div class="col-lg-6 text-center fade-up">

                <img src="<?= htmlspecialchars(settingsImageUrl($settings, 'homepage_banner', 'assets/images/hero.png')) ?>"
                    class="img-fluid"
                    style="max-height:520px;"
                    alt="VIDURA Hero">

            </div>

        </div>

    </div>

</section>

<!-- STATS -->

<section>

    <div class="container-custom">

        <div class="stats">

            <div class="row">

                <div class="col-md-3 stat-item">

                    <h2><?= $totalClubs ?></h2>

                    <p>Activity Clubs</p>

                </div>

                <div class="col-md-3 stat-item">

                    <h2><?= $totalMembers ?></h2>

                    <p>Members</p>

                </div>

                <div class="col-md-3 stat-item">

                    <h2><?= $totalEvents ?></h2>

                    <p>Events</p>

                </div>

                <div class="col-md-3 stat-item">

                    <h2><?= $totalRegistrations ?></h2>

                    <p>Registrations</p>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- CLUBS -->

<section>

    <div class="container-custom">

        <div class="text-center mb-5">

            <h2 class="section-title">

                Our Activity Clubs

            </h2>

            <p class="section-subtitle">

                Discover your passion through technology,
                sports and culture.

            </p>

        </div>

        <div class="row g-4">

                <div class="col-lg-4">

                    <div class="card-custom text-center">

                    <img src="<?= htmlspecialchars(settingsImageUrl($settings, 'techkruti_image', 'assets/images/techkruti.png')) ?>"
                        height="90"
                        class="mx-auto mb-4"
                        alt="TechKruti">

                    <h3>TechKruti</h3>

                    <p>

                        Innovation, Programming,
                        AI, Hackathons,
                        Research and Technology.

                    </p>

                </div>

            </div>

                <div class="col-lg-4">

                    <div class="card-custom text-center">

                    <img src="<?= htmlspecialchars(settingsImageUrl($settings, 'khelkruti_image', 'assets/images/khelkruti.png')) ?>"
                        height="90"
                        class="mx-auto mb-4"
                        alt="KhelKruti">

                    <h3>KhelKruti</h3>

                    <p>

                        Sports, Fitness,
                        Team Spirit,
                        Competitions and Leadership.

                    </p>

                </div>

            </div>

                <div class="col-lg-4">

                    <div class="card-custom text-center">

                    <img src="<?= htmlspecialchars(settingsImageUrl($settings, 'samskruti_image', 'assets/images/samskruti.png')) ?>"
                        height="90"
                        class="mx-auto mb-4"
                        alt="SamsKruti">

                    <h3>SamsKruti</h3>

                    <p>

                        Arts, Culture,
                        Creativity,
                        Music,
                        Dance and Literature.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- CTA -->

<section>

    <div class="container-custom">

        <div class="card-custom text-center">

            <h2 class="section-title">

                Become a Part of VIDURA

            </h2>

            <p class="section-subtitle">

                Participate • Learn • Build • Lead • Grow

            </p>

            <div class="mt-4">

                <a href="register.php"
                    class="btn btn-primary-custom">

                    Register Now

                </a>

            </div>

        </div>

    </div>

</section>

<?php include 'includes/footer.php'; ?>
