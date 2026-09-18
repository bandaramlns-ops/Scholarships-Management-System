<?php

require_once __DIR__ . '/functions.php';

$pageTitle = 'Help';

include __DIR__ . '/includes/header.php';

?>


<div class="page-heading">

    <div>

        <p class="eyebrow">
            User Guide
        </p>

        <h1>
            Help
        </h1>

        <p>
            Instructions for using the
            Online Scholarship Management System.
        </p>

    </div>

</div>


<section class="card">

    <h2>
        Student Guide
    </h2>

    <h3>
        1. Register an Account
    </h3>

    <p>
        Select Register from the navigation menu and
        provide the requested personal and academic
        information.
    </p>


    <h3>
        2. Login
    </h3>

    <p>
        Enter your username and password on the Login page.
    </p>

    <p>
        The required default ordinary account is:
    </p>

    <div class="note">

        Username:
        <strong>ucsc</strong>

        <br>

        Password:
        <strong>ucsc</strong>

    </div>


    <h3>
        3. Find a Scholarship
    </h3>

    <p>
        Open the Scholarships page. Use the search box
        and status filter to find suitable opportunities.
    </p>


    <h3>
        4. View Scholarship Details
    </h3>

    <p>
        Select View Details to check the scholarship
        amount, provider, eligibility requirements and
        application deadline.
    </p>


    <h3>
        5. Apply for a Scholarship
    </h3>

    <p>
        Select Apply Now and enter your GPA, annual
        household income and personal statement.
    </p>

    <p>
        You may also upload a supporting PDF, JPG,
        JPEG or PNG document up to 2 MB.
    </p>


    <h3>
        6. Track an Application
    </h3>

    <p>
        Open My Applications to view your application
        status.
    </p>

    <p>
        Possible statuses are:
    </p>

    <ul>

        <li>
            Pending — application is awaiting review.
        </li>

        <li>
            Approved — application was approved.
        </li>

        <li>
            Rejected — application was not approved.
        </li>

    </ul>


    <h3>
        7. Update Your Profile
    </h3>

    <p>
        Open Profile to update your name, email,
        phone number, faculty or year of study.
    </p>

</section>


<section class="card">

    <h2>
        Administrator Guide
    </h2>


    <h3>
        Manage Users
    </h3>

    <p>
        Administrators can add, edit, activate,
        deactivate and delete user accounts.
    </p>


    <h3>
        Manage Scholarships
    </h3>

    <p>
        Administrators can create scholarship
        opportunities and update their details,
        status and deadlines.
    </p>


    <h3>
        Review Applications
    </h3>

    <p>
        Administrators can view student applications,
        open supporting documents and record decisions.
    </p>


    <h3>
        Reports
    </h3>

    <p>
        The Reports page displays summary statistics
        for users, scholarships and applications.
    </p>

</section>


<section class="card">

    <h2>
        Common Problems
    </h2>


    <h3>
        I cannot log in
    </h3>

    <p>
        Check that the username and password are correct.
        An administrator may also have deactivated the account.
    </p>


    <h3>
        I cannot apply for a scholarship
    </h3>

    <p>
        Check whether the scholarship is Open and whether
        its application deadline has passed.
    </p>


    <h3>
        I cannot apply twice
    </h3>

    <p>
        The system allows only one application per student
        for each scholarship.
    </p>


    <h3>
        My document will not upload
    </h3>

    <p>
        Use PDF, JPG, JPEG or PNG and ensure the file
        is no larger than 2 MB.
    </p>

</section>


<?php

include __DIR__ . '/includes/footer.php';

?>