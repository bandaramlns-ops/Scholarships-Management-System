<?php

require_once __DIR__ . '/../functions.php';

require_admin();

/*
 * GENERAL SYSTEM STATISTICS
 */

$summaryResult = $conn->query(
    "SELECT

        (SELECT COUNT(*)
         FROM users) AS total_users,

        (SELECT COUNT(*)
         FROM users
         WHERE role = 'student') AS total_students,

        (SELECT COUNT(*)
         FROM users
         WHERE role = 'admin') AS total_admins,

        (SELECT COUNT(*)
         FROM users
         WHERE is_active = 1) AS active_users,

        (SELECT COUNT(*)
         FROM scholarships) AS total_scholarships,

        (SELECT COUNT(*)
         FROM scholarships
         WHERE status = 'Open') AS open_scholarships,

        (SELECT COUNT(*)
         FROM scholarships
         WHERE status = 'Closed') AS closed_scholarships,

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

$summary = $summaryResult->fetch_assoc();


/*
 * SCHOLARSHIP APPLICATION REPORT
 */

$scholarshipReport = $conn->query(
    "SELECT

        s.id,
        s.title,
        s.provider,
        s.amount,
        s.deadline,
        s.status,

        COUNT(a.id) AS total_applications,

        SUM(
            CASE
                WHEN a.status = 'Pending'
                THEN 1
                ELSE 0
            END
        ) AS pending_count,

        SUM(
            CASE
                WHEN a.status = 'Approved'
                THEN 1
                ELSE 0
            END
        ) AS approved_count,

        SUM(
            CASE
                WHEN a.status = 'Rejected'
                THEN 1
                ELSE 0
            END
        ) AS rejected_count

     FROM scholarships AS s

     LEFT JOIN applications AS a
        ON a.scholarship_id = s.id

     GROUP BY
        s.id,
        s.title,
        s.provider,
        s.amount,
        s.deadline,
        s.status

     ORDER BY s.created_at DESC"
);


/*
 * STUDENT APPLICATION REPORT
 */

$studentReport = $conn->query(
    "SELECT

        u.id,
        u.username,
        u.full_name,
        u.email,

        COUNT(a.id) AS total_applications,

        SUM(
            CASE
                WHEN a.status = 'Pending'
                THEN 1
                ELSE 0
            END
        ) AS pending_count,

        SUM(
            CASE
                WHEN a.status = 'Approved'
                THEN 1
                ELSE 0
            END
        ) AS approved_count,

        SUM(
            CASE
                WHEN a.status = 'Rejected'
                THEN 1
                ELSE 0
            END
        ) AS rejected_count

     FROM users AS u

     LEFT JOIN applications AS a
        ON a.user_id = u.id

     WHERE u.role = 'student'

     GROUP BY
        u.id,
        u.username,
        u.full_name,
        u.email

     ORDER BY total_applications DESC,
              u.full_name ASC"
);


/*
 * MOST RECENT APPLICATIONS
 */

$recentApplications = $conn->query(
    "SELECT

        a.id,
        a.status,
        a.gpa,
        a.applied_at,

        u.full_name,
        u.username,

        s.title

     FROM applications AS a

     INNER JOIN users AS u
        ON u.id = a.user_id

     INNER JOIN scholarships AS s
        ON s.id = a.scholarship_id

     ORDER BY a.applied_at DESC

     LIMIT 10"
);


$pageTitle = 'Reports';

include __DIR__ . '/../includes/header.php';

?>


<div class="page-heading">

    <div>

        <p class="eyebrow">
            Administration
        </p>

        <h1>
            System Reports
        </h1>

        <p>
            Summary statistics for users, scholarships and
            scholarship applications.
        </p>

    </div>


    <button
        type="button"
        class="button"
        onclick="window.print()"
    >
        Print Report
    </button>

</div>


<h2>
    System Summary
</h2>


<div class="stats-grid">

    <div class="stat">

        <strong>
            <?= (int) $summary['total_users'] ?>
        </strong>

        <span>
            Total Users
        </span>

    </div>


    <div class="stat">

        <strong>
            <?= (int) $summary['total_students'] ?>
        </strong>

        <span>
            Students
        </span>

    </div>


    <div class="stat">

        <strong>
            <?= (int) $summary['total_admins'] ?>
        </strong>

        <span>
            Administrators
        </span>

    </div>


    <div class="stat">

        <strong>
            <?= (int) $summary['active_users'] ?>
        </strong>

        <span>
            Active Users
        </span>

    </div>

</div>


<div class="stats-grid">

    <div class="stat">

        <strong>
            <?= (int) $summary['total_scholarships'] ?>
        </strong>

        <span>
            Total Scholarships
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
            <?= (int) $summary['closed_scholarships'] ?>
        </strong>

        <span>
            Closed Scholarships
        </span>

    </div>

</div>


<h2>
    Application Summary
</h2>


<div class="stats-grid">

    <div class="stat">

        <strong>
            <?= (int) $summary['total_applications'] ?>
        </strong>

        <span>
            Total Applications
        </span>

    </div>


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


<h2>
    Scholarship Application Report
</h2>


<div class="table-wrap">

    <table>

        <thead>

            <tr>

                <th>
                    Scholarship
                </th>

                <th>
                    Provider
                </th>

                <th>
                    Amount
                </th>

                <th>
                    Deadline
                </th>

                <th>
                    Status
                </th>

                <th>
                    Total
                </th>

                <th>
                    Pending
                </th>

                <th>
                    Approved
                </th>

                <th>
                    Rejected
                </th>

            </tr>

        </thead>


        <tbody>

            <?php while (
                $row = $scholarshipReport->fetch_assoc()
            ): ?>

                <tr>

                    <td>

                        <strong>
                            <?= e($row['title']) ?>
                        </strong>

                    </td>


                    <td>
                        <?= e($row['provider']) ?>
                    </td>


                    <td>

                        <?= e(
                            format_money(
                                $row['amount']
                            )
                        ) ?>

                    </td>


                    <td>

                        <?= e(
                            date(
                                'd M Y',
                                strtotime(
                                    $row['deadline']
                                )
                            )
                        ) ?>

                    </td>


                    <td>

                        <span
                            class="badge <?= e(
                                strtolower(
                                    $row['status']
                                )
                            ) ?>"
                        >

                            <?= e($row['status']) ?>

                        </span>

                    </td>


                    <td>
                        <?= (int) $row['total_applications'] ?>
                    </td>


                    <td>
                        <?= (int) $row['pending_count'] ?>
                    </td>


                    <td>
                        <?= (int) $row['approved_count'] ?>
                    </td>


                    <td>
                        <?= (int) $row['rejected_count'] ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

