<?php
$mode = $_GET['mode'] ?? 'list';
?>

<?php if ($mode === 'form') { ?>
<?php

require_once __DIR__ . '/../functions.php';

require_admin();

$id =
    filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

$editing = (bool) $id;

$record = [
    'username' => '',
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'faculty' => '',
    'year_of_study' => '',
    'role' => 'student',
    'is_active' => 1
];

if ($editing) {

    $statement = $conn->prepare(
        'SELECT
             id,
             username,
             full_name,
             email,
             phone,
             faculty,
             year_of_study,
             role,
             is_active
         FROM users
         WHERE id = ?
         LIMIT 1'
    );

    $statement->bind_param('i', $id);

    $statement->execute();

    $record = $statement
        ->get_result()
        ->fetch_assoc();

    if (!$record) {
        http_response_code(404);
        exit('User not found.');
    }
}

$isCurrentAccount =
    $editing
    && (int) $id === (int) $_SESSION['user_id'];

$isDefaultUser =
    $editing
    && $record['username'] === 'ucsc';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $faculty = trim($_POST['faculty'] ?? '');
    $yearOfStudy = trim($_POST['year_of_study'] ?? '');
    $role = $_POST['role'] ?? 'student';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($isDefaultUser) {
        $username = 'ucsc';
        $role = 'student';
        $isActive = 1;
        $password = '';
        $confirmPassword = '';
    }

    if ($isCurrentAccount) {
        $role = 'admin';
        $isActive = 1;
    }

    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] =
            'Username must contain 3–30 letters, numbers or underscores.';
    }

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }

    if (strlen($fullName) > 100) {
        $errors[] =
            'Full name must not exceed 100 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (
        $phone !== ''
        && !preg_match('/^[0-9+\-\s]{7,25}$/', $phone)
    ) {
        $errors[] = 'Enter a valid phone number.';
    }

    if (strlen($faculty) > 100) {
        $errors[] =
            'Faculty name must not exceed 100 characters.';
    }

    $allowedYears = ['', '1', '2', '3', '4', '5'];

    if (
        !in_array(
            $yearOfStudy,
            $allowedYears,
            true
        )
    ) {
        $errors[] = 'Select a valid year of study.';
    }

    if (!in_array($role, ['student', 'admin'], true)) {
        $errors[] = 'Select a valid account role.';
    }

    if (!$editing && strlen($password) < 6) {
        $errors[] =
            'New users require a password of at least 6 characters.';
    }

    if (
        $editing
        && $password !== ''
        && strlen($password) < 6
    ) {
        $errors[] =
            'The new password must contain at least 6 characters.';
    }

    if (
        $password !== ''
        && $password !== $confirmPassword
    ) {
        $errors[] =
            'Password and confirmation password do not match.';
    }

    if (empty($errors)) {

        if ($editing) {

            $duplicateStatement = $conn->prepare(
                'SELECT id
                 FROM users
                 WHERE
                     (
                         username = ?
                         OR email = ?
                     )
                 AND id <> ?
                 LIMIT 1'
            );

            $duplicateStatement->bind_param(
                'ssi',
                $username,
                $email,
                $id
            );

        } else {

            $duplicateStatement = $conn->prepare(
                'SELECT id
                 FROM users
                 WHERE username = ?
                 OR email = ?
                 LIMIT 1'
            );

            $duplicateStatement->bind_param(
                'ss',
                $username,
                $email
            );
        }

        $duplicateStatement->execute();

        $duplicateUser = $duplicateStatement
            ->get_result()
            ->fetch_assoc();

        if ($duplicateUser) {
            $errors[] =
                'That username or email address already exists.';
        }
    }

    if (empty($errors)) {

        try {

            if ($editing) {

                if ($password !== '') {

                    $passwordHash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $updateStatement = $conn->prepare(
                        'UPDATE users
                         SET
                             username = ?,
                             password = ?,
                             full_name = ?,
                             email = ?,
                             phone = ?,
                             faculty = ?,
                             year_of_study = ?,
                             role = ?,
                             is_active = ?
                         WHERE id = ?'
                    );

                    $updateStatement->bind_param(
                        'ssssssssii',
                        $username,
                        $passwordHash,
                        $fullName,
                        $email,
                        $phone,
                        $faculty,
                        $yearOfStudy,
                        $role,
                        $isActive,
                        $id
                    );

                } else {

                    $updateStatement = $conn->prepare(
                        'UPDATE users
                         SET
                             username = ?,
                             full_name = ?,
                             email = ?,
                             phone = ?,
                             faculty = ?,
                             year_of_study = ?,
                             role = ?,
                             is_active = ?
                         WHERE id = ?'
                    );

                    $updateStatement->bind_param(
                        'sssssssii',
                        $username,
                        $fullName,
                        $email,
                        $phone,
                        $faculty,
                        $yearOfStudy,
                        $role,
                        $isActive,
                        $id
                    );
                }

                $updateStatement->execute();

                set_flash(
                    'success',
                    'User account updated successfully.'
                );

            } else {

                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

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
                         role,
                         is_active
                     )
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );

                $insertStatement->bind_param(
                    'ssssssssi',
                    $username,
                    $passwordHash,
                    $fullName,
                    $email,
                    $phone,
                    $faculty,
                    $yearOfStudy,
                    $role,
                    $isActive
                );

                $insertStatement->execute();

                set_flash(
                    'success',
                    'User account added successfully.'
                );
            }

            redirect('/admin/users.php');

        } catch (mysqli_sql_exception $exception) {

            $errors[] =
                'The user account could not be saved.';
        }
    }

    $record = [
        'username' => $username,
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'faculty' => $faculty,
        'year_of_study' => $yearOfStudy,
        'role' => $role,
        'is_active' => $isActive
    ];
}

