<?php
$mode = $_GET['mode'] ?? 'list';
?>

<?php if ($mode === 'review') { ?>
<?php

require_once __DIR__ . '/../functions.php';

require_admin();

$id =
    filter_input(
        INPUT_GET,
        'id',
        FILTER_VALIDATE_INT
    )
    ?: filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );

if (!$id) {

    set_flash(
        'error',
        'Invalid application selection.'
    );

    redirect('/admin/applications.php');
}


/*
 * Retrieve the complete application,
 * student and scholarship information.
 */
$statement = $conn->prepare(
    'SELECT
        a.*,

        u.username,
        u.full_name,
        u.email,
        u.phone,
        u.faculty,
        u.year_of_study,

        s.title,
        s.provider,
        s.amount,
        s.deadline,
        s.status AS scholarship_status

     FROM applications AS a

     INNER JOIN users AS u
        ON u.id = a.user_id

     INNER JOIN scholarships AS s
        ON s.id = a.scholarship_id

     WHERE a.id = ?

     LIMIT 1'
);

$statement->bind_param(
    'i',
    $id
);

$statement->execute();

$application = $statement
    ->get_result()
    ->fetch_assoc();

if (!$application) {

    http_response_code(404);

    exit(
        'Application not found.'
    );
}


$errors = [];


