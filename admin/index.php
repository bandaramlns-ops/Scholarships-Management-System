<?php

require_once __DIR__ . '/../functions.php';

require_admin();


/*
 * Dashboard summary.
 */
$result = $conn->query(
    "SELECT

        (SELECT COUNT(*)
         FROM users) AS total_users,

        (SELECT COUNT(*)
         FROM scholarships) AS total_scholarships,

        (SELECT COUNT(*)
         FROM scholarships
         WHERE status = 'Open') AS open_scholarships,

        (SELECT COUNT(*)
         FROM applications) AS total_applications,

        (SELECT COUNT(*)
         FROM applications
         WHERE status = 'Pending') AS pending_applications,

        (SELECT COUNT(*)
         FROM applications
         WHERE status = 'Approved') AS approved_applications,

        (SELECT COUNT(*)
         FROM applications
         WHERE status = 'Rejected') AS rejected_applications"
);

$summary = $result->fetch_assoc();


$pageTitle = 'Admin Dashboard';

include __DIR__ . '/../includes/header.php';

?>


<div class="page-heading">

    <div>

        <p class="eyebrow">
            Administration
        </p>

        <h1>
            Admin Dashboard
        </h1>

        <p>
            Manage users, scholarships,
            applications and reports.
        </p>

    </div>

</div>


<div class="stats-grid">

    <div class="stat">

        <strong>
            <?= (int) $summary['total_users'] ?>
        </strong>

        <span>
            Users
        </span>

    </div>


    <div class="stat">

        <strong>
            <?= (int) $summary['total_scholarships'] ?>
        </strong>

        <span>
            Scholarships
        </span>

    </div>


    <div class="stat">

        <strong>
            <?= (int) $summary['open_scholarships'] ?>
        </strong>

        <span>
            Open Scholarships
        </span>

    </div>


    <div class="stat">

        <strong>
            <?= (int) $summary['total_applications'] ?>
        </strong>

        <span>
            Applications
        </span>

    </div>

</div>


<div class="stats-grid">

    <div class="stat">

        <strong>
            <?= (int) $summary['pending_applications'] ?>
        </strong>

        <span>
            Pending
        </span>

    </div>


    <div class="stat">

        <strong>
            <?= (int) $summary['approved_applications'] ?>
        </strong>

        <span>
            Approved
        </span>

    </div>


    <div class="stat">

        <strong>
            <?= (int) $summary['rejected_applications'] ?>
        </strong>

        <span>
            Rejected
        </span>

    </div>

</div>


<section class="card">

    <h2>
        Administrative Tasks
    </h2>


    <div class="feature-grid">


        <div>

            <h3>
                Manage Users
            </h3>

            <p>
                Add, edit, activate, deactivate
                and delete user accounts.
            </p>

            <a
                class="button small"
                href="<?= BASE_URL ?>/admin/users.php"
            >
                Manage Users
            </a>

        </div>


        <div>

            <h3>
                Manage Scholarships
            </h3>

            <p>
                Add, edit, open, close and
                delete scholarships.
            </p>

            <a
                class="button small"
                href="<?= BASE_URL ?>/admin/scholarships.php"
            >
                Manage Scholarships
            </a>

        </div>


        <div>

            <h3>
                Review Applications
            </h3>

            <p>
                Review student applications and
                record decisions.
            </p>

            <a
                class="button small"
                href="<?= BASE_URL ?>/admin/applications.php"
            >
                Review Applications
            </a>

        </div>


        <div>

            <h3>
                Reports
            </h3>

            <p>
                View system statistics and
                application reports.
            </p>

            <a
                class="button small"
                href="<?= BASE_URL ?>/admin/reports.php"
            >
                View Reports
            </a>

        </div>


    </div>

</section>


<?php

include __DIR__ . '/../includes/footer.php';

?>