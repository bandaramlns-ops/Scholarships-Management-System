<?php

require_once __DIR__ . '/../functions.php';

// Read and validate the scholarship ID from the URL.
$scholarshipId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

// Return to the scholarship list if the ID is invalid.
if (!$scholarshipId) {
    set_flash('error', 'Invalid scholarship selection.');
    redirect('/student/scholarships.php');
}

// Get the selected scholarship from the database.
$statement = $conn->prepare(
    'SELECT *
     FROM scholarships
     WHERE id = ?
     LIMIT 1'
);

$statement->bind_param('i', $scholarshipId);
$statement->execute();

$scholarship = $statement
    ->get_result()
    ->fetch_assoc();

// Display a 404 response when the scholarship does not exist.
if (!$scholarship) {
    http_response_code(404);
    exit('Scholarship not found.');
}

// Check whether the deadline has passed.
$today = date('Y-m-d');

$deadlinePassed = $scholarship['deadline'] < $today;

// Applications are allowed only for open scholarships
// whose deadlines have not passed.
$canApply = (
    $scholarship['status'] === 'Open'
    && !$deadlinePassed
);

// Store any existing application made by the current student.
$existingApplication = null;

if (is_logged_in() && !is_admin()) {

    $applicationStatement = $conn->prepare(
        'SELECT id, status, applied_at
         FROM applications
         WHERE scholarship_id = ?
         AND user_id = ?
         LIMIT 1'
    );

    $userId = (int) $_SESSION['user_id'];

    $applicationStatement->bind_param(
        'ii',
        $scholarshipId,
        $userId
    );

    $applicationStatement->execute();

    $existingApplication = $applicationStatement
        ->get_result()
        ->fetch_assoc();
}

$pageTitle = $scholarship['title'];

include __DIR__ . '/../includes/header.php';

?>

<article class="detail-card">

    <div>

        <span
            class="badge <?= e(strtolower($scholarship['status'])) ?>"
        >
            <?= e($scholarship['status']) ?>
        </span>

        <?php if ($deadlinePassed): ?>

            <span class="badge closed">
                Deadline Passed
            </span>

        <?php endif; ?>

    </div>

    <h1>
        <?= e($scholarship['title']) ?>
    </h1>

    <div class="detail-grid">

        <section>

            <h2>Scholarship Information</h2>

            <p>
                <strong>Provider:</strong><br>

                <?= e($scholarship['provider']) ?>
            </p>

            <p>
                <strong>Scholarship Amount:</strong><br>

                <?= e(format_money($scholarship['amount'])) ?>
            </p>

            <p>
                <strong>Application Deadline:</strong><br>

                <?= e(
                    date(
                        'd F Y',
                        strtotime($scholarship['deadline'])
                    )
                ) ?>
            </p>

            <p>
                <strong>Status:</strong><br>

                <?= e($scholarship['status']) ?>
            </p>

        </section>

        <section>

            <h2>Application Availability</h2>

            <?php if ($existingApplication): ?>

                <div class="note">

                    <p>
                        You have already applied for this scholarship.
                    </p>

                    <p>
                        <strong>Application status:</strong>

                        <span
                            class="badge <?= e(
                                strtolower($existingApplication['status'])
                            ) ?>"
                        >
                            <?= e($existingApplication['status']) ?>
                        </span>
                    </p>

                    <p>
                        <strong>Applied on:</strong>

                        <?= e(
                            date(
                                'd F Y',
                                strtotime(
                                    $existingApplication['applied_at']
                                )
                            )
                        ) ?>
                    </p>

                    <a
                        class="button small"
                        href="<?= BASE_URL ?>/student/my_applications.php"
                    >
                        View My Applications
                    </a>

                </div>

            <?php elseif (!$canApply): ?>

                <div class="alert error">

                    <?php if ($scholarship['status'] === 'Closed'): ?>

                        This scholarship is currently closed.

                    <?php else: ?>

                        The application deadline has passed.

                    <?php endif; ?>

                </div>

            <?php elseif (!is_logged_in()): ?>

                <p>
                    You must log in before submitting an application.
                </p>

                <a
                    class="button"
                    href="<?= BASE_URL ?>/index.php"
                >
                    Login to Apply
                </a>

            <?php elseif (is_admin()): ?>

                <div class="note">
                    Administrators can view scholarship details but cannot
                    submit scholarship applications.
                </div>

            <?php else: ?>

                <p>
                    This scholarship is currently accepting applications.
                </p>

                <a
                    class="button"
                    href="<?= BASE_URL ?>/student/apply.php?scholarship_id=<?= (int) $scholarshipId ?>"
                >
                    Apply Now
                </a>

            <?php endif; ?>

        </section>

    </div>

    <hr>

    <section>

        <h2>Description</h2>

        <p>
            <?= nl2br(e($scholarship['description'])) ?>
        </p>

    </section>

    <section>

        <h2>Eligibility Requirements</h2>

        <p>
            <?= nl2br(e($scholarship['eligibility'])) ?>
        </p>

    </section>

    <p>
        <a
            class="button small"
            href="<?= BASE_URL ?>/student/scholarships.php"
        >
            Back to Scholarships
        </a>
    </p>

</article>

<?php include __DIR__ . '/../includes/footer.php'; ?>