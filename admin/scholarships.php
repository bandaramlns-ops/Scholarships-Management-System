<?php
$mode = $_GET['mode'] ?? 'list';
?>

<?php if ($mode === 'form') { ?>
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

$editing = (bool) $id;

$record = [
    'title' => '',
    'provider' => '',
    'description' => '',
    'eligibility' => '',
    'amount' => '',
    'deadline' => '',
    'status' => 'Open'
];

if ($editing) {

    $statement = $conn->prepare(
        'SELECT *
         FROM scholarships
         WHERE id = ?
         LIMIT 1'
    );

    $statement->bind_param(
        'i',
        $id
    );

    $statement->execute();

    $record = $statement
        ->get_result()
        ->fetch_assoc();

    if (!$record) {
        http_response_code(404);
        exit('Scholarship not found.');
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $title = trim(
        $_POST['title'] ?? ''
    );

    $provider = trim(
        $_POST['provider'] ?? ''
    );

    $description = trim(
        $_POST['description'] ?? ''
    );

    $eligibility = trim(
        $_POST['eligibility'] ?? ''
    );

    $amount = trim(
        $_POST['amount'] ?? ''
    );

    $deadline =
        $_POST['deadline'] ?? '';

    $status =
        $_POST['status'] ?? 'Open';

    if ($title === '') {
        $errors[] =
            'Scholarship title is required.';
    }

    if (strlen($title) > 160) {
        $errors[] =
            'Scholarship title is too long.';
    }

    if ($provider === '') {
        $errors[] =
            'Provider is required.';
    }

    if (strlen($provider) > 160) {
        $errors[] =
            'Provider name is too long.';
    }

    if ($description === '') {
        $errors[] =
            'Description is required.';
    }

    if ($eligibility === '') {
        $errors[] =
            'Eligibility requirements are required.';
    }

    if (
        !is_numeric($amount)
        || (float) $amount < 0
    ) {
        $errors[] =
            'Enter a valid scholarship amount.';
    }

    if ($deadline === '') {
        $errors[] =
            'Application deadline is required.';
    }

    if (
        !in_array(
            $status,
            ['Open', 'Closed'],
            true
        )
    ) {
        $errors[] =
            'Select a valid scholarship status.';
    }

    if (empty($errors)) {

        $amountValue =
            (float) $amount;

        if ($editing) {

            $updateStatement =
                $conn->prepare(
                    'UPDATE scholarships
                     SET
                         title = ?,
                         provider = ?,
                         description = ?,
                         eligibility = ?,
                         amount = ?,
                         deadline = ?,
                         status = ?
                     WHERE id = ?'
                );

            $updateStatement->bind_param(
                'ssssdssi',
                $title,
                $provider,
                $description,
                $eligibility,
                $amountValue,
                $deadline,
                $status,
                $id
            );

            $updateStatement->execute();

            set_flash(
                'success',
                'Scholarship updated successfully.'
            );

        } else {

            $createdBy =
                (int) $_SESSION['user_id'];

            $insertStatement =
                $conn->prepare(
                    'INSERT INTO scholarships
                     (
                         title,
                         provider,
                         description,
                         eligibility,
                         amount,
                         deadline,
                         status,
                         created_by
                     )
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );

            $insertStatement->bind_param(
                'ssssdssi',
                $title,
                $provider,
                $description,
                $eligibility,
                $amountValue,
                $deadline,
                $status,
                $createdBy
            );

            $insertStatement->execute();

            set_flash(
                'success',
                'Scholarship added successfully.'
            );
        }

        redirect(
            '/admin/scholarships.php'
        );
    }

    $record = [
        'title' => $title,
        'provider' => $provider,
        'description' => $description,
        'eligibility' => $eligibility,
        'amount' => $amount,
        'deadline' => $deadline,
        'status' => $status
    ];
}

$pageTitle =
    $editing
    ? 'Edit Scholarship'
    : 'Add Scholarship';

include __DIR__ . '/../includes/header.php';

?>

<section class="form-card">

    <p class="eyebrow">
        Administration
    </p>

    <h1>
        <?= $editing
            ? 'Edit Scholarship'
            : 'Add Scholarship' ?>
    </h1>

    <?php foreach ($errors as $error): ?>

        <div class="alert error">
            <?= e($error) ?>
        </div>

    <?php endforeach; ?>

    <form
        method="post"
        action="<?= BASE_URL ?>/admin/scholarships.php?mode=form"
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
            Scholarship title

            <input
                type="text"
                name="title"
                value="<?= e($record['title']) ?>"
                maxlength="160"
                required
            >
        </label>

        <label>
            Provider

            <input
                type="text"
                name="provider"
                value="<?= e($record['provider']) ?>"
                maxlength="160"
                required
            >
        </label>

        <label>
            Amount (LKR)

            <input
                type="number"
                name="amount"
                min="0"
                step="0.01"
                value="<?= e($record['amount']) ?>"
                required
            >
        </label>

        <label>
            Application deadline

            <input
                type="date"
                name="deadline"
                value="<?= e($record['deadline']) ?>"
                required
            >
        </label>

        <label>
            Status

            <select
                name="status"
                required
            >

                <option
                    value="Open"
                    <?= $record['status'] === 'Open'
                        ? 'selected'
                        : '' ?>
                >
                    Open
                </option>

                <option
                    value="Closed"
                    <?= $record['status'] === 'Closed'
                        ? 'selected'
                        : '' ?>
                >
                    Closed
                </option>

            </select>

        </label>

        <label class="full">
            Description

            <textarea
                name="description"
                rows="6"
                required
            ><?= e($record['description']) ?></textarea>

        </label>

        <label class="full">
            Eligibility requirements

            <textarea
                name="eligibility"
                rows="6"
                required
            ><?= e($record['eligibility']) ?></textarea>

        </label>

        <div class="full">

            <button type="submit">
                Save Scholarship
            </button>

            <a
                class="button small"
                href="<?= BASE_URL ?>/admin/scholarships.php"
            >
                Cancel
            </a>

        </div>

    </form>

</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>
<?php exit; } ?>

<?php if ($mode === 'delete') { ?>
<?php

require_once __DIR__ . '/../functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request method.');
}

