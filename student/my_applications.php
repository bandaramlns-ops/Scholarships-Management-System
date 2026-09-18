<?php

require_once __DIR__ . '/../functions.php';

// Only logged-in users can access this page.
require_login();

// Administrators should use the administrator application page.
if (is_admin()) {
    redirect('/admin/applications.php');
}

$userId = (int) $_SESSION['user_id'];

// Retrieve all applications submitted by the current student.
$statement = $conn->prepare(
    'SELECT
         a.id,
         a.scholarship_id,
         a.gpa,
         a.household_income,
         a.personal_statement,
         a.document_path,
         a.status,
         a.admin_comment,
         a.applied_at,
         a.reviewed_at,
         s.title,
         s.provider,
         s.amount,
         s.deadline,
         s.status AS scholarship_status
     FROM applications AS a
     INNER JOIN scholarships AS s
         ON s.id = a.scholarship_id
     WHERE a.user_id = ?
     ORDER BY a.applied_at DESC'
);

$statement->bind_param('i', $userId);
$statement->execute();

$result = $statement->get_result();

$applications = [];
$pendingCount = 0;
$approvedCount = 0;
$rejectedCount = 0;

while ($application = $result->fetch_assoc()) {
    $applications[] = $application;

    if ($application['status'] === 'Pending') {
        $pendingCount++;
    } elseif ($application['status'] === 'Approved') {
        $approvedCount++;
    } elseif ($application['status'] === 'Rejected') {
        $rejectedCount++;
    }
}

$pageTitle = 'My Applications';

include __DIR__ . '/../includes/header.php';

?>

<div class="page-heading">

    <div>
        <p class="eyebrow">Application tracking</p>

        <h1>My Applications</h1>

        <p>
            View your submitted scholarship applications and their
            current decisions.
        </p>
    </div>

    <a
        class="button"
        href="<?= BASE_URL ?>/student/scholarships.php"
    >
        Browse Scholarships
    </a>

</div>

<div class="stats-grid">

    <div class="stat">
        <strong><?= count($applications) ?></strong>
        <span>Total applications</span>
    </div>

    <div class="stat">
        <strong><?= $pendingCount ?></strong>
        <span>Pending</span>
    </div>

    <div class="stat">
        <strong><?= $approvedCount ?></strong>
        <span>Approved</span>
    </div>

    <div class="stat">
        <strong><?= $rejectedCount ?></strong>
        <span>Rejected</span>
    </div>

</div>

<?php if (empty($applications)): ?>

    <section class="card">

        <h2>No applications submitted</h2>

        <p>
            You have not submitted any scholarship applications yet.
        </p>

        <a
            class="button"
            href="<?= BASE_URL ?>/student/scholarships.php"
        >
            Find a Scholarship
        </a>

    </section>

<?php else: ?>

    <div class="card-list">

        <?php foreach ($applications as $application): ?>

            <article class="card">

                <div class="page-heading">

                    <div>

                        <span
                            class="badge <?= e(
                                strtolower($application['status'])
                            ) ?>"
                        >
                            <?= e($application['status']) ?>
                        </span>

                        <h2>
                            <?= e($application['title']) ?>
                        </h2>

                        <p>
                            <strong>Provider:</strong>
                            <?= e($application['provider']) ?>
                        </p>

                    </div>

                    <div>

                        <a
                            class="button small"
                            href="<?= BASE_URL ?>/student/scholarship_view.php?id=<?= (int) $application['scholarship_id'] ?>"
                        >
                            View Scholarship
                        </a>

                    </div>

                </div>

                <div class="detail-grid">

                    <section>

                        <h3>Application Information</h3>

                        <p>
                            <strong>GPA:</strong><br>
                            <?= e(
                                number_format(
                                    (float) $application['gpa'],
                                    2
                                )
                            ) ?>
                        </p>

                        <p>
                            <strong>Annual household income:</strong><br>
                            <?= e(
                                format_money(
                                    $application['household_income']
                                )
                            ) ?>
                        </p>

                        <p>
                            <strong>Scholarship amount:</strong><br>
                            <?= e(
                                format_money(
                                    $application['amount']
                                )
                            ) ?>
                        </p>

                    </section>

                    <section>

                        <h3>Dates and Decision</h3>

                        <p>
                            <strong>Applied on:</strong><br>

                            <?= e(
                                date(
                                    'd F Y, h:i A',
                                    strtotime(
                                        $application['applied_at']
                                    )
                                )
                            ) ?>
                        </p>

                        <p>
                            <strong>Application deadline:</strong><br>

                            <?= e(
                                date(
                                    'd F Y',
                                    strtotime(
                                        $application['deadline']
                                    )
                                )
                            ) ?>
                        </p>

                        <p>
                            <strong>Reviewed on:</strong><br>

                            <?php if ($application['reviewed_at']): ?>

                                <?= e(
                                    date(
                                        'd F Y, h:i A',
                                        strtotime(
                                            $application['reviewed_at']
                                        )
                                    )
                                ) ?>

                            <?php else: ?>

                                Not reviewed yet

                            <?php endif; ?>

                        </p>

                    </section>

                </div>

                <hr>

                <section>

                    <h3>Personal Statement</h3>

                    <p>
                        <?= nl2br(
                            e($application['personal_statement'])
                        ) ?>
                    </p>

                </section>

                <section>

                    <h3>Administrator Comment</h3>

                    <?php if (
                        trim(
                            (string) $application['admin_comment']
                        ) !== ''
                    ): ?>

                        <div class="note">
                            <?= nl2br(
                                e($application['admin_comment'])
                            ) ?>
                        </div>

                    <?php elseif (
                        $application['status'] === 'Pending'
                    ): ?>

                        <div class="note">
                            This application has not yet been reviewed.
                        </div>

                    <?php else: ?>

                        <div class="note">
                            No administrator comment was provided.
                        </div>

                    <?php endif; ?>

                </section>

                <?php if ($application['document_path']): ?>

                    <p>
                        <a
                            class="button small"
                            href="<?= BASE_URL ?>/download_document.php?application_id=<?= (int) $application['id'] ?>"
                            target="_blank"
                            rel="noopener"
                        >
                            Open Supporting Document
                        </a>
                    </p>

                <?php else: ?>

                    <p>
                        <strong>Supporting document:</strong>
                        No document uploaded
                    </p>

                <?php endif; ?>

            </article>

        <?php endforeach; ?>

    </div>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>