/*
 * Process the administrator decision.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $status =
        $_POST['status'] ?? '';

    $comment = trim(
        $_POST['admin_comment'] ?? ''
    );


    if (
        !in_array(
            $status,
            [
                'Pending',
                'Approved',
                'Rejected'
            ],
            true
        )
    ) {

        $errors[] =
            'Choose a valid application status.';
    }


    if (strlen($comment) > 5000) {

        $errors[] =
            'Administrator comment is too long.';
    }


    if (empty($errors)) {

        $updateStatement = $conn->prepare(
            "UPDATE applications

             SET
                status = ?,
                admin_comment = ?,

                reviewed_at =
                    CASE
                        WHEN ? = 'Pending'
                        THEN NULL
                        ELSE NOW()
                    END

             WHERE id = ?"
        );

        $updateStatement->bind_param(
            'sssi',
            $status,
            $comment,
            $status,
            $id
        );

        $updateStatement->execute();


        set_flash(
            'success',
            'Application decision saved successfully.'
        );


        redirect(
            '/admin/applications.php'
        );
    }
}


$pageTitle = 'Review Application';

include __DIR__ . '/../includes/header.php';

?>


<div class="page-heading">

    <div>

        <p class="eyebrow">
            Application Review
        </p>

        <h1>
            Review Application
        </h1>

    </div>


    <a
        class="button small"
        href="<?= BASE_URL ?>/admin/applications.php"
    >
        Back to Applications
    </a>

</div>


<section class="detail-card">


    <div class="detail-grid">


        <section>

            <h2>
                Student Information
            </h2>


            <p>

                <strong>
                    Full Name
                </strong>

                <br>

                <?= e($application['full_name']) ?>

            </p>


            <p>

                <strong>
                    Username
                </strong>

                <br>

                <?= e($application['username']) ?>

            </p>


            <p>

                <strong>
                    Email
                </strong>

                <br>

                <?= e($application['email']) ?>

            </p>


            <p>

                <strong>
                    Phone
                </strong>

                <br>

                <?= e(
                    $application['phone']
                    ?: 'Not provided'
                ) ?>

            </p>


            <p>

                <strong>
                    Faculty
                </strong>

                <br>

                <?= e(
                    $application['faculty']
                    ?: 'Not provided'
                ) ?>

            </p>


            <p>

                <strong>
                    Year of Study
                </strong>

                <br>

                <?php if (
                    $application['year_of_study']
                ): ?>

                    Year
                    <?= e(
                        $application['year_of_study']
                    ) ?>

                <?php else: ?>

                    Not provided

                <?php endif; ?>

            </p>

        </section>



        <section>

            <h2>
                Scholarship Information
            </h2>


            <p>

                <strong>
                    Scholarship
                </strong>

                <br>

                <?= e($application['title']) ?>

            </p>


            <p>

                <strong>
                    Provider
                </strong>

                <br>

                <?= e($application['provider']) ?>

            </p>


            <p>

                <strong>
                    Scholarship Amount
                </strong>

                <br>

                <?= e(
                    format_money(
                        $application['amount']
                    )
                ) ?>

            </p>


            <p>

                <strong>
                    Application Deadline
                </strong>

                <br>

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

                <strong>
                    Scholarship Status
                </strong>

                <br>

                <?= e(
                    $application['scholarship_status']
                ) ?>

            </p>

        </section>


    </div>


    <hr>


    <div class="detail-grid">


        <section>

            <h2>
                Application Information
            </h2>


            <p>

                <strong>
                    GPA
                </strong>

                <br>

                <?= e(
                    number_format(
                        (float) $application['gpa'],
                        2
                    )
                ) ?>

            </p>


            <p>

                <strong>
                    Annual Household Income
                </strong>

                <br>

                <?= e(
                    format_money(
                        $application['household_income']
                    )
                ) ?>

            </p>


            <p>

                <strong>
                    Application Date
                </strong>

                <br>

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

                <strong>
                    Current Status
                </strong>

                <br>

                <span
                    class="badge <?= e(
                        strtolower(
                            $application['status']
                        )
                    ) ?>"
                >

                    <?= e(
                        $application['status']
                    ) ?>

                </span>

            </p>


            <p>

                <strong>
                    Reviewed Date
                </strong>

                <br>

                <?php if (
                    $application['reviewed_at']
                ): ?>

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


        <section>

            <h2>
                Supporting Document
            </h2>


            <?php if (
                $application['document_path']
            ): ?>

                <p>
                    A supporting document was submitted with
                    this application.
                </p>

                <a
                    class="button"
                    target="_blank"
                    rel="noopener"
                    href="<?= BASE_URL ?>/download_document.php?application_id=<?= (int) $application['id'] ?>"
                >
                    Open Supporting Document
                </a>

            <?php else: ?>

                <div class="note">

                    No supporting document was uploaded.

                </div>

            <?php endif; ?>

        </section>


    </div>


    <hr>


    <section>

        <h2>
            Personal Statement
        </h2>

        <div class="note">

            <?= nl2br(
                e(
                    $application[
                        'personal_statement'
                    ]
                )
            ) ?>

        </div>

    </section>


    <hr>


    <?php foreach (
        $errors
        as $error
    ): ?>

        <div class="alert error">

            <?= e($error) ?>

        </div>

    <?php endforeach; ?>


    <section>

        <h2>
            Administrator Decision
        </h2>


        <form
            method="post"
            action="<?= BASE_URL ?>/admin/applications.php?mode=review"
            class="form-grid"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >


            <input
                type="hidden"
                name="id"
                value="<?= (int) $id ?>"
            >


            <label>

                Application Status

                <select
                    name="status"
                    required
                >

                    <option
                        value="Pending"
                        <?= $application['status']
                            === 'Pending'
                            ? 'selected'
                            : '' ?>
                    >
                        Pending
                    </option>


                    <option
                        value="Approved"
                        <?= $application['status']
                            === 'Approved'
                            ? 'selected'
                            : '' ?>
                    >
                        Approved
                    </option>


                    <option
                        value="Rejected"
                        <?= $application['status']
                            === 'Rejected'
                            ? 'selected'
                            : '' ?>
                    >
                        Rejected
                    </option>

                </select>

            </label>


            <label class="full">

                Administrator Comment

                <textarea
                    name="admin_comment"
                    rows="6"
                    maxlength="5000"
                    placeholder="Enter the reason for the decision or any comments for the student."
                ><?= e(
                    $application['admin_comment']
                ) ?></textarea>

            </label>


            <div class="full">

                <button type="submit">

                    Save Decision

                </button>

                <a
                    class="button small"
                    href="<?= BASE_URL ?>/admin/applications.php"
                >
                    Cancel
                </a>

            </div>


        </form>

    </section>


</section>


<?php

include __DIR__ . '/../includes/footer.php';

?>
<?php exit; } ?>

<?php

require_once __DIR__ . '/../functions.php';

require_admin();

$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';

$allowedStatuses = [
    'all',
    'Pending',
    'Approved',
    'Rejected'
];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

/*
 * Application statistics
 */