$pageTitle = $editing
    ? 'Edit User'
    : 'Add User';

include __DIR__ . '/../includes/header.php';

?>

<section class="form-card">

    <h1>
        <?= $editing ? 'Edit User' : 'Add User' ?>
    </h1>

    <?php if ($isDefaultUser): ?>

        <div class="note">
            The required default account must remain an active student
            with the username <strong>ucsc</strong>.
        </div>

    <?php endif; ?>

    <?php if ($isCurrentAccount): ?>

        <div class="note">
            You cannot deactivate your own account or remove your own
            administrator role.
        </div>

    <?php endif; ?>

    <?php foreach ($errors as $error): ?>

        <div class="alert error">
            <?= e($error) ?>
        </div>

    <?php endforeach; ?>

    <form method="post" action="<?= BASE_URL ?>/admin/users.php?mode=form" class="form-grid">

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
            Username

            <input
                type="text"
                name="username"
                value="<?= e($record['username']) ?>"
                minlength="3"
                maxlength="30"
                <?= $isDefaultUser ? 'readonly' : '' ?>
                required
            >
        </label>

        <label>
            Full name

            <input
                type="text"
                name="full_name"
                value="<?= e($record['full_name']) ?>"
                maxlength="100"
                required
            >
        </label>

        <label>
            Email address

            <input
                type="email"
                name="email"
                value="<?= e($record['email']) ?>"
                maxlength="120"
                required
            >
        </label>

        <label>
            Phone number

            <input
                type="text"
                name="phone"
                value="<?= e($record['phone']) ?>"
                maxlength="25"
            >
        </label>

        <label>
            Faculty

            <input
                type="text"
                name="faculty"
                value="<?= e($record['faculty']) ?>"
                maxlength="100"
            >
        </label>

        <label>
            Year of study

            <select name="year_of_study">

                <option value="">Not applicable</option>

                <?php for ($year = 1; $year <= 5; $year++): ?>

                    <option
                        value="<?= $year ?>"
                        <?= (string) $record['year_of_study']
                            === (string) $year
                            ? 'selected'
                            : '' ?>
                    >
                        Year <?= $year ?>
                    </option>

                <?php endfor; ?>

            </select>
        </label>

        <label>
            Role

            <?php if (
                $isCurrentAccount
                || $isDefaultUser
            ): ?>

                <input
                    type="text"
                    value="<?= e(ucfirst($record['role'])) ?>"
                    readonly
                >

                <input
                    type="hidden"
                    name="role"
                    value="<?= e($record['role']) ?>"
                >

            <?php else: ?>

                <select name="role">

                    <option
                        value="student"
                        <?= $record['role'] === 'student'
                            ? 'selected'
                            : '' ?>
                    >
                        Student
                    </option>

                    <option
                        value="admin"
                        <?= $record['role'] === 'admin'
                            ? 'selected'
                            : '' ?>
                    >
                        Administrator
                    </option>

                </select>

            <?php endif; ?>

        </label>

        <?php if (!$isDefaultUser): ?>

            <label>
                Password
                <?= $editing
                    ? '(leave blank to keep the current password)'
                    : '' ?>

                <input
                    type="password"
                    name="password"
                    minlength="6"
                    <?= $editing ? '' : 'required' ?>
                >
            </label>

            <label>
                Confirm password

                <input
                    type="password"
                    name="confirm_password"
                    minlength="6"
                    <?= $editing ? '' : 'required' ?>
                >
            </label>

        <?php endif; ?>

        <?php if (
            $isCurrentAccount
            || $isDefaultUser
        ): ?>

            <label class="checkbox">

                <input
                    type="checkbox"
                    checked
                    disabled
                >

                Active account

            </label>

            <input
                type="hidden"
                name="is_active"
                value="1"
            >

        <?php else: ?>

            <label class="checkbox">

                <input
                    type="checkbox"
                    name="is_active"
                    <?= !empty($record['is_active'])
                        ? 'checked'
                        : '' ?>
                >

                Active account

            </label>

        <?php endif; ?>

        <div class="full">

            <button type="submit">
                Save User
            </button>

            <a
                class="button small"
                href="<?= BASE_URL ?>/admin/users.php"
            >
                Cancel
            </a>

        </div>

    </form>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php exit; } ?>

