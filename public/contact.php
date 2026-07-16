<?php
require_once '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="member-shell py-5">
    <div class="dashboard-hero p-4 p-lg-5 mb-4">
        <div class="section-kicker"><i class="bi bi-chat-dots-fill"></i> Contact</div>
        <h1 class="display-5 fw-bold mb-2">Reach the VIDURA team</h1>
        <p class="text-muted fs-5 mb-0">Use this page for the contact path members expect to find in the navbar.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="member-panel h-100">
                <div class="member-panel__inner">
                    <div class="section-title-row">
                        <div>
                            <div class="section-kicker"><i class="bi bi-envelope-paper-fill"></i> Contact details</div>
                            <h3 class="mb-1">Department and club coordination</h3>
                        </div>
                    </div>
                    <div class="d-grid gap-3">
                        <div class="event-item"><div class="fw-semibold">Email</div><div class="text-muted">vidura@liet.ac.in</div></div>
                        <div class="event-item"><div class="fw-semibold">Department</div><div class="text-muted">Computer Science & Systems Engineering</div></div>
                        <div class="event-item"><div class="fw-semibold">Campus</div><div class="text-muted">Lendi Institute of Engineering & Technology</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="member-panel h-100">
                <div class="member-panel__inner">
                    <div class="section-kicker"><i class="bi bi-info-circle-fill"></i> Support</div>
                    <h3>What to send</h3>
                    <p class="text-muted">For members, send student roll number, club name, and the activity or issue you need help with.</p>
                    <div class="mini-metric">
                        <div class="label">Response</div>
                        <div class="small text-muted">This page is ready to be connected to a mail handler later.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