$countResult = $conn->query(
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'Pending') AS pending,
        SUM(status = 'Approved') AS approved,
        SUM(status = 'Rejected') AS rejected
     FROM applications"
);

$counts = $countResult->fetch_assoc();

$totalApplications = (int) ($counts['total'] ?? 0);
$pendingApplications = (int) ($counts['pending'] ?? 0);
$approvedApplications = (int) ($counts['approved'] ?? 0);
$rejectedApplications = (int) ($counts['rejected'] ?? 0);

/*
 * Search and filter applications.
 */
if ($search !== '' && $statusFilter !== 'all') {

    $like = '%' . $search . '%';

    $statement = $conn->prepare(
        'SELECT
            a.id,
            a.gpa,
            a.household_income,
            a.status,
            a.applied_at,
            a.reviewed_at,
            u.full_name,
            u.username,
            u.email,
            s.title,
            s.provider
         FROM applications AS a

         INNER JOIN users AS u
            ON u.id = a.user_id

         INNER JOIN scholarships AS s
            ON s.id = a.scholarship_id

         WHERE
            (
                u.full_name LIKE ?
                OR u.username LIKE ?
                OR u.email LIKE ?
                OR s.title LIKE ?
                OR s.provider LIKE ?
            )
            AND a.status = ?

         ORDER BY a.applied_at DESC'
    );

    $statement->bind_param(
        'ssssss',
        $like,
        $like,
        $like,
        $like,
        $like,
        $statusFilter
    );

    $statement->execute();

    $result = $statement->get_result();

} elseif ($search !== '') {

    $like = '%' . $search . '%';

    $statement = $conn->prepare(
        'SELECT
            a.id,
            a.gpa,
            a.household_income,
            a.status,
            a.applied_at,
            a.reviewed_at,
            u.full_name,
            u.username,
            u.email,
            s.title,
            s.provider
         FROM applications AS a

         INNER JOIN users AS u
            ON u.id = a.user_id

         INNER JOIN scholarships AS s
            ON s.id = a.scholarship_id

         WHERE
            u.full_name LIKE ?
            OR u.username LIKE ?
            OR u.email LIKE ?
            OR s.title LIKE ?
            OR s.provider LIKE ?

         ORDER BY a.applied_at DESC'
    );

    $statement->bind_param(
        'sssss',
        $like,
        $like,
        $like,
        $like,
        $like
    );

    $statement->execute();

    $result = $statement->get_result();

} elseif ($statusFilter !== 'all') {

    $statement = $conn->prepare(
        'SELECT
            a.id,
            a.gpa,
            a.household_income,
            a.status,
            a.applied_at,
            a.reviewed_at,
            u.full_name,
            u.username,
            u.email,
            s.title,
            s.provider
         FROM applications AS a

         INNER JOIN users AS u
            ON u.id = a.user_id

         INNER JOIN scholarships AS s
            ON s.id = a.scholarship_id

         WHERE a.status = ?

         ORDER BY a.applied_at DESC'
    );

    $statement->bind_param(
        's',
        $statusFilter
    );

    $statement->execute();

    $result = $statement->get_result();

} else {

    $result = $conn->query(
        'SELECT
            a.id,
            a.gpa,
            a.household_income,
            a.status,
            a.applied_at,
            a.reviewed_at,
            u.full_name,
            u.username,
            u.email,
            s.title,
            s.provider
         FROM applications AS a

         INNER JOIN users AS u
            ON u.id = a.user_id

         INNER JOIN scholarships AS s
            ON s.id = a.scholarship_id

         ORDER BY a.applied_at DESC'
    );
}

$resultCount = $result->num_rows;

$pageTitle = 'Review Applications';

include __DIR__ . '/../includes/header.php';

?>

<div class="page-heading">

    <div>

        <p class="eyebrow">
            Administration
        </p>

        <h1>
            Scholarship Applications
        </h1>

        <p>
            Review student applications and record scholarship decisions.
        </p>

    </div>

