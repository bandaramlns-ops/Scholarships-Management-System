# 🎓 Scholarship Management System

A web-based portal developed in **PHP** and **MySQL** to streamline and automate the scholarship application, verification, and evaluation lifecycle for students and administrative boards.

---

## 📌 Features

### 👨‍🎓 Student Portal
- **User Authentication:** Registration, login, session validation, and secure profile management.
- **Scholarship Discovery:** Browse available scholarship opportunities and review eligibility criteria.
- **Online Application:** Submit scholarship applications with required academic and background details.
- **Document Management:** Upload supporting financial, identification, and academic documents.
- **Application Tracking:** Monitor review status (Pending, Approved, Rejected) in real time.

### 🛡️ Admin Dashboard
- **Scholarship Administration:** Create, configure, update, or close scholarship schemes.
- **Application Review:** Inspect submitted applicant profiles, verify documents, and update statuses.
- **Applicant Directory:** Filter and search registered students and their submissions.
- **Reporting & Auditing:** Generate summarized reports and track fund/application distributions.
- **Access Control:** Restricted access protecting administrative functionalities and document storage.

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP (Native / Procedural) |
| **Database** | MySQL |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Web Server** | Apache (XAMPP)|

---

## 📂 Project Directory Structure

```text
├── admin/                  # Administrative management panel and controllers
├── assets/                 # Frontend assets (CSS styles, JavaScript, icons)
├── includes/               # Reusable view components (header, footer, nav)
├── sql/                    # Database schema and initial SQL data dumps
│   └── scholarship_management.sql
├── student/                # Student-facing workflows and views
├── uploads/                # Document storage repository (.htaccess secured)
├── config.php              # Database connections and global environment settings
├── functions.php           # Helper functions and business logic utilities
├── index.php               # System entry point and landing page
└── README.md               # Project documentation