<?php if ($mode === 'toggle') { ?>
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
    set_flash('error', 'Invalid user selection.');
    redirect('/admin/users.php');
}

if ((int) $id === (int) $_SESSION['user_id']) {
    set_flash(
        'error',
        'You cannot deactivate your own account.'
    );

    redirect('/admin/users.php');
}

$statement = $conn->prepare(
    'SELECT
         id,
         username,
         role,
         is_active
     FROM users
     WHERE id = ?
     LIMIT 1'
);

$statement->bind_param('i', $id);
$statement->execute();

$user = $statement
    ->get_result()
    ->fetch_assoc();

if (!$user) {
    set_flash('error', 'User account not found.');
    redirect('/admin/users.php');
}

if ($user['username'] === 'ucsc') {
    set_flash(
        'error',
        'The required default UCSC account cannot be deactivated.'
    );

    redirect('/admin/users.php');
}

$newStatus = $user['is_active'] ? 0 : 1;

if (
    $user['role'] === 'admin'
    && (int) $user['is_active'] === 1
    && $newStatus === 0
) {

    $adminCountResult = $conn->query(
        "SELECT COUNT(*) AS total
         FROM users
         WHERE role = 'admin'
         AND is_active = 1"
    );

    $activeAdminCount = (int) $adminCountResult
        ->fetch_assoc()['total'];

    if ($activeAdminCount <= 1) {

        set_flash(
            'error',
            'The final active administrator cannot be deactivated.'
        );

        redirect('/admin/users.php');
    }
}

$updateStatement = $conn->prepare(
    'UPDATE users
     SET is_active = ?
     WHERE id = ?'
);

$updateStatement->bind_param(
    'ii',
    $newStatus,
    $id
);

$updateStatement->execute();

set_flash(
    'success',
    $newStatus
        ? 'User account activated successfully.'
        : 'User account deactivated successfully.'
);

redirect('/admin/users.php');
exit;
}
?>

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
    set_flash('error', 'Invalid user selection.');
    redirect('/admin/users.php');
}

if ((int) $id === (int) $_SESSION['user_id']) {

    set_flash(
        'error',
        'You cannot delete your own account.'
    );

    redirect('/admin/users.php');
}

