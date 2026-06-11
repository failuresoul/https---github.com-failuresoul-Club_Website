
A comprehensive web application for the **Sylhet Association of Khulna University of Engineering & Technology (KUET)**. The platform unites, coordinates, and supports Sylheti students studying at KUET through event announcements, membership registrations, fundraising campaigns, job opportunity postings, and emergency support services.

---

## 🚀 Features

- **Public Landing Page:** Displays sections for Home, About Us, Events, Gallery, Notices, Funding, Jobs, Emergency, and Contact.
- **Dynamic Notification System:** An interactive notification bell panel alerts users of the latest notices, funding updates, jobs, or emergency cases.
- **Membership Application ("Join Now"):** Custom modal form requesting student details, photo upload, and their connection to Sylhet.
- **Donation Receipt Logging:** Interface for users to report donation transaction IDs (`TrxID`) for verification under specific funding campaigns.
- **Job Board:** Lists active internships, tuition roles, and event assistant opportunities for members.
- **Emergency Helpline:** Immediate contact numbers and instructions for blood requests or urgent assistance.
- **Admin Dashboard:** Secure administration panel to review, approve, edit, or delete notices, campaigns, members, contact inquiries, job postings, and gallery assets.

---

## 🛠️ Technology Stack

- **Frontend:** HTML5, CSS3 (Vanilla styles), Vanilla JavaScript
- **Backend:** PHP (OOP concepts and PDO database interface)
- **Database:** MySQL

---

## 📂 File Structure

```text
Sylhet Association/
│
├── admin/                 # Admin Panel management views
│   ├── index.php          # Admin landing dashboard
│   ├── members.php        # Manage members & applications (approve/reject/edit)
│   ├── notices.php        # Publish & remove notices
│   ├── funding.php        # Control fundraising campaigns
│   ├── donations.php      # Approve/reject logged transaction records
│   ├── jobs.php           # Post and toggle job roles
│   ├── emergency.php      # Manage active emergency cases
│   ├── gallery.php        # Upload and categorize gallery photos
│   ├── contact.php        # View and respond to general contact messages
│   ├── login.php          # Admin secure login screen
│   ├── logout.php         # Destroy session
│   ├── setup.php          # Database setup and data seeder script
│   ├── header.php         # Shared admin header
│   └── footer.php         # Shared admin footer
│
├── process/               # Backend action handlers for forms
│   ├── submit_join.php    # Processes membership submissions with file uploads
│   ├── submit_donation.php# Logs transaction receipt records
│   └── submit_contact.php # Stores public inquiry messages
│
├── css/                   # Stylesheets
│   ├── style.css          # Main site style guide
│   └── admin.css          # Admin panel layout and dashboard style
│
├── img/                   # Asset folder for website media and uploads
├── config.php             # Session start, PDO DB connection, and utility helpers
├── index.php              # Main landing page
└── README.md              # Project documentation
```

---

## ⚙️ Installation & Setup

1. **Prerequisites:**
   Ensure you have a local PHP/MySQL environment installed, such as **XAMPP**, **WAMP**, or **Laragon**.

2. **Clone/Extract Project:**
   Place the project files into your local server root directory (e.g., `C:\xampp\htdocs\Sylhet Association`).

3. **Verify Database Configuration:**
   Open `config.php` and verify the MySQL connection settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sylhet_association');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Initialize Database & Seed Data:**
   Start your Apache and MySQL servers, then navigate to the database setup script in your browser:
   ```text
   http://localhost/Sylhet Association/admin/setup.php
   ```
   This script will automatically:
   - Create the database `sylhet_association` if it does not exist.
   - Construct all 10 relational tables with required constraints.
   - Seed initial data (advisors, members, notices, jobs, campaigns, and emergency cases).
   - Register 4 predefined admin accounts.

5. **Access the Public Site:**
   Open the homepage:
   ```text
   http://localhost/Sylhet Association/index.php
   ```

---

## 🔑 Default Admin Credentials

Once database initialization is completed via `setup.php`, you can log into the Admin Area using any of the following accounts:

| Name | Email Address | Password |
|---|---|---|
| Chitrodip Sen | `chitrodip@kuet.ac.bd` | `Chitrodip@KUET20` |
| Preetom Roy Shaibal | `preetom@kuet.ac.bd` | `Preetom@KUET20` |
| Amit Kairy | `amit@kuet.ac.bd` | `Amit@KUET20` |
| Abu Omayer | `omayer@kuet.ac.bd` | `Omayer@KUET20` |

Admin login page is located at: `http://localhost/Sylhet Association/admin/login.php` or accessible through the link in the footer.
