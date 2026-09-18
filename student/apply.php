<?php

require_once __DIR__ . '/../functions.php';

// Only logged-in users can access this page.
require_login();

// Administrators cannot submit applications.
if (is_admin()) {
    http_response_code(403);
    exit('Administrators cannot submit scholarship applications.');
}

// Read the scholarship ID from the URL or submitted form.
$scholarshipId =
    filter_input(INPUT_GET, 'scholarship_id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'scholarship_id', FILTER_VALIDATE_INT);

// Return to the scholarship list when the ID is invalid.
if (!$scholarshipId) {
    set_flash('error', 'Invalid scholarship selection.');
    redirect('/student/scholarships.php');
}

// Retrieve the selected scholarship.
$scholarshipStatement = $conn->prepare(
    'SELECT *
     FROM scholarships
     WHERE id = ?
     LIMIT 1'
);

$scholarshipStatement->bind_param('i', $scholarshipId);
$scholarshipStatement->execute();

$scholarship = $scholarshipStatement
    ->get_result()
    ->fetch_assoc();

// Stop if the scholarship does not exist.
if (!$scholarship) {
    http_response_code(404);
    exit('Scholarship not found.');
}

// Block applications to closed or expired scholarships.
$today = date('Y-m-d');

if (
    $scholarship['status'] !== 'Open'
    || $scholarship['deadline'] < $today
) {
    set_flash(
        'error',
        'This scholarship is not accepting applications.'
    );

    redirect('/student/scholarship_view.php?id=' . $scholarshipId);
}

// Check whether the student has already applied.
$userId = (int) $_SESSION['user_id'];

$duplicateStatement = $conn->prepare(
    'SELECT id
     FROM applications
     WHERE scholarship_id = ?
     AND user_id = ?
     LIMIT 1'
);

$duplicateStatement->bind_param(
    'ii',
    $scholarshipId,
    $userId
);

$duplicateStatement->execute();

$existingApplication = $duplicateStatement
    ->get_result()
    ->fetch_assoc();

if ($existingApplication) {
    set_flash(
        'error',
        'You have already applied for this scholarship.'
    );

    redirect('/student/scholarship_view.php?id=' . $scholarshipId);
}

// Form data and error messages.
$errors = [];

$gpa = '';
$householdIncome = '';
$personalStatement = '';

// Process the submitted application.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $gpa = trim($_POST['gpa'] ?? '');
    $householdIncome = trim(
        $_POST['household_income'] ?? ''
    );
    $personalStatement = trim(
        $_POST['personal_statement'] ?? ''
    );

    $documentPath = null;

    // Validate GPA.
    if (
        !is_numeric($gpa)
        || (float) $gpa < 0
        || (float) $gpa > 4
    ) {
        $errors[] = 'GPA must be between 0.00 and 4.00.';
    }

    // Validate household income.
    if (
        !is_numeric($householdIncome)
        || (float) $householdIncome < 0
    ) {
        $errors[] = 'Enter a valid household income.';
    }

    // Validate personal statement.
    if (strlen($personalStatement) < 50) {
        $errors[] =
            'Personal statement must contain at least 50 characters.';
    }

    if (strlen($personalStatement) > 5000) {
        $errors[] =
            'Personal statement must not exceed 5000 characters.';
    }

    /*
     * Validate the supporting document when a file is selected.
     */
    if (
        isset($_FILES['supporting_document'])
        && $_FILES['supporting_document']['error']
            !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['supporting_document'];

        $allowedExtensions = [
            'pdf',
            'jpg',
            'jpeg',
            'png'
        ];

        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png'
        ];

        $extension = strtolower(
            pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )
        );

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] =
                'The supporting document could not be uploaded.';
        } elseif ($file['size'] > MAX_UPLOAD_BYTES) {
            $errors[] =
                'The supporting document must be 2 MB or smaller.';
        } elseif (
            !in_array(
                $extension,
                $allowedExtensions,
                true
            )
        ) {
            $errors[] =
                'Allowed document types are PDF, JPG, JPEG and PNG.';
        } else {
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);

            $mimeType = $fileInfo->file(
                $file['tmp_name']
            );

            if (
                !in_array(
                    $mimeType,
                    $allowedMimeTypes,
                    true
                )
            ) {
                $errors[] =
                    'The uploaded file content is not accepted.';
            }
        }
    }

    /*
     * Save the uploaded document only after validation succeeds.
     */
    if (
        empty($errors)
        && isset($_FILES['supporting_document'])
        && $_FILES['supporting_document']['error']
            !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['supporting_document'];

        $extension = strtolower(
            pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )
        );

        $newFileName =
            'application_user_'
            . $userId
            . '_'
            . time()
            . '_'
            . bin2hex(random_bytes(5))
            . '.'
            . $extension;

        $destination = UPLOAD_DIR . $newFileName;

        if (
            move_uploaded_file(
                $file['tmp_name'],
                $destination
            )
        ) {
            $documentPath =
                'uploads/' . $newFileName;
        } else {
            $errors[] =
                'The supporting document could not be saved.';
        }
    }

    /*
     * Insert the application after every validation succeeds.
     */
    if (empty($errors)) {

        $gpaValue = (float) $gpa;
        $incomeValue = (float) $householdIncome;

        $insertStatement = $conn->prepare(
            'INSERT INTO applications
             (
                 scholarship_id,
                 user_id,
                 gpa,
                 household_income,
                 personal_statement,
                 document_path
             )
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $insertStatement->bind_param(
            'iiddss',
            $scholarshipId,
            $userId,
            $gpaValue,
            $incomeValue,
            $personalStatement,
            $documentPath
        );

        try {
            $insertStatement->execute();

            set_flash(
                'success',
                'Your scholarship application was submitted successfully.'
            );

            redirect('/student/my_applications.php');

        } catch (mysqli_sql_exception $exception) {

            // Remove the uploaded document when insertion fails.
            if (
                $documentPath
                && file_exists(__DIR__ . '/' . $documentPath)
            ) {
                unlink(__DIR__ . '/' . $documentPath);
            }

            if ((int) $exception->getCode() === 1062) {
                $errors[] =
                    'You have already applied for this scholarship.';
            } else {
                $errors[] =
                    'The application could not be submitted. Please try again.';
            }
        }
    }
}