$userStatement = $conn->prepare(
    'SELECT
         id,
         username,
         role,
         is_active
     FROM users
     WHERE id = ?
     LIMIT 1'
);

$userStatement->bind_param('i', $id);
$userStatement->execute();

$user = $userStatement
    ->get_result()
    ->fetch_assoc();

if (!$user) {
    set_flash('error', 'User account not found.');
    redirect('/admin/users.php');
}

if ($user['username'] === 'ucsc') {

    set_flash(
        'error',
        'The required default UCSC user cannot be deleted.'
    );

    redirect('/admin/users.php');
}

$documentStatement = $conn->prepare(
    'SELECT document_path
     FROM applications
     WHERE user_id = ?
     AND document_path IS NOT NULL'
);

$documentStatement->bind_param('i', $id);
$documentStatement->execute();

$documentResult = $documentStatement->get_result();

$documentPaths = [];

while ($document = $documentResult->fetch_assoc()) {

    if ($document['document_path']) {
        $documentPaths[] = $document['document_path'];
    }
}

try {

    $deleteStatement = $conn->prepare(
        'DELETE FROM users
         WHERE id = ?'
    );

    $deleteStatement->bind_param('i', $id);

    $deleteStatement->execute();

    foreach ($documentPaths as $documentPath) {

        $fileName = basename($documentPath);

        $fullPath = UPLOAD_DIR . $fileName;

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    set_flash(
        'success',
        'User account deleted successfully.'
    );

} catch (mysqli_sql_exception $exception) {

    set_flash(
        'error',
        'The user account could not be deleted.'
    );
}

redirect('/admin/users.php');
exit;
}
?>

<?php

require_once __DIR__ . '/../functions.php';

require_admin();

