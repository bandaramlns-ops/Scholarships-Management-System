<?php

require_once __DIR__ . '/functions.php';

$pageTitle = 'Functionalities';

include __DIR__ . '/includes/header.php';

?>


<div class="page-heading">

    <div>

        <p class="eyebrow">
            System Features
        </p>

        <h1>
            Functionalities
        </h1>

        <p>
            The main facilities available in the
            Online Scholarship Management System.
        </p>

    </div>

</div>


<section class="card">

    <h2>
        Student Functionalities
    </h2>

    <div class="feature-grid">

        <div>

            <h3>
                User Registration
            </h3>

            <p>
                Students can create personal accounts
                using their academic and contact details.
            </p>

        </div>


        <div>

            <h3>
                Secure Login and Logout
            </h3>

            <p>
                Registered students can securely log in
                and log out of the system.
            </p>

        </div>


        <div>

            <h3>
                Scholarship Search
            </h3>

            <p>
                Students can browse and search scholarship
                opportunities by title and provider.
            </p>

        </div>


        <div>

            <h3>
                Scholarship Details
            </h3>

            <p>
                Students can view scholarship amount,
                deadline, description and eligibility.
            </p>

        </div>


        <div>

            <h3>
                Online Applications
            </h3>

            <p>
                Students can submit GPA, household income,
                personal statements and supporting documents.
            </p>

        </div>


        <div>

            <h3>
                Application Tracking
            </h3>

            <p>
                Students can monitor Pending, Approved
                and Rejected applications.
            </p>

        </div>


        <div>

            <h3>
                Administrator Comments
            </h3>

            <p>
                Students can view comments provided
                during the application review process.
            </p>

        </div>


        <div>

            <h3>
                Profile Management
            </h3>

            <p>
                Students can update their personal,
                contact and academic information.
            </p>

        </div>

    </div>

</section>


<section class="card">

    <h2>
        Administrator Functionalities
    </h2>

    <div class="feature-grid">

        <div>

            <h3>
                User Management
            </h3>

            <p>
                Administrators can add, edit, activate,
                deactivate and delete accounts.
            </p>

        </div>


        <div>

            <h3>
                Scholarship Management
            </h3>

            <p>
                Administrators can add, edit, open,
                close and delete scholarships.
            </p>

        </div>


        <div>

            <h3>
                Application Review
            </h3>

            <p>
                Administrators can review application
                information and uploaded documents.
            </p>

        </div>


        <div>

            <h3>
                Application Decisions
            </h3>

            <p>
                Applications can be marked Pending,
                Approved or Rejected.
            </p>

        </div>


        <div>

            <h3>
                Administrator Comments
            </h3>

            <p>
                Administrators can provide comments
                regarding scholarship decisions.
            </p>

        </div>


        <div>

            <h3>
                Reports
            </h3>

            <p>
                The system produces summary reports
                for users, scholarships and applications.
            </p>

        </div>

    </div>

</section>


<section class="card">

    <h2>
        Security Functionalities
    </h2>

    <div class="feature-grid">

        <div>

            <h3>
                Role-Based Authorization
            </h3>

            <p>
                Administrator pages cannot be accessed
                by ordinary student users.
            </p>

        </div>


        <div>

            <h3>
                Password Protection
            </h3>

            <p>
                Passwords are stored using secure
                password hashing.
            </p>

        </div>


        <div>

            <h3>
                SQL Injection Protection
            </h3>

            <p>
                Database operations use prepared
                SQL statements.
            </p>

        </div>


        <div>

            <h3>
                Secure Form Submission
            </h3>

            <p>
                CSRF tokens are used for important
                form submissions.
            </p>

        </div>


        <div>

            <h3>
                File Validation
            </h3>

            <p>
                Uploaded supporting documents are
                restricted by file type and size.
            </p>

        </div>

    </div>

</section>


<?php

include __DIR__ . '/includes/footer.php';

?>