$pageTitle = 'Apply for ' . $scholarship['title'];

include __DIR__ . '/../includes/header.php';

?>

<section class="form-card">

    <p class="eyebrow">Scholarship application</p>

    <h1>
        Apply for <?= e($scholarship['title']) ?>
    </h1>

    <div class="note">

        <p>
            <strong>Provider:</strong>
            <?= e($scholarship['provider']) ?>
        </p>

        <p>
            <strong>Amount:</strong>
            <?= e(format_money($scholarship['amount'])) ?>
        </p>

        <p>
            <strong>Deadline:</strong>
            <?= e(
                date(
                    'd F Y',
                    strtotime($scholarship['deadline'])
                )
            ) ?>
        </p>

    </div>

    <?php foreach ($errors as $error): ?>

        <div class="alert error">
            <?= e($error) ?>
        </div>

    <?php endforeach; ?>

    <form
        method="post"
        enctype="multipart/form-data"
        class="form-grid"
    >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <input
            type="hidden"
            name="scholarship_id"
            value="<?= (int) $scholarshipId ?>"
        >

        <label>
            GPA

            <input
                type="number"
                name="gpa"
                min="0"
                max="4"
                step="0.01"
                value="<?= e($gpa) ?>"
                placeholder="Example: 3.50"
                required
            >
        </label>

        <label>
            Annual household income (LKR)

            <input
                type="number"
                name="household_income"
                min="0"
                step="0.01"
                value="<?= e($householdIncome) ?>"
                placeholder="Example: 600000"
                required
            >
        </label>

        <label class="full">
            Personal statement

            <textarea
                name="personal_statement"
                rows="8"
                minlength="50"
                maxlength="5000"
                placeholder="Explain why you are applying and why you are suitable for this scholarship."
                required
            ><?= e($personalStatement) ?></textarea>
        </label>

        <label class="full">
            Supporting document

            <input
                type="file"
                name="supporting_document"
                accept=".pdf,.jpg,.jpeg,.png"
            >

            <small>
                Optional. Accepted formats: PDF, JPG, JPEG and PNG.
                Maximum size: 2 MB.
            </small>
        </label>

        <div class="full">

            <button type="submit">
                Submit Application
            </button>

            <a
                class="button small"
                href="<?= BASE_URL ?>/student/scholarship_view.php?id=<?= (int) $scholarshipId ?>"
            >
                Cancel
            </a>

        </div>

    </form>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>