</div>


<div class="stats-grid">

    <div class="stat">

        <strong>
            <?= $totalApplications ?>
        </strong>

        <span>
            Total Applications
        </span>

    </div>

    <div class="stat">

        <strong>
            <?= $pendingApplications ?>
        </strong>

        <span>
            Pending
        </span>

    </div>

    <div class="stat">

        <strong>
            <?= $approvedApplications ?>
        </strong>

        <span>
            Approved
        </span>

    </div>

    <div class="stat">

        <strong>
            <?= $rejectedApplications ?>
        </strong>

        <span>
            Rejected
        </span>

    </div>

</div>


<section class="card">

    <form method="get" class="search-form">

        <input
            type="text"
            name="search"
            value="<?= e($search) ?>"
            placeholder="Search student or scholarship"
        >

        <select name="status">

            <option
                value="all"
                <?= $statusFilter === 'all'
                    ? 'selected'
                    : '' ?>
            >
                All statuses
            </option>

            <option
                value="Pending"
                <?= $statusFilter === 'Pending'
                    ? 'selected'
                    : '' ?>
            >
                Pending
            </option>

            <option
                value="Approved"
                <?= $statusFilter === 'Approved'
                    ? 'selected'
                    : '' ?>
            >
                Approved
            </option>

            <option
                value="Rejected"
                <?= $statusFilter === 'Rejected'
                    ? 'selected'
                    : '' ?>
            >
                Rejected
            </option>

        </select>

        <button type="submit">
            Search
        </button>

        <?php if (
            $search !== ''
            || $statusFilter !== 'all'
        ): ?>

            <a
                class="button small"
                href="<?= BASE_URL ?>/admin/applications.php"
            >
                Clear
            </a>

        <?php endif; ?>

    </form>

</section>


<section class="card">

    <strong>
        <?= (int) $resultCount ?>
    </strong>

    application<?= $resultCount === 1 ? '' : 's' ?> found.

</section>


<div class="table-wrap">

    <table>

        <thead>

            <tr>

                <th>
                    Student
                </th>

                <th>
                    Scholarship
                </th>

                <th>
                    GPA
                </th>

                <th>
                    Household Income
                </th>

                <th>
                    Applied
                </th>

                <th>
                    Status
                </th>

                <th>
                    Action
                </th>

            </tr>

        </thead>

        <tbody>

            <?php if ($resultCount === 0): ?>

                <tr>

                    <td colspan="7">
                        No applications found.
                    </td>

                </tr>

            <?php endif; ?>


            <?php while (
                $application = $result->fetch_assoc()
            ): ?>

                <tr>

                    <td>

                        <strong>
                            <?= e($application['full_name']) ?>
                        </strong>

                        <br>

                        <small>
                            <?= e($application['username']) ?>
                        </small>

                        <br>

                        <small>
                            <?= e($application['email']) ?>
                        </small>

                    </td>


                    <td>

                        <strong>
                            <?= e($application['title']) ?>
                        </strong>

                        <br>

                        <small>
                            <?= e($application['provider']) ?>
                        </small>

                    </td>


                    <td>

                        <?= e(
                            number_format(
                                (float) $application['gpa'],
                                2
                            )
                        ) ?>

                    </td>


                    <td>

                        <?= e(
                            format_money(
                                $application['household_income']
                            )
                        ) ?>

                    </td>


                    <td>

                        <?= e(
                            date(
                                'd M Y',
                                strtotime(
                                    $application['applied_at']
                                )
                            )
                        ) ?>

                    </td>


                    <td>

                        <span
                            class="badge <?= e(
                                strtolower(
                                    $application['status']
                                )
                            ) ?>"
                        >

                            <?= e($application['status']) ?>

                        </span>

                    </td>


                    <td>

                        <a
                            class="button small"
                            href="<?= BASE_URL ?>/admin/applications.php?mode=review&id=<?= (int) $application['id'] ?>"
                        >
                            Review
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

</div>

<?php

include __DIR__ . '/../includes/footer.php';

?>