verify_csrf();

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {
    set_flash(
        'error',
        'Invalid scholarship selection.'
    );

    redirect(
        '/admin/scholarships.php'
    );
}

// Find uploaded documents belonging to applications
// for this scholarship.
$documentStatement =
    $conn->prepare(
        'SELECT document_path
         FROM applications
         WHERE scholarship_id = ?
         AND document_path IS NOT NULL'
    );

$documentStatement->bind_param(
    'i',
    $id
);

$documentStatement->execute();

$documentResult =
    $documentStatement->get_result();

$documentPaths = [];

while (
    $document =
        $documentResult->fetch_assoc()
) {

    if ($document['document_path']) {
        $documentPaths[] =
            $document['document_path'];
    }
}

try {

    $deleteStatement =
        $conn->prepare(
            'DELETE FROM scholarships
             WHERE id = ?'
        );

    $deleteStatement->bind_param(
        'i',
        $id
    );

    $deleteStatement->execute();

    if (
        $deleteStatement->affected_rows === 0
    ) {

        set_flash(
            'error',
            'Scholarship not found.'
        );

        redirect(
            '/admin/scholarships.php'
        );
    }

    // Remove uploaded files belonging to
    // deleted applications.
    foreach (
        $documentPaths
        as $documentPath
    ) {

        $fileName =
            basename($documentPath);

        $filePath =
            UPLOAD_DIR . $fileName;

        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    set_flash(
        'success',
        'Scholarship deleted successfully.'
    );

} catch (
    mysqli_sql_exception $exception
) {

    set_flash(
        'error',
        'The scholarship could not be deleted.'
    );
}

redirect(
    '/admin/scholarships.php'
);
exit;
}
?>

<?php

require_once __DIR__ . '/../functions.php';

require_admin();

$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';

if (!in_array($statusFilter, ['all', 'Open', 'Closed'], true)) {
    $statusFilter = 'all';
}

if ($search !== '' && $statusFilter !== 'all') {

    $like = '%' . $search . '%';

    $statement = $conn->prepare(
        'SELECT
             s.*,
             u.username AS creator,
             COUNT(a.id) AS application_count
         FROM scholarships AS s
         LEFT JOIN users AS u
             ON u.id = s.created_by
         LEFT JOIN applications AS a
             ON a.scholarship_id = s.id
         WHERE
             (
                 s.title LIKE ?
                 OR s.provider LIKE ?
             )
             AND s.status = ?
         GROUP BY s.id
         ORDER BY s.created_at DESC'
    );

    $statement->bind_param(
        'sss',
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
             s.*,
             u.username AS creator,
             COUNT(a.id) AS application_count
         FROM scholarships AS s
         LEFT JOIN users AS u
             ON u.id = s.created_by
         LEFT JOIN applications AS a
             ON a.scholarship_id = s.id
         WHERE
             s.title LIKE ?
             OR s.provider LIKE ?
         GROUP BY s.id
         ORDER BY s.created_at DESC'
    );

    $statement->bind_param(
        'ss',
        $like,
        $like
    );

    $statement->execute();

    $result = $statement->get_result();

} elseif ($statusFilter !== 'all') {

    $statement = $conn->prepare(
        'SELECT
             s.*,
             u.username AS creator,
             COUNT(a.id) AS application_count
         FROM scholarships AS s
         LEFT JOIN users AS u
             ON u.id = s.created_by
         LEFT JOIN applications AS a
             ON a.scholarship_id = s.id
         WHERE s.status = ?
         GROUP BY s.id
         ORDER BY s.created_at DESC'
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
             s.*,
             u.username AS creator,
             COUNT(a.id) AS application_count
         FROM scholarships AS s
         LEFT JOIN users AS u
             ON u.id = s.created_by
         LEFT JOIN applications AS a
             ON a.scholarship_id = s.id
         GROUP BY s.id
         ORDER BY s.created_at DESC'
    );
}