</div>


<h2>
    Student Application Report
</h2>


<div class="table-wrap">

    <table>

        <thead>

            <tr>

                <th>
                    Student
                </th>

                <th>
                    Email
                </th>

                <th>
                    Total Applications
                </th>

                <th>
                    Pending
                </th>

                <th>
                    Approved
                </th>

                <th>
                    Rejected
                </th>

            </tr>

        </thead>


        <tbody>

            <?php while (
                $student = $studentReport->fetch_assoc()
            ): ?>

                <tr>

                    <td>

                        <strong>
                            <?= e($student['full_name']) ?>
                        </strong>

                        <br>

                        <small>
                            <?= e($student['username']) ?>
                        </small>

                    </td>


                    <td>
                        <?= e($student['email']) ?>
                    </td>


                    <td>
                        <?= (int) $student['total_applications'] ?>
                    </td>


                    <td>
                        <?= (int) $student['pending_count'] ?>
                    </td>


                    <td>
                        <?= (int) $student['approved_count'] ?>
                    </td>


                    <td>
                        <?= (int) $student['rejected_count'] ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

</div>


<h2>
    Recent Applications
</h2>


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
                    Status
                </th>

                <th>
                    Applied
                </th>

                <th>
                    Action
                </th>

            </tr>

        </thead>


        <tbody>

            <?php if (
                $recentApplications->num_rows === 0
            ): ?>

                <tr>

                    <td colspan="6">
                        No applications have been submitted yet.
                    </td>

                </tr>

            <?php endif; ?>


            <?php while (
                $application =
                    $recentApplications->fetch_assoc()
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

                    </td>


                    <td>
                        <?= e($application['title']) ?>
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

                        <a
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