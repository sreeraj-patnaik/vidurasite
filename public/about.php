<?php
require_once '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="section-kicker">
            <i class="bi bi-info-circle-fill"></i>
            About VIDURA
        </div>
        <h1 class="display-5 fw-bold mb-3">A single activity platform for the department</h1>
        <p class="text-muted fs-5 mb-0" style="max-width:900px;">
            VIDURA brings clubs, events, attendance, points, badges, gallery highlights, and member identity into one organized system for the Department of Computer Science & Systems Engineering.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="member-panel h-100">
                <div class="member-panel__inner">
                    <div class="section-title-row">
                        <div>
                            <div class="section-kicker"><i class="bi bi-bullseye"></i> What it does</div>
                            <h3 class="mb-1">The platform in one view</h3>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><div class="event-item h-100"><h5>Membership</h5><p class="text-muted mb-0">Member profiles, approvals, and identity management.</p></div></div>
                        <div class="col-md-6"><div class="event-item h-100"><h5>Activities</h5><p class="text-muted mb-0">Events, registrations, attendance, and participation points.</p></div></div>
                        <div class="col-md-6"><div class="event-item h-100"><h5>Recognition</h5><p class="text-muted mb-0">Badges, leaderboard, and member achievements.</p></div></div>
                        <div class="col-md-6"><div class="event-item h-100"><h5>Media</h5><p class="text-muted mb-0">Gallery pages for event memories and highlights.</p></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="member-panel h-100">
                <div class="member-panel__inner">
                    <div class="section-kicker"><i class="bi bi-people-fill"></i> Clubs</div>
                    <h3>Three activity streams</h3>
                    <div class="d-grid gap-3 mt-4">
                        <div class="mini-metric"><div class="label">TechKruti</div><div class="small text-muted">Technical workshops, coding, innovation, and tech events.</div></div>
                        <div class="mini-metric"><div class="label">KhelKruti</div><div class="small text-muted">Sports, competition, fitness, and team activities.</div></div>
                        <div class="mini-metric"><div class="label">SamsKruti</div><div class="small text-muted">Cultural, literary, creative, and stage activities.</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