$search = trim($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';

if (!in_array($roleFilter, ['all', 'student', 'admin'], true)) {
    $roleFilter = 'all';
}

if (!in_array($statusFilter, ['all', 'active', 'inactive'], true)) {
    $statusFilter = 'all';
}

$searchLike = '%' . $search . '%';

$roleValue = $roleFilter === 'all'
    ? ''
    : $roleFilter;

$activeValue = -1;

if ($statusFilter === 'active') {
    $activeValue = 1;
} elseif ($statusFilter === 'inactive') {
    $activeValue = 0;
}

$statement = $conn->prepare(
    "SELECT
         id,
         username,
         full_name,
         email,
         phone,
         faculty,
         year_of_study,
         role,
         is_active,
         created_at
     FROM users
     WHERE
         (
             ? = ''
             OR username LIKE ?
             OR full_name LIKE ?
             OR email LIKE ?
         )
         AND
         (
             ? = ''
             OR role = ?
         )
         AND
         (
             ? = -1
             OR is_active = ?
         )
     ORDER BY created_at DESC"
);

$statement->bind_param(
    'ssssssii',
    $search,
    $searchLike,
    $searchLike,
    $searchLike,
    $roleValue,
    $roleValue,
    $activeValue,
    $activeValue
);

$statement->execute();

$result = $statement->get_result();

$resultCount = $result->num_rows;

$pageTitle = 'Manage Users';

include __DIR__ . '/../includes/header.php';

?>

<div class="page-heading">

    <div>
        <p class="eyebrow">Administration</p>

        <h1>Manage Users</h1>

        <p>
            Add, view, edit, activate, deactivate and delete user accounts.
        </p>
    </div>

    <a
        class="button"
        href="<?= BASE_URL ?>/admin/users.php?mode=form"
    >
        Add User
    </a>

</div>

<section class="card">

    <form method="get" class="search-form">

        <input
            type="text"
            name="search"
            value="<?= e($search) ?>"
            placeholder="Search username, name or email"
        >

        <select name="role">

            <option
                value="all"
                <?= $roleFilter === 'all' ? 'selected' : '' ?>
            >
                All roles
            </option>

            <option
                value="student"
                <?= $roleFilter === 'student' ? 'selected' : '' ?>
            >
                Students
            </option>

            <option
                value="admin"
                <?= $roleFilter === 'admin' ? 'selected' : '' ?>
            >
                Administrators
            </option>

        </select>

        <select name="status">

            <option
                value="all"
                <?= $statusFilter === 'all' ? 'selected' : '' ?>
            >
                All statuses
            </option>

            <option
                value="active"
                <?= $statusFilter === 'active' ? 'selected' : '' ?>
            >
                Active
            </option>

            <option
                value="inactive"
                <?= $statusFilter === 'inactive' ? 'selected' : '' ?>
            >
                Inactive
            </option>

        </select>

        <button type="submit">
            Search
        </button>

        <?php if (
            $search !== ''
            || $roleFilter !== 'all'
            || $statusFilter !== 'all'
        ): ?>

            <a
                class="button small"
                href="<?= BASE_URL ?>/admin/users.php"
            >
                Clear
            </a>

        <?php endif; ?>

    </form>

</section>

<section class="card">

    <strong><?= (int) $resultCount ?></strong>

    user<?= $resultCount === 1 ? '' : 's' ?> found.

</section>

<div class="table-wrap">

    <table>

        <thead>

            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Contact</th>
                <th>Faculty and Year of Study</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>

        </thead>

        <tbody>

            <?php if ($resultCount === 0): ?>

                <tr>
                    <td colspan="8">
                        No users matched your search.
                    </td>
                </tr>

            <?php endif; ?>

            <?php while ($user = $result->fetch_assoc()): ?>

                <?php

                $isCurrentUser =
                    (int) $user['id']
                    === (int) $_SESSION['user_id'];

                $isDefaultUser =
                    $user['username'] === 'ucsc';

                ?>

                <tr>

                    <td>
                        <?= (int) $user['id'] ?>
                    </td>

                    <td>
                        <strong>
                            <?= e($user['full_name']) ?>
                        </strong>

                        <br>

                        <?= e($user['username']) ?>

                        <?php if ($isCurrentUser): ?>
                            <br><small>Current account</small>
                        <?php endif; ?>

                        <?php if ($isDefaultUser): ?>
                            <br><small>Required default user</small>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?= e($user['email']) ?>

                        <br>

                        <?= e($user['phone'] ?: 'No phone') ?>
                    </td>

                    <td>
                        <?= e($user['faculty'] ?: 'Not provided') ?>

                        <br>

                        <?php if ($user['year_of_study']): ?>
                            Year <?= e($user['year_of_study']) ?>
                        <?php else: ?>
                            Year not provided
                        <?php endif; ?>
                    </td>

                    <td>
                        <?= e(ucfirst($user['role'])) ?>
                    </td>

                    <td>

                        <span
                            class="badge <?= $user['is_active']
                                ? 'approved'
                                : 'rejected' ?>"
                        >
                            <?= $user['is_active']
                                ? 'Active'
                                : 'Inactive' ?>
                        </span>

                    </td>

                    <td>
                        <?= e(
                            date(
                                'd M Y',
                                strtotime($user['created_at'])
                            )
                        ) ?>
                    </td>

                    <td class="actions">

                        <a
                            href="<?= BASE_URL ?>/admin/users.php?mode=form&id=<?= (int) $user['id'] ?>"
                        >
                            Edit
                        </a>

                        <?php if (
                            !$isCurrentUser
                            && !$isDefaultUser
                        ): ?>

                            <form
                                method="post"
                                action="<?= BASE_URL ?>/admin/users.php?mode=toggle"
                                class="inline-form"
                                data-confirm-form="<?= $user['is_active']
                                    ? 'Deactivate this user account?'
                                    : 'Activate this user account?' ?>"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e(csrf_token()) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $user['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="link-button"
                                >
                                    <?= $user['is_active']
                                        ? 'Deactivate'
                                        : 'Activate' ?>
                                </button>

                            </form>

                            <form
                                method="post"
                                action="<?= BASE_URL ?>/admin/users.php?mode=delete"
                                class="inline-form"
                                data-confirm-form="Delete this user? Their scholarship applications will also be deleted."
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e(csrf_token()) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $user['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="link-button danger-link"
                                >
                                    Delete
                                </button>

                            </form>

                        <?php else: ?>

                            <span>
                                Protected
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>