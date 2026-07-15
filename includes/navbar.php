<nav class="navbar navbar-expand-lg navbar-custom sticky-top">

    <div class="container-custom">

        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">

            <img src="<?= BASE_URL ?>/assets/images/liet_logo.png"
                alt="LIET"
                height="48"
                class="me-3">

            <img src="<?= BASE_URL ?>/assets/images/vidura_logo.png"
                alt="VIDURA"
                height="48">

        </a>

        <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse justify-content-end"
            id="mainNavbar">

            <ul class="navbar-nav align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/about.php">About</a>
                </li>

                <li class="nav-item dropdown">

                    <a class="nav-link dropdown-toggle"
                        href="#"
                        data-bs-toggle="dropdown">

                        Clubs

                    </a>

                    <ul class="dropdown-menu shadow border-0">

                        <li>
                            <a class="dropdown-item"
                                href="<?= BASE_URL ?>/public/techkruti.php">

                                💻 TechKruti

                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                                href="<?= BASE_URL ?>/public/khelkruti.php">

                                🏆 KhelKruti

                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                                href="<?= BASE_URL ?>/public/samskruti.php">

                                🎭 SamsKruti

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

                <li class="nav-item ms-lg-3">

                    <a class="btn btn-outline-custom"
                        href="<?= BASE_URL ?>/login.php">

                        Login

                    </a>

                </li>

                <li class="nav-item ms-lg-2">

                    <a class="btn btn-primary-custom"
                        href="<?= BASE_URL ?>/register.php">

                        Join VIDURA

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>