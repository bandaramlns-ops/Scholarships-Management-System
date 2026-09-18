<?php

require_once __DIR__ . '/../functions.php';

// Only logged-in users can access this page.
require_login();

// Get the currently logged-in user's information.
$account = current_user($conn);

// Stop access when the account cannot be found.
if (!$account) {
    session_unset();
    session_destroy();

    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$errors = [];

// Initial form values.
$fullName = $account['full_name'] ?? '';
$email = $account['email'] ?? '';
$phone = $account['phone'] ?? '';
$faculty = $account['faculty'] ?? '';
$yearOfStudy = $account['year_of_study'] ?? '';

// Process the form after clicking Save Profile.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $faculty = trim($_POST['faculty'] ?? '');
    $yearOfStudy = trim($_POST['year_of_study'] ?? '');

    // Validate full name.
    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    } elseif (strlen($fullName) > 100) {
        $errors[] = 'Full name must not exceed 100 characters.';
    }

    // Validate email.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    // Validate phone number when entered.
    if (
        $phone !== '' &&
        !preg_match('/^[0-9+\-\s]{7,25}$/', $phone)
    ) {
        $errors[] = 'Enter a valid phone number.';
    }

    // Validate faculty length.
    if (strlen($faculty) > 100) {
        $errors[] = 'Faculty name must not exceed 100 characters.';
    }

    // Validate year of study.
    $allowedYears = ['', '1', '2', '3', '4', '5'];

    if (!in_array($yearOfStudy, $allowedYears, true)) {
        $errors[] = 'Select a valid year of study.';
    }

    // Check whether another user already uses the email.
    if (empty($errors)) {
        $checkStatement = $conn->prepare(
            'SELECT id
             FROM users
             WHERE email = ?
             AND id <> ?
             LIMIT 1'
        );

        $userId = (int) $_SESSION['user_id'];

        $checkStatement->bind_param(
            'si',
            $email,
            $userId
        );

        $checkStatement->execute();

        $existingAccount = $checkStatement
            ->get_result()
            ->fetch_assoc();

        if ($existingAccount) {
            $errors[] = 'That email address is already in use.';
        }
    }

    // Update the profile when validation succeeds.
    if (empty($errors)) {
        $updateStatement = $conn->prepare(
            'UPDATE users
             SET full_name = ?,
                 email = ?,
                 phone = ?,
                 faculty = ?,
                 year_of_study = ?
             WHERE id = ?'
        );

        $userId = (int) $_SESSION['user_id'];

        $updateStatement->bind_param(
            'sssssi',
            $fullName,
            $email,
            $phone,
            $faculty,
            $yearOfStudy,
            $userId
        );

        if ($updateStatement->execute()) {
            set_flash(
                'success',
                'Profile updated successfully.'
            );

            redirect('/student/profile.php');
        }

        $errors[] = 'Profile could not be updated. Please try again.';
    }
}

$pageTitle = 'My Profile';

include __DIR__ . '/../includes/header.php';

?>

<section class="form-card">

    <h1>My Profile</h1>

    <p>
        View and update your personal and academic information.
    </p>

    <?php foreach ($errors as $error): ?>
        <div class="alert error">
            <?= e($error) ?>
        </div>
    <?php endforeach; ?>

    <form method="post" class="form-grid">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <label>
            Username

            <input
                type="text"
                value="<?= e($account['username']) ?>"
                readonly
            >
        </label>

        <label>
            Account role

            <input
                type="text"
                value="<?= e(ucfirst($account['role'])) ?>"
                readonly
            >
        </label>

        <label>
            Full name

            <input
                type="text"
                name="full_name"
                value="<?= e($fullName) ?>"
                maxlength="100"
                required
            >
        </label>

        <label>
            Email address

            <input
                type="email"
                name="email"
                value="<?= e($email) ?>"
                maxlength="120"
                required
            >
        </label>

        <label>
            Phone number

            <input
                type="text"
                name="phone"
                value="<?= e($phone) ?>"
                maxlength="25"
                placeholder="+94 77 123 4567"
            >
        </label>

        <label>
            Faculty

            <input
                type="text"
                name="faculty"
                value="<?= e($faculty) ?>"
                maxlength="100"
                placeholder="Example: Faculty of Science"
            >
        </label>

        <label>
            Year of study

            <select name="year_of_study">

                <option
                    value=""
                    <?= $yearOfStudy === '' ? 'selected' : '' ?>
                >
                    Select year
                </option>

                <option
                    value="1"
                    <?= $yearOfStudy === '1' ? 'selected' : '' ?>
                >
                    Year 1
                </option>

                <option
                    value="2"
                    <?= $yearOfStudy === '2' ? 'selected' : '' ?>
                >
                    Year 2
                </option>

                <option
                    value="3"
                    <?= $yearOfStudy === '3' ? 'selected' : '' ?>
                >
                    Year 3
                </option>

                <option
                    value="4"
                    <?= $yearOfStudy === '4' ? 'selected' : '' ?>
                >
                    Year 4
                </option>

                <option
                    value="5"
                    <?= $yearOfStudy === '5' ? 'selected' : '' ?>
                >
                    Year 5
                </option>

            </select>
        </label>

        <div class="full">
            <button type="submit">
                Save Profile
            </button>
        </div>

    </form>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>