<?php

require_once __DIR__ . '/../functions.php';

// Read the search and status-filter values from the URL.
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';

// Only allow these status-filter values.
$allowedStatuses = ['all', 'Open', 'Closed'];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

/*
 * Select scholarships according to the search and filter values.
 */

// Search text and status are both provided.
if ($search !== '' && $statusFilter !== 'all') {

    $like = '%' . $search . '%';

    $statement = $conn->prepare(
        'SELECT *
         FROM scholarships
         WHERE
         (
             title LIKE ?
             OR provider LIKE ?
             OR description LIKE ?
             OR eligibility LIKE ?
         )
         AND status = ?
         ORDER BY deadline ASC'
    );

    $statement->bind_param(
        'sssss',
        $like,
        $like,
        $like,
        $like,
        $statusFilter
    );

    $statement->execute();
    $result = $statement->get_result();

// Only search text is provided.
} elseif ($search !== '') {

    $like = '%' . $search . '%';

    $statement = $conn->prepare(
        'SELECT *
         FROM scholarships
         WHERE
             title LIKE ?
             OR provider LIKE ?
             OR description LIKE ?
             OR eligibility LIKE ?
         ORDER BY
             CASE WHEN status = "Open" THEN 0 ELSE 1 END,
             deadline ASC'
    );

    $statement->bind_param(
        'ssss',
        $like,
        $like,
        $like,
        $like
    );

    $statement->execute();
    $result = $statement->get_result();

// Only a status is selected.
} elseif ($statusFilter !== 'all') {

    $statement = $conn->prepare(
        'SELECT *
         FROM scholarships
         WHERE status = ?
         ORDER BY deadline ASC'
    );

    $statement->bind_param('s', $statusFilter);

    $statement->execute();
    $result = $statement->get_result();

// No search or filter is provided.
} else {

    $result = $conn->query(
        'SELECT *
         FROM scholarships
         ORDER BY
             CASE WHEN status = "Open" THEN 0 ELSE 1 END,
             deadline ASC'
    );
}

$resultCount = $result->num_rows;

$pageTitle = 'Scholarships';

include __DIR__ . '/../includes/header.php';

?>

<div class="page-heading">

    <div>
        <p class="eyebrow">Available opportunities</p>

        <h1>Scholarships</h1>

        <p>
            Search and view available university scholarship opportunities.
        </p>
    </div>

    <form method="get" class="search-form">

        <input
            type="text"
            name="search"
            value="<?= e($search) ?>"
            placeholder="Search title or provider"
        >

        <select name="status">

            <option
                value="all"
                <?= $statusFilter === 'all' ? 'selected' : '' ?>
            >
                All statuses
            </option>

            <option
                value="Open"
                <?= $statusFilter === 'Open' ? 'selected' : '' ?>
            >
                Open
            </option>

            <option
                value="Closed"
                <?= $statusFilter === 'Closed' ? 'selected' : '' ?>
            >
                Closed
            </option>

        </select>

        <button type="submit">
            Search
        </button>

        <?php if ($search !== '' || $statusFilter !== 'all'): ?>

            <a
                class="button small"
                href="<?= BASE_URL ?>/student/scholarships.php"
            >
                Clear
            </a>

        <?php endif; ?>

    </form>

</div>

<div class="card">

    <strong>
        <?= (int) $resultCount ?>
    </strong>

    scholarship<?= $resultCount === 1 ? '' : 's' ?> found.

</div>

<div class="card-list">

    <?php if ($resultCount === 0): ?>

        <div class="card">

            <h2>No scholarships found</h2>

            <p>
                Try entering a different search term or changing the status
                filter.
            </p>

            <a
                class="button small"
                href="<?= BASE_URL ?>/student/scholarships.php"
            >
                Show All Scholarships
            </a>

        </div>

    <?php endif; ?>

    <?php while ($scholarship = $result->fetch_assoc()): ?>

        <?php

        $deadlineTimestamp = strtotime($scholarship['deadline']);

        $todayTimestamp = strtotime(date('Y-m-d'));

        $deadlinePassed = $deadlineTimestamp < $todayTimestamp;

        ?>

        <article class="card scholarship-card">

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

                <h2>
                    <?= e($scholarship['title']) ?>
                </h2>

                <p>
                    <strong>Provider:</strong>
                    <?= e($scholarship['provider']) ?>
                </p>

                <p>
                    <?= e(
                        mb_strimwidth(
                            $scholarship['description'],
                            0,
                            200,
                            '...'
                        )
                    ) ?>
                </p>

            </div>

            <div>

                <p>
                    <strong>Amount:</strong><br>

                    <?= e(format_money($scholarship['amount'])) ?>
                </p>

                <p>
                    <strong>Deadline:</strong><br>

                    <?= e(
                        date(
                            'd F Y',
                            $deadlineTimestamp
                        )
                    ) ?>
                </p>

                <a
                    class="button small"
                    href="<?= BASE_URL ?>/student/scholarship_view.php?id=<?= (int) $scholarship['id'] ?>"
                >
                    View Details
                </a>

            </div>

        </article>

    <?php endwhile; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>