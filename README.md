# Personal IT Portfolio & Dynamic Project Management System

**Author:** Kian Lee Raian M. Aguinaldo  
**Degree:** Bachelor of Science in Information Technology  
**Contact:** [kianaguinaldo7@gmail.com](mailto:kianaguinaldo7@gmail.com) | `09762022723`  

A modern, responsive personal portfolio website built with native **PHP 8**, **MySQL**, **Modern CSS**, and **Vanilla JavaScript**. Designed with a developer-focused aesthetic using the custom color palette:  
`#5680E9` (Royal Blue) &bull; `#84CEEB` (Sky Blue) &bull; `#5AB9EA` (Cerulean) &bull; `#C1C8C4` (Sage/Silver) &bull; `#8860D0` (Violet)

The portfolio includes a **Project & Content Management System (Admin Dashboard)**, an **interactive canvas particle background with scroll parallax and desktop mouse interaction**, and **IntersectionObserver scroll-triggered animations**.

---

## Key Features

1. **Public Portfolio (`index.php`)**:
   - **Hero Section:** Introduction to Kian Lee Raian M. Aguinaldo, dynamic typing role, entrance animations, and direct action buttons (including dynamic resume download).
   - **About Me Section:** Background as an Information Technology graduate, career focus, and technical pillars.
   - **Skills & Competencies:** Animated cards highlighting Software Engineering, Database Systems, Networking & Infrastructure, and Systems Administration & Security.
   - **Dynamic Projects Showcase:** Automatically queries active projects from MySQL. Displays screenshots, titles, descriptions, technology badges, and conditional GitHub / Live Demo buttons.
   - **Certificates & Achievements:** Showcases verified credentials, training completions, and technical workshops with modal/tab previews and document links.
   - **Experience & Education Timeline:** Visual vertical timeline chronicling BS Information Technology degree, hands-on IT support internship, and project milestones.
   - **Contact Section:** AJAX-powered contact form that stores submissions directly into the MySQL `messages` table, alongside public contact links.
   - **Interactive Particle Background:** High-performance Canvas particle system with multi-layer vertical scroll parallax, gentle desktop mouse repulsion, and reduced particle counts on mobile devices.
   - **Accessibility:** Fully honors `prefers-reduced-motion` settings.

2. **Protected Admin Dashboard (`/admin`)**:
   - **Authentication:** Protected session-based login (`/admin/login.php`) using Bcrypt password verification (`password_verify`) against MySQL `admin_users`. No hardcoded fallback credentials exist.
   - **Overview Dashboard (`/admin/index.php`):** Metrics for total projects, published projects, certificates, unread inquiries, database status, and recent entries.
   - **Project Management (`/admin/projects.php`):** Tabular management of projects with add, edit, and secure POST deletion with modal confirmation.
   - **Add & Edit Project (`/admin/add-project.php`, `/admin/edit-project.php`):** Form handling with title, category, technologies, description, screenshot file upload (with MIME type and extension validation into `uploads/projects/`), URLs, and publication status.
   - **Certificate Management (`/admin/certificates.php`, `/admin/add-certificate.php`, `/admin/edit-certificate.php`, `/admin/delete-certificate.php`):** Complete management of certificates, PDF/image upload validation, ordering, quick status toggling, and secure POST deletion.
   - **Resume Management (`/admin/resume.php`):** Dedicated PDF upload, preview, and removal interface linked to the public `download-resume.php` controller.
   - **Settings (`/admin/settings.php`):** Manage administrator username and password (requiring current password verification and collision checks) and update public profile information.
   - **Security Measures:** Prepared PDO statements for SQL queries, CSRF token validation on all state-changing POST forms, secure session cookies (`HttpOnly`, `SameSite=Lax`), path-traversal safeguards on file deletion, and `.htaccess` protection disabling script execution in upload directories.

---

## Step-by-Step Setup & Installation Guide

### 1. Requirements
- **Web Server & PHP:** PHP 8.0 or newer (tested on PHP 8.2) with `pdo_mysql`, `fileinfo`, and `mbstring` extensions enabled
- **Database:** MySQL 5.7+ or MariaDB 10.4+
- **Recommended Local Stack:** [XAMPP](https://www.apachefriends.org/) (Windows, macOS, or Linux)

---

### 2. Set Up Local Environment (XAMPP)
1. Download and install **XAMPP** from [apachefriends.org](https://www.apachefriends.org/).
2. Open the **XAMPP Control Panel**.
3. Start both **Apache** and **MySQL**.
4. Confirm both services indicate active/running status.

---

### 3. Place Project in Web Root
Clone or copy this repository into your web server's document root (e.g., XAMPP `htdocs`):
```
C:\xampp\htdocs\portfolio
```
*(If served from another directory, configure an Apache VirtualHost or use PHP's built-in web server).*

---

### 4. Create MySQL Database & Import Schema (New Local Database Only)

> [!IMPORTANT]
> The database setup instructions below are strictly for creating a **fresh, new local development database**. Never run schema import scripts against an existing database containing active records.

1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`) or your preferred MySQL client.
2. Create a new local database named `portfolio_db` with `utf8mb4_unicode_ci` collation:
   ```sql
   CREATE DATABASE IF NOT EXISTS `portfolio_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import the project schema and initial portfolio structure into the new database using `database.sql`:
   - **Via phpMyAdmin:** Select `portfolio_db` in the sidebar, open the **Import** tab, choose `database.sql` from the repository root, and click **Import**.
   - **Via MySQL CLI (PowerShell):**
     ```powershell
     Get-Content database.sql | & "C:\xampp\mysql\bin\mysql.exe" -u root portfolio_db
     ```
   `database.sql` safely creates all tables (`projects`, `certificates`, `profile`, `messages`, `admin_users`) using `CREATE TABLE IF NOT EXISTS` and populates baseline portfolio data without seeding credentials or fake inquiries.

---

### 5. Configure Database Credentials (`.env`)

A configuration template is provided in `.env.example`. Create a local copy named `.env` in the project root:

```ini
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=portfolio_db
DB_USER=root
DB_PASS=

# Application
APP_URL=http://localhost/portfolio
SESSION_NAME=portfolio_admin
```

> [!NOTE]
> `config/db.php` automatically parses `.env` if present. If `.env` is omitted, `config/db.php` falls back to default local XAMPP parameters (`127.0.0.1:3306`, user `root`, empty password, database `portfolio_db`).

---

### 6. Admin Authentication & Access

- **Admin Login URL:** `http://localhost/portfolio/admin/login.php`
- **No Hardcoded Credentials:** The application contains **no default or fallback credentials**. All login attempts are verified strictly against the `admin_users` table in MySQL using `password_verify()`.
- **Administrator Provisioning:** In order to prevent sensitive credentials from ever being committed to version control, **no administrator account is seeded in `database.sql` or `database/schema.sql`**, and no web-based registration tool is included in this public repository.
- **Account Setup Requirement:** An administrator account must be provisioned securely in your local database before logging in. You can insert an admin user record with a Bcrypt password hash into `admin_users`:
  ```sql
  -- Example SQL insertion (replace USERNAME and generate your own Bcrypt hash):
  INSERT INTO `admin_users` (`username`, `password_hash`, `created_at`)
  VALUES ('YOUR_USERNAME', '$2y$10$YOUR_BCRYPT_HASH_HERE', NOW());
  ```
  *(A password hash can be generated using PHP: `php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_BCRYPT);"`).*

> [!TIP]
> Once logged in, administrator credentials and public profile information can be updated at any time from **Settings** (`/admin/settings.php`).

---

### 7. Run and View the Portfolio

1. Open your browser and navigate to:
   ```
   http://localhost/portfolio/index.php
   ```
2. The portfolio will render with your profile information, dynamic projects and certificates loaded from MySQL, the interactive particle background, and contact form.

---

### 8. Content Management Workflow (Admin CRUD)

1. **Sign In:** Navigate to `/admin/login.php` and sign in using your provisioned database credentials.
2. **Projects (`/admin/projects.php`):**
   - Click **+ Add Project** (`/admin/add-project.php`) to publish new work with descriptions, tags, live links, and uploaded screenshots.
   - Edit or delete existing records. Project deletions require interactive modal confirmation and submit via secure POST with CSRF validation.
3. **Certificates (`/admin/certificates.php`):**
   - Click **+ Add Certificate** (`/admin/add-certificate.php`) to upload achievement documents (PDF/PNG/JPG) with issuing organization, date, and description.
   - Quickly toggle visibility between Published and Draft directly from the table.
4. **Resume (`/admin/resume.php`):**
   - Upload or replace the active PDF resume served by `download-resume.php`.
5. **Profile & Account Settings (`/admin/settings.php`):**
   - Update administrator credentials (requires current password verification) or edit public contact details.

---

### 9. Publishing to GitHub Safely

Publishing to a public GitHub repository is permanent. To safeguard private credentials and sensitive files, follow this two-phase procedure: first inspect and stage your changes locally, then review before actually publishing.

#### Phase 1: Review and Stage Files Locally

1. **Verify `.gitignore` Rules:**
   Confirm that `.gitignore` is active and properly excludes:
   - Environment configurations (`.env`, `.env.local`, `.env.backup`)
   - Local database backups and SQL dumps (`_backups/`, `*.sql.bak`, `dump.sql`)
   - Local setup scripts (`setup.php`)
   - Actual uploaded files (`uploads/projects/*`, `uploads/certificates/*`, `uploads/resume/*`), preserving only `.gitkeep` and `.htaccess`

2. **Inspect Working Tree Status:**
   Review all modified and untracked files before staging:
   ```bash
   git status
   ```

3. **Stage Changes:**
   Stage verified files:
   ```bash
   git add .
   ```

4. **Verify the Staged File List & Inspect Diffs Before Committing:**
   Double-check exactly what is staged for commit:
   ```bash
   git status
   git diff --staged
   ```
   Verify that no `.env` file, database backup with credentials, or sensitive private files appear in the staged output.

5. **Commit Locally:**
   Once you have thoroughly verified the staged files:
   ```bash
   git commit -m "feat: portfolio website with admin cms, certificates, and resume management"
   git branch -M main
   ```

#### Phase 2: Remote Publishing (Requires Explicit Review & Approval)

> [!CAUTION]
> **Warning:** Executing `git push` immediately uploads your commit history and files to the remote repository on GitHub, making them permanently accessible to anyone if the repository is public. **Do not run `git push` until you have explicitly reviewed, verified, and approved all staged and committed content.**

When you are ready to publish:

1. **Set Up Remote URL (Placeholder):**
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/YOUR_PORTFOLIO_REPO.git
   ```
   *(Keep `YOUR_USERNAME` and `YOUR_PORTFOLIO_REPO` as placeholders until you explicitly configure your own GitHub repository).*

2. **Push to Remote:**
   ```bash
   git push -u origin main
   ```

---

## Directory Structure

```
portfolio/
|-- .env.example              # Configuration environment template
|-- .gitignore                # Protects credentials, backups, and user uploads
|-- database.sql              # Clean MySQL schema and initial portfolio data
|-- download-resume.php       # Public resume download and tab preview controller
|-- index.php                 # Public portfolio homepage
|-- README.md                 # Project documentation
|-- admin/                    # Protected Admin CMS
|   |-- index.php             # Overview dashboard & key metrics
|   |-- projects.php          # Project list & modal deletion interface
|   |-- add-project.php       # Add project form with image upload
|   |-- edit-project.php      # Edit project form & image replacement
|   |-- delete-project.php    # Confirmed POST project deletion handler
|   |-- certificates.php      # Certificate management & status toggle
|   |-- add-certificate.php   # Add certificate form & document upload
|   |-- edit-certificate.php  # Edit certificate form & file replacement
|   |-- delete-certificate.php# Confirmed POST certificate deletion handler
|   |-- resume.php            # Resume PDF upload, replacement & removal
|   |-- settings.php          # Account credentials & public profile manager
|   |-- login.php             # Database-backed administrator authentication
|   |-- logout.php            # Session termination & cookie invalidation
|   |-- auth_check.php        # Session validation & CSRF security middleware
|   |-- admin_header.php      # Shared navigation bar component
|   `-- admin.css             # Admin dashboard stylesheet
|-- api/
|   `-- contact.php           # Asynchronous message processor & validator
|-- assets/
|   |-- css/
|   |   `-- style.css         # Main theme stylesheet (#5680E9 palette)
|   |-- img/
|   |   `-- profile.jpg       # Profile picture asset
|   `-- js/
|       |-- main.js           # Scroll animations & navigation behavior
|       |-- particles.js      # Interactive canvas particle engine
|       `-- contact.js        # Contact form submission & toast feedback
|-- config/
|   `-- db.php                # PDO connection & database helper utilities
|-- database/
|   |-- schema.sql            # Clean DDL database schema structure
|   |-- migration_certificates_resume.sql  # SQL migration for certificates & resume
|   `-- migrate_certificates_resume.php    # Idempotent migration runner script
`-- uploads/
    |-- certificates/         # Certificate document uploads
    |   |-- .htaccess         # Disables script execution
    |   `-- .gitkeep
    |-- projects/             # Project screenshot uploads
    |   |-- .htaccess         # Disables script execution
    |   `-- .gitkeep
    `-- resume/               # Resume PDF uploads
        |-- .htaccess         # Disables script execution
        `-- .gitkeep
```

---

## License
Created for personal portfolio and professional software demonstration purposes.