$resultCount = $result->num_rows;

$pageTitle = 'Manage Scholarships';

include __DIR__ . '/../includes/header.php';

?>

<div class="page-heading">

    <div>

        <p class="eyebrow">
            Administration
        </p>

        <h1>
            Manage Scholarships
        </h1>

        <p>
            Add, edit, open, close and delete scholarship opportunities.
        </p>

    </div>

    <a
        class="button"
        href="<?= BASE_URL ?>/admin/scholarships.php?mode=form"
    >
        Add Scholarship
    </a>

</div>

<section class="card">

    <form method="get" class="search-form">

        <input
            type="text"
            name="search"
            value="<?= e($search) ?>"
            placeholder="Search scholarship or provider"
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
                value="Open"
                <?= $statusFilter === 'Open'
                    ? 'selected'
                    : '' ?>
            >
                Open
            </option>

            <option
                value="Closed"
                <?= $statusFilter === 'Closed'
                    ? 'selected'
                    : '' ?>
            >
                Closed
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
                href="<?= BASE_URL ?>/admin/scholarships.php"
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

    scholarship<?= $resultCount === 1 ? '' : 's' ?> found.

</section>

<div class="table-wrap">

    <table>

        <thead>

            <tr>
                <th>Scholarship</th>
                <th>Provider</th>
                <th>Amount</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Applications</th>
                <th>Created By</th>
                <th>Actions</th>
            </tr>

        </thead>

        <tbody>

            <?php if ($resultCount === 0): ?>

                <tr>
                    <td colspan="8">
                        No scholarships found.
                    </td>
                </tr>

            <?php endif; ?>

            <?php while (
                $scholarship = $result->fetch_assoc()
            ): ?>

                <?php

                $deadlinePassed =
                    $scholarship['deadline']
                    < date('Y-m-d');

                ?>

                <tr>

                    <td>

                        <strong>
                            <?= e($scholarship['title']) ?>
                        </strong>

                        <?php if ($deadlinePassed): ?>

                            <br>

                            <small>
                                Deadline passed
                            </small>

                        <?php endif; ?>

                    </td>

                    <td>
                        <?= e($scholarship['provider']) ?>
                    </td>

                    <td>
                        <?= e(
                            format_money(
                                $scholarship['amount']
                            )
                        ) ?>
                    </td>

                    <td>
                        <?= e(
                            date(
                                'd M Y',
                                strtotime(
                                    $scholarship['deadline']
                                )
                            )
                        ) ?>
                    </td>

                    <td>

                        <span
                            class="badge <?= e(
                                strtolower(
                                    $scholarship['status']
                                )
                            ) ?>"
                        >
                            <?= e($scholarship['status']) ?>
                        </span>

                    </td>

                    <td>
                        <?= (int)
                            $scholarship['application_count'] ?>
                    </td>

                    <td>
                        <?= e(
                            $scholarship['creator']
                            ?: 'Unknown'
                        ) ?>
                    </td>

                    <td class="actions">

                        <a
                            href="<?= BASE_URL ?>/student/scholarship_view.php?id=<?= (int) $scholarship['id'] ?>"
                        >
                            View
                        </a>

                        <a
                            href="<?= BASE_URL ?>/admin/scholarships.php?mode=form&id=<?= (int) $scholarship['id'] ?>"
                        >
                            Edit
                        </a>

                        <form
                            method="post"
                            action="<?= BASE_URL ?>/admin/scholarships.php?mode=delete"
                            class="inline-form"
                            data-confirm-form="Delete this scholarship and all related applications?"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e(csrf_token()) ?>"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $scholarship['id'] ?>"
                            >

                            <button
                                type="submit"
                                class="link-button danger-link"
                            >
                                Delete
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

</div>

<?php
include __DIR__ . '/../includes/footer.php';
?>