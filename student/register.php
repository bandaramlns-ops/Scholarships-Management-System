<?php

// Load reusable functions, session settings and database connection.
require_once __DIR__ . '/../functions.php';

// A logged-in user does not need to register again.
if (is_logged_in()) {
    redirect('/home.php');
}

// This array stores validation error messages.
$errors = [];

// Default values allow the form to retain entered information after an error.
$username = '';
$fullName = '';
$email = '';
$phone = '';
$faculty = '';
$yearOfStudy = '';

// Process the form only when the Register button is clicked.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify that the form request came from this website.
    verify_csrf();

    // Read and clean the submitted form values.
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $faculty = trim($_POST['faculty'] ?? '');
    $yearOfStudy = trim($_POST['year_of_study'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    /*
     * Validate the username.
     * It must contain 3–30 letters, numbers or underscores.
     */
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] =
            'Username must contain 3–30 letters, numbers or underscores.';
    }

    // Full name cannot be empty.
    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }

    // Confirm that the email follows a valid email format.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    /*
     * The phone number is optional.
     * When entered, it may contain numbers, spaces, + and - signs.
     */
    if (
        $phone !== '' &&
        !preg_match('/^[0-9+\-\s]{7,25}$/', $phone)
    ) {
        $errors[] = 'Enter a valid phone number.';
    }

    // Faculty must be selected.
    if ($faculty === '') {
        $errors[] = 'Faculty is required.';
    }

    // Only the listed years are accepted.
    $allowedYears = ['1', '2', '3', '4', '5'];

    if (!in_array($yearOfStudy, $allowedYears, true)) {
        $errors[] = 'Select a valid year of study.';
    }

    // Password must contain at least six characters.
    if (strlen($password) < 6) {
        $errors[] = 'Password must contain at least 6 characters.';
    }

    // Both password fields must contain the same value.
    if ($password !== $confirmPassword) {
        $errors[] = 'Password and confirmation password do not match.';
    }

    /*
     * Check whether the username or email is already registered.
     * This query runs only when the earlier validation finds no errors.
     */
    if (empty($errors)) {
        $checkStatement = $conn->prepare(
            'SELECT id
             FROM users
             WHERE username = ? OR email = ?
             LIMIT 1'
        );

        $checkStatement->bind_param(
            'ss',
            $username,
            $email
        );

        $checkStatement->execute();

        $existingUser = $checkStatement
            ->get_result()
            ->fetch_assoc();

        if ($existingUser) {
            $errors[] = 'That username or email is already registered.';
        }
    }

    /*
     * Insert the student only when every validation test passes.
     */
    if (empty($errors)) {

        // Convert the password into a secure one-way hash.
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        // Public registration always creates a student account.
        $role = 'student';

        $insertStatement = $conn->prepare(
            'INSERT INTO users
             (
                 username,
                 password,
                 full_name,
                 email,
                 phone,
                 faculty,
                 year_of_study,
                 role
             )
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $insertStatement->bind_param(
            'ssssssss',
            $username,
            $passwordHash,
            $fullName,
            $email,
            $phone,
            $faculty,
            $yearOfStudy,
            $role
        );

        if ($insertStatement->execute()) {
            set_flash(
                'success',
                'Registration successful. You can now log in.'
            );

            redirect('/index.php');
        } else {
            $errors[] =
                'Registration could not be completed. Please try again.';
        }
    }
}

// Set the browser-page title.
$pageTitle = 'Student Registration';

// Add the common page header and navigation.
include __DIR__ . '/../includes/header.php';

?>

<section class="auth-card wide">

    <h1>Create Student Account</h1>

    <p>
        Complete the form below to create a scholarship applicant account.
    </p>

    <!-- Display every server-side validation error. -->
    <?php foreach ($errors as $error): ?>
        <div class="alert error">
            <?= e($error) ?>
        </div>
    <?php endforeach; ?>

    <form method="post" class="form-grid">

        <!-- Security token used to prevent forged form submissions. -->
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <label>
            Username

            <input
                type="text"
                name="username"
                value="<?= e($username) ?>"
                minlength="3"
                maxlength="30"
                autocomplete="username"
                required
            >
        </label>

        <label>
            Full name

            <input
                type="text"
                name="full_name"
                value="<?= e($fullName) ?>"
                maxlength="100"
                autocomplete="name"
                required
            >
        </label>

        <label>
            Email

            <input
                type="email"
                name="email"
                value="<?= e($email) ?>"
                maxlength="120"
                autocomplete="email"
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
                autocomplete="tel"
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
                required
            >
        </label>

        <label>
            Year of study

            <select name="year_of_study" required>
                <option value="">Select year</option>

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

        <label>
            Password

            <input
                type="password"
                name="password"
                minlength="6"
                autocomplete="new-password"
                required
            >
        </label>

        <label>
            Confirm password

            <input
                type="password"
                name="confirm_password"
                minlength="6"
                autocomplete="new-password"
                required
            >
        </label>

        <button type="submit">
            Register
        </button>

    </form>

</section>

<?php

// Add the common footer and JavaScript file.
include __DIR__ . '/../includes/footer.php';

?>