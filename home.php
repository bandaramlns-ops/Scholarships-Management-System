<?php

require_once __DIR__ . '/functions.php';

$pageTitle = 'Home';

$user = null;
$applicationCounts = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
];

$openScholarshipCount = 0;


/*
 * Count currently open scholarships.
 */
$scholarshipResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM scholarships
     WHERE status = 'Open'
     AND deadline >= CURDATE()"
);

if ($scholarshipResult) {
    $openScholarshipCount = (int)
        $scholarshipResult->fetch_assoc()['total'];
}


/*
 * Get information for logged-in users.
 */
if (is_logged_in()) {

    $user = current_user($conn);

    if (!is_admin()) {

        $userId = (int) $_SESSION['user_id'];

        $statement = $conn->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'Pending') AS pending,
                SUM(status = 'Approved') AS approved,
                SUM(status = 'Rejected') AS rejected
             FROM applications
             WHERE user_id = ?"
        );

        $statement->bind_param('i', $userId);
        $statement->execute();

        $counts = $statement
            ->get_result()
            ->fetch_assoc();

        $applicationCounts = [
            'total' => (int) ($counts['total'] ?? 0),
            'pending' => (int) ($counts['pending'] ?? 0),
            'approved' => (int) ($counts['approved'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0)
        ];
    }
}

include __DIR__ . '/includes/header.php';

?>


<?php if (!is_logged_in()): ?>

    <section class="hero">

        <p class="eyebrow">
            Online Scholarship Management System
        </p>

        <h1>
            Find and Apply for University Scholarships
        </h1>

        <p>
            Browse available scholarship opportunities,
            review eligibility requirements and submit
            applications through one centralized system.
        </p>

        <div class="hero-actions">

            <a
                class="button"
                href="<?= BASE_URL ?>/student/scholarships.php"
            >
                Browse Scholarships
            </a>

            <a
                class="button secondary"
                href="<?= BASE_URL ?>/index.php"
            >
                Login
            </a>

            <a
                class="button secondary"
                href="<?= BASE_URL ?>/student/register.php"
            >
                Create Account
            </a>

        </div>

    </section>


    <div class="stats-grid">

        <div class="stat">

            <strong>
                <?= $openScholarshipCount ?>
            </strong>

            <span>
                Open Scholarships
            </span>

        </div>

        <div class="stat">

            <strong>
                Online
            </strong>

            <span>
                Application Process
            </span>

        </div>

        <div class="stat">

            <strong>
                Secure
            </strong>

            <span>
                User Accounts
            </span>

        </div>

    </div>


    <section class="card">

        <h2>
            How It Works
        </h2>

        <div class="feature-grid">

            <div>

                <h3>
                    1. Create an Account
                </h3>

                <p>
                    Register as a student and complete
                    your personal and academic profile.
                </p>

            </div>


            <div>

                <h3>
                    2. Find Scholarships
                </h3>

                <p>
                    Search available scholarships and
                    review their eligibility requirements.
                </p>

            </div>


            <div>

                <h3>
                    3. Apply Online
                </h3>

                <p>
                    Submit your application, academic
                    information and supporting documents.
                </p>

            </div>


            <div>

                <h3>
                    4. Track Your Result
                </h3>

                <p>
                    View Pending, Approved or Rejected
                    application decisions online.
                </p>

            </div>

        </div>

    </section>


<?php elseif (is_admin()): ?>


    <section class="hero">

        <p class="eyebrow">
            Administrator
        </p>

        <h1>
            Welcome,
            <?= e($user['full_name'] ?: $user['username']) ?>
        </h1>

        <p>
            Use the administrator dashboard to manage
            users, scholarships, applications and reports.
        </p>

        <a
            class="button"
            href="<?= BASE_URL ?>/admin/"
        >
            Open Admin Dashboard
        </a>

    </section>


    <section class="card">

        <h2>
            Administrator Functions
        </h2>

        <div class="feature-grid">

            <div>
                <h3>Manage Users</h3>

                <p>
                    Add, edit, activate, deactivate
                    and delete user accounts.
                </p>

                <a
                    href="<?= BASE_URL ?>/admin/users.php"
                >
                    Manage Users
                </a>
            </div>


            <div>
                <h3>Manage Scholarships</h3>

                <p>
                    Create, edit, open, close and
                    remove scholarship opportunities.
                </p>

                <a
                    href="<?= BASE_URL ?>/admin/scholarships.php"
                >
                    Manage Scholarships
                </a>
            </div>


            <div>
                <h3>Review Applications</h3>

                <p>
                    Review student applications and
                    record scholarship decisions.
                </p>

                <a
                    href="<?= BASE_URL ?>/admin/applications.php"
                >
                    Review Applications
                </a>
            </div>


            <div>
                <h3>Reports</h3>

                <p>
                    View summary statistics for users,
                    scholarships and applications.
                </p>

                <a
                    href="<?= BASE_URL ?>/admin/reports.php"
                >
                    Open Reports
                </a>
            </div>

        </div>

    </section>


<?php else: ?>


    <section class="hero">

        <p class="eyebrow">
            Student Dashboard
        </p>

        <h1>
            Welcome,
            <?= e($user['full_name'] ?: $user['username']) ?>
        </h1>

        <p>
            Explore available scholarships and monitor
            your submitted applications.
        </p>

        <div class="hero-actions">

            <a
                class="button"
                href="<?= BASE_URL ?>/student/scholarships.php"
            >
                Browse Scholarships
            </a>

            <a
                class="button secondary"
                href="<?= BASE_URL ?>/student/my_applications.php"
            >
                My Applications
            </a>

        </div>

    </section>


    <div class="stats-grid">

        <div class="stat">

            <strong>
                <?= $openScholarshipCount ?>
            </strong>

            <span>
                Open Scholarships
            </span>

        </div>


        <div class="stat">

            <strong>
                <?= $applicationCounts['total'] ?>
            </strong>

            <span>
                My Applications
            </span>

        </div>


        <div class="stat">

            <strong>
                <?= $applicationCounts['pending'] ?>
            </strong>

            <span>
                Pending
            </span>

        </div>


        <div class="stat">

            <strong>
                <?= $applicationCounts['approved'] ?>
            </strong>

            <span>
                Approved
            </span>

        </div>

    </div>


    <section class="card">

        <h2>
            Quick Actions
        </h2>

        <div class="feature-grid">

            <div>

                <h3>
                    Find Scholarships
                </h3>

                <p>
                    Browse and search available
                    scholarship opportunities.
                </p>

                <a
                    href="<?= BASE_URL ?>/student/scholarships.php"
                >
                    Browse Scholarships
                </a>

            </div>


            <div>

                <h3>
                    Track Applications
                </h3>

                <p>
                    Check the current status of
                    applications you have submitted.
                </p>

                <a
                    href="<?= BASE_URL ?>/student/my_applications.php"
                >
                    My Applications
                </a>

            </div>


            <div>

                <h3>
                    Update Profile
                </h3>

                <p>
                    Keep your contact and academic
                    information up to date.
                </p>

                <a
                    href="<?= BASE_URL ?>/student/profile.php"
                >
                    My Profile
                </a>

            </div>

        </div>

    </section>


<?php endif; ?>


<?php

include __DIR__ . '/includes/footer.php';

?>