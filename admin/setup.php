<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Temporary load config constants but catch connection error
define('DB_HOST', 'localhost');
define('DB_NAME', 'sylhet_association');
define('DB_USER', 'root');
define('DB_PASS', '');

$message = "";
$error = false;

try {
    // 1. Connect to MySQL server first without selecting a database
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 2. Create database
    $conn->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8 COLLATE utf8_general_ci");
    $message .= "✔ Database `" . DB_NAME . "` created or verified.<br>";
    
    // 3. Connect to the specific database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 4. Create Tables
    
    // Admins
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $message .= "✔ Table `admins` created.<br>";
    
    // Members
    $pdo->exec("CREATE TABLE IF NOT EXISTS members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        student_id VARCHAR(50) UNIQUE NOT NULL,
        department VARCHAR(100) NOT NULL,
        batch VARCHAR(50) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        photo VARCHAR(255) DEFAULT NULL,
        reason TEXT NOT NULL,
        position VARCHAR(100) DEFAULT 'Member',
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        district VARCHAR(100) DEFAULT NULL,
        school VARCHAR(150) DEFAULT NULL,
        college VARCHAR(150) DEFAULT NULL,
        expectations TEXT DEFAULT NULL,
        memory TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $message .= "✔ Table `members` created.<br>";
    
    // Notices
    $pdo->exec("CREATE TABLE IF NOT EXISTS notices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        date DATE NOT NULL,
        attachment VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $message .= "✔ Table `notices` created.<br>";
    
    // Funding Campaigns
    $pdo->exec("CREATE TABLE IF NOT EXISTS funding_campaigns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        goal_amount DECIMAL(10, 2) NOT NULL,
        raised_amount DECIMAL(10, 2) DEFAULT 0.00,
        deadline DATE DEFAULT NULL,
        purpose VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $message .= "✔ Table `funding_campaigns` created.<br>";
    
    // Donations
    $pdo->exec("CREATE TABLE IF NOT EXISTS donations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT NULL,
        donor_name VARCHAR(100) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        transaction_id VARCHAR(100) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        date DATE NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (campaign_id) REFERENCES funding_campaigns(id) ON DELETE SET NULL
    ) ENGINE=InnoDB");
    $message .= "✔ Table `donations` created.<br>";
    
    // Contact Messages
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        message_type ENUM('general', 'job offer', 'emergency', 'funding request') DEFAULT 'general',
        department VARCHAR(100) DEFAULT NULL,
        message TEXT NOT NULL,
        status ENUM('pending', 'reviewed', 'published') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $message .= "✔ Table `contact_messages` created.<br>";
    
    // Job Offers
    $pdo->exec("CREATE TABLE IF NOT EXISTS job_offers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        company VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        requirements TEXT DEFAULT NULL,
        deadline DATE DEFAULT NULL,
        contact VARCHAR(255) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $message .= "✔ Table `job_offers` created.<br>";
    
    // Emergency Cases
    $pdo->exec("CREATE TABLE IF NOT EXISTS emergency_cases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        person_name VARCHAR(100) NOT NULL,
        situation TEXT NOT NULL,
        contact VARCHAR(255) NOT NULL,
        support_needed TEXT NOT NULL,
        status ENUM('active', 'resolved') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $message .= "✔ Table `emergency_cases` created.<br>";
    
    // Gallery
    $pdo->exec("CREATE TABLE IF NOT EXISTS gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) DEFAULT NULL,
        caption TEXT DEFAULT NULL,
        category VARCHAR(100) DEFAULT 'standard',
        filename VARCHAR(255) NOT NULL,
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $message .= "✔ Table `gallery` created.<br>";
    
    // Contact Info
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_info (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role VARCHAR(100) NOT NULL,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        email VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $message .= "✔ Table `contact_info` created.<br>";
    
    // 5. Seed Admins (exactly 4, predefined)
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        $admins = [
            [
                'name' => 'Chitrodip Sen',
                'email' => 'chitrodip@kuet.ac.bd',
                'password' => password_hash('Chitrodip@KUET20', PASSWORD_DEFAULT)
            ],
            [
                'name' => 'Preetom Roy Shaibal',
                'email' => 'preetom@kuet.ac.bd',
                'password' => password_hash('Preetom@KUET20', PASSWORD_DEFAULT)
            ],
            [
                'name' => 'Amit Kairy',
                'email' => 'amit@kuet.ac.bd',
                'password' => password_hash('Amit@KUET20', PASSWORD_DEFAULT)
            ],
            [
                'name' => 'Abu Omayer',
                'email' => 'omayer@kuet.ac.bd',
                'password' => password_hash('Omayer@KUET20', PASSWORD_DEFAULT)
            ]
        ];
        
        $insertAdmin = $pdo->prepare("INSERT INTO admins (name, email, password) VALUES (:name, :email, :password)");
        foreach ($admins as $admin) {
            $insertAdmin->execute($admin);
        }
        $message .= "✔ Predefined 4 admins successfully seeded.<br>";
    } else {
        $message .= "ℹ Predefined admins already seeded.<br>";
    }
    
    // 6. Seed Members (existing advisors, leaders, and general members)
    $stmt = $pdo->query("SELECT COUNT(*) FROM members");
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        $members = [
            // Advisors
            ['name' => 'Engr. Sarkar Raj Kishore Bappa', 'student_id' => '1423001', 'department' => 'BECM', 'batch' => '2K14', 'phone' => 'N/A', 'email' => 'sarkar@advisor.com', 'photo' => 'img/People/Student_Advisor_1.jpg', 'reason' => 'Advisor', 'position' => 'Advisor', 'status' => 'approved'],
            ['name' => 'Engr. Sowmitro Bhattacharjee', 'student_id' => '1505001', 'department' => 'ME', 'batch' => '2K15', 'phone' => 'N/A', 'email' => 'sowmitro@advisor.com', 'photo' => 'img/People/Student_Advisor_2.jpg', 'reason' => 'Advisor', 'position' => 'Advisor', 'status' => 'approved'],
            ['name' => 'Engr. Amit Deb Roy Anik', 'student_id' => '1603001', 'department' => 'EEE', 'batch' => '2K16', 'phone' => 'N/A', 'email' => 'amit.anik@advisor.com', 'photo' => 'img/People/Student_Advisor_3.jpg', 'reason' => 'Advisor', 'position' => 'Advisor', 'status' => 'approved'],
            ['name' => 'Engr. Altaf Hussain Chowdhury', 'student_id' => '1711001', 'department' => 'IEM', 'batch' => '2K17', 'phone' => 'N/A', 'email' => 'altaf@advisor.com', 'photo' => 'img/People/Student_Advisor_4.jpg', 'reason' => 'Advisor', 'position' => 'Advisor', 'status' => 'approved'],
            ['name' => 'Engr. Mohammad Mohiuddin', 'student_id' => '1801001', 'department' => 'CE', 'batch' => '2K18', 'phone' => 'N/A', 'email' => 'mohammad@advisor.com', 'photo' => 'img/People/Student_Advisor_5.jpg', 'reason' => 'Advisor', 'position' => 'Advisor', 'status' => 'approved'],
            ['name' => 'Engr. Bidyut Kumar Das', 'student_id' => '1907001', 'department' => 'CSE', 'batch' => '2K19', 'phone' => 'N/A', 'email' => 'bidyut@advisor.com', 'photo' => 'img/People/Student_Advisor_6.jpg', 'reason' => 'Advisor', 'position' => 'Advisor', 'status' => 'approved'],
            
            // President
            ['name' => 'Chitrodip Sen', 'student_id' => '2005001', 'department' => 'ME', 'batch' => '2K20', 'phone' => '+880 1644 139432', 'email' => 'chitrodip@kuet.ac.bd', 'photo' => 'img/People/President.jpg', 'reason' => 'President of the association', 'position' => 'President', 'status' => 'approved'],
            
            // Vice Presidents
            ['name' => 'Amit Kairy', 'student_id' => '2007002', 'department' => 'CSE', 'batch' => '2K20', 'phone' => 'N/A', 'email' => 'amit@kuet.ac.bd', 'photo' => 'img/People/Vice_president_1.jpg', 'reason' => 'Vice President', 'position' => 'Vice President', 'status' => 'approved'],
            ['name' => 'Abu Omayer', 'student_id' => '2007003', 'department' => 'CSE', 'batch' => '2K20', 'phone' => 'N/A', 'email' => 'omayer@kuet.ac.bd', 'photo' => 'img/People/Vice_president_2.jpg', 'reason' => 'Vice President', 'position' => 'Vice President', 'status' => 'approved'],
            
            // General Secretary
            ['name' => 'Preetom Roy Shaibal', 'student_id' => '2001002', 'department' => 'CE', 'batch' => '2K20', 'phone' => '+880 1619 500255', 'email' => 'preetom@kuet.ac.bd', 'photo' => 'img/People/General_Secretary.jpg', 'reason' => 'General Secretary', 'position' => 'General Secretary', 'status' => 'approved'],
            
            // Presidium Members
            ['name' => 'Niloy Das', 'student_id' => '2007005', 'department' => 'CSE', 'batch' => '2K20', 'phone' => 'N/A', 'email' => 'niloy@kuet.ac.bd', 'photo' => 'img/People/Presidium_Member_1.jpg', 'reason' => 'Presidium Member', 'position' => 'Presidium Member', 'status' => 'approved'],
            ['name' => 'Atikur Rahman Asib', 'student_id' => '2007006', 'department' => 'CSE', 'batch' => '2K20', 'phone' => 'N/A', 'email' => 'asib@kuet.ac.bd', 'photo' => 'img/People/Presidium_Member_2.jpg', 'reason' => 'Presidium Member', 'position' => 'Presidium Member', 'status' => 'approved'],
            ['name' => 'Hemayetul Islam Jami', 'student_id' => '2007007', 'department' => 'CSE', 'batch' => '2K20', 'phone' => 'N/A', 'email' => 'jami@kuet.ac.bd', 'photo' => 'img/People/Presidium_Member_3.jpg', 'reason' => 'Presidium Member', 'position' => 'Presidium Member', 'status' => 'approved'],
            ['name' => 'Syed Rukon', 'student_id' => '2019001', 'department' => 'URP', 'batch' => '2K20', 'phone' => 'N/A', 'email' => 'rukon@kuet.ac.bd', 'photo' => 'img/People/Presidium_Member_4.jpg', 'reason' => 'Presidium Member', 'position' => 'Presidium Member', 'status' => 'approved'],
            ['name' => 'Abdullah Al Shafi', 'student_id' => '2007010', 'department' => 'CSE', 'batch' => '2K20', 'phone' => 'N/A', 'email' => 'shafi@kuet.ac.bd', 'photo' => 'img/People/Presidium_Member_5.jpg', 'reason' => 'Presidium Member', 'position' => 'Presidium Member', 'status' => 'approved'],
            
            // Assistant General Secretaries
            ['name' => 'Satyaki Shome', 'student_id' => '2123001', 'department' => 'BECM', 'batch' => '2K21', 'phone' => 'N/A', 'email' => 'satyaki@kuet.ac.bd', 'photo' => 'img/People/Assistant_General_Secretary1.png', 'reason' => 'AGS', 'position' => 'Assistant General Secretary', 'status' => 'approved'],
            ['name' => 'Jakir Miah', 'student_id' => '2105002', 'department' => 'ME', 'batch' => '2K21', 'phone' => 'N/A', 'email' => 'jakir@kuet.ac.bd', 'photo' => 'img/People/Assistant_General_Secretary2.png', 'reason' => 'AGS', 'position' => 'Assistant General Secretary', 'status' => 'approved'],
            
            // Joint Secretary
            ['name' => 'Anmoy Datta', 'student_id' => '2121001', 'department' => 'TE', 'batch' => '2K21', 'phone' => 'N/A', 'email' => 'anmoy@kuet.ac.bd', 'photo' => 'img/People/Joint_Secratary.jpg', 'reason' => 'JS', 'position' => 'Joint Secretary', 'status' => 'approved'],
            
            // Organizing Secretaries
            ['name' => 'Shuvro Das', 'student_id' => '2125001', 'department' => 'LE', 'batch' => '2K21', 'phone' => 'N/A', 'email' => 'shuvro@kuet.ac.bd', 'photo' => 'img/People/Organizing_Secretary_1.png', 'reason' => 'OS', 'position' => 'Organizing Secretary', 'status' => 'approved'],
            ['name' => 'Rejaul Karim', 'student_id' => '2105005', 'department' => 'ME', 'batch' => '2K21', 'phone' => 'N/A', 'email' => 'rejaul@kuet.ac.bd', 'photo' => 'img/People/Organizing_Secretary_2.png', 'reason' => 'OS', 'position' => 'Organizing Secretary', 'status' => 'approved'],
            
            // Publicity Secretary
            ['name' => 'Sadia Mostafa', 'student_id' => '2107015', 'department' => 'CSE', 'batch' => '2K21', 'phone' => 'N/A', 'email' => 'sadia@kuet.ac.bd', 'photo' => 'img/People/Publicity_Secratary.jpg', 'reason' => 'Publicity Secretary', 'position' => 'Publicity Secretary', 'status' => 'approved'],
            
            // Cultural Secretary
            ['name' => 'Dip Shekhar Datta', 'student_id' => '2107020', 'department' => 'CSE', 'batch' => '2K21', 'phone' => 'N/A', 'email' => 'dip@kuet.ac.bd', 'photo' => 'img/People/Cultural_Secratary.jpg', 'reason' => 'Cultural Secretary', 'position' => 'Cultural Secretary', 'status' => 'approved'],
            
            // Treasurer
            ['name' => 'H.M. Azrof', 'student_id' => '2107030', 'department' => 'CSE', 'batch' => '2K21', 'phone' => 'N/A', 'email' => 'azrof@kuet.ac.bd', 'photo' => 'img/People/Treasour.png', 'reason' => 'Treasurer', 'position' => 'Treasurer', 'status' => 'approved'],
            
            // Members
            ['name' => 'Nurul Absar Shadik', 'student_id' => '2207001', 'department' => 'CSE', 'batch' => '2K22', 'phone' => 'N/A', 'email' => 'shadik@kuet.ac.bd', 'photo' => 'img/People/Member_1.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
            ['name' => 'Nurul Islam Rayhan', 'student_id' => '2203001', 'department' => 'EEE', 'batch' => '2K22', 'phone' => 'N/A', 'email' => 'rayhan@kuet.ac.bd', 'photo' => 'img/People/Member_2.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
            ['name' => 'MasumUR Rahman Fokhrul', 'student_id' => '2207005', 'department' => 'CSE', 'batch' => '2K22', 'phone' => 'N/A', 'email' => 'fokhrul@kuet.ac.bd', 'photo' => 'img/People/Member_3.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
            ['name' => 'Golam Hossain Rabbi', 'student_id' => '2227001', 'department' => 'MSE', 'batch' => '2K22', 'phone' => 'N/A', 'email' => 'rabbi@kuet.ac.bd', 'photo' => 'img/People/Member_4.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
            ['name' => 'Md. Bashir Ahmed', 'student_id' => '2203005', 'department' => 'EEE', 'batch' => '2K22', 'phone' => 'N/A', 'email' => 'bashir@kuet.ac.bd', 'photo' => 'img/People/Member_5.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
            ['name' => 'Sayanto Das', 'student_id' => '2209001', 'department' => 'ECE', 'batch' => '2K22', 'phone' => 'N/A', 'email' => 'sayanto@kuet.ac.bd', 'photo' => 'img/People/Member_11.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
            ['name' => 'Jahed Ahmed', 'student_id' => '2307001', 'department' => 'CSE', 'batch' => '2K23', 'phone' => 'N/A', 'email' => 'jahed@kuet.ac.bd', 'photo' => 'img/People/Member_6.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
            ['name' => 'Shahriar Anan', 'student_id' => '2305001', 'department' => 'ME', 'batch' => '2K23', 'phone' => 'N/A', 'email' => 'anan@kuet.ac.bd', 'photo' => 'img/People/Member_7.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
            ['name' => 'Tasnimul Hasan Rahat', 'student_id' => '2303001', 'department' => 'EEE', 'batch' => '2K23', 'phone' => 'N/A', 'email' => 'rahat@kuet.ac.bd', 'photo' => 'img/People/Member_8.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
            ['name' => 'Monojit Paul', 'student_id' => '2307005', 'department' => 'CSE', 'batch' => '2K23', 'phone' => 'N/A', 'email' => 'monojit@kuet.ac.bd', 'photo' => 'img/People/Member_9.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
            ['name' => 'Parthib Roy Dhrubo', 'student_id' => '2303005', 'department' => 'EEE', 'batch' => '2K23', 'phone' => 'N/A', 'email' => 'dhrubo@kuet.ac.bd', 'photo' => 'img/People/Member_10.jpg', 'reason' => 'Member', 'position' => 'Member', 'status' => 'approved'],
        ];
        
        $insertMember = $pdo->prepare("INSERT INTO members (name, student_id, department, batch, phone, email, photo, reason, position, status) 
                                       VALUES (:name, :student_id, :department, :batch, :phone, :email, :photo, :reason, :position, :status)");
        foreach ($members as $member) {
            $insertMember->execute($member);
        }
        $message .= "✔ Existing members (" . count($members) . ") successfully seeded.<br>";
    } else {
        $message .= "ℹ Members already seeded.<br>";
    }
    
    // 7. Seed Notices
    $stmt = $pdo->query("SELECT COUNT(*) FROM notices");
    if ($stmt->fetchColumn() == 0) {
        $notices = [
            ['title' => 'Admission Help', 'description' => 'Volunteers needed to assist applicants with seating, accommodation and transport guidance. Sign up or contact the General Secretary.', 'date' => '2026-07-12'],
            ['title' => 'General Meeting', 'description' => 'Monthly member meeting at KUET cultural hall. Agenda: event planning, fund allocation, and membership drives.', 'date' => '2026-06-20'],
            ['title' => 'BBQ Night & T-shirt Sale', 'description' => 'Pre-order official association T-shirts. Funds support event costs and community programs. See Funding section to donate or order.', 'date' => '2026-08-05'],
            ['title' => 'Emergency Relief Drive', 'description' => 'Ongoing collection for flood-affected families. Donations accepted (bank transfer / in-person / mobile). See Funding for details.', 'date' => '2026-06-11']
        ];
        
        $insertNotice = $pdo->prepare("INSERT INTO notices (title, description, date) VALUES (:title, :description, :date)");
        foreach ($notices as $notice) {
            $insertNotice->execute($notice);
        }
        $message .= "✔ Notices successfully seeded.<br>";
    }
    
    // 8. Seed Campaigns
    $stmt = $pdo->query("SELECT COUNT(*) FROM funding_campaigns");
    if ($stmt->fetchColumn() == 0) {
        $campaigns = [
            ['title' => 'Flood Relief Campaign', 'description' => 'Immediate relief packages (food, medicine, shelter) for flood-affected families in the Sylhet region.', 'goal_amount' => 100000.00, 'raised_amount' => 42000.00, 'deadline' => '2026-07-15', 'purpose' => 'Immediate Relief Packages'],
            ['title' => 'T-shirt Fundraiser (Event Merch)', 'description' => 'Pre-orders help cover event costs and provide subsidized T-shirts to members. Suggested donation: ৳300 per shirt.', 'goal_amount' => 30000.00, 'raised_amount' => 0.00, 'deadline' => '2026-08-01', 'purpose' => 'Event Costs']
        ];
        
        $insertCampaign = $pdo->prepare("INSERT INTO funding_campaigns (title, description, goal_amount, raised_amount, deadline, purpose) VALUES (:title, :description, :goal_amount, :raised_amount, :deadline, :purpose)");
        foreach ($campaigns as $camp) {
            $insertCampaign->execute($camp);
        }
        $message .= "✔ Funding campaigns successfully seeded.<br>";
    }
    
    // 9. Seed Job Offers
    $stmt = $pdo->query("SELECT COUNT(*) FROM job_offers");
    if ($stmt->fetchColumn() == 0) {
        $jobs = [
            ['title' => 'Alumni: Web Developer (Intern/Junior)', 'company' => 'TechSolutions Bangladesh', 'description' => 'Alumni opportunity offering internship or junior role for final-year students. Stipend negotiable.', 'requirements' => 'HTML, CSS, JavaScript, PHP, MySQL basics.', 'deadline' => '2026-07-30', 'contact' => 'sylhetassoc.kuet@gmail.com', 'status' => 'active'],
            ['title' => 'Part-time Tutor Positions', 'company' => 'Local Coaching/Home Tuition', 'description' => 'Senior students can apply to tutor juniors (Math, Physics, Programming).', 'requirements' => 'Strong foundations in KUET syllabus, teaching skills.', 'deadline' => '2026-06-30', 'contact' => 'sylhetassoc.kuet@gmail.com', 'status' => 'active'],
            ['title' => 'Event & Logistics Assistants', 'company' => 'Sylhet Association KUET', 'description' => 'Short-term paid roles for upcoming events — ideal for students seeking part-time work and experience.', 'requirements' => 'Event management skills, energy, and commitment.', 'deadline' => '2026-08-03', 'contact' => 'sylhetassoc.kuet@gmail.com', 'status' => 'active']
        ];
        
        $insertJob = $pdo->prepare("INSERT INTO job_offers (title, company, description, requirements, deadline, contact, status) VALUES (:title, :company, :description, :requirements, :deadline, :contact, :status)");
        foreach ($jobs as $job) {
            $insertJob->execute($job);
        }
        $message .= "✔ Job offers successfully seeded.<br>";
    }
    
    // 10. Seed Emergency Cases
    $stmt = $pdo->query("SELECT COUNT(*) FROM emergency_cases");
    if ($stmt->fetchColumn() == 0) {
        $emergencies = [
            ['person_name' => 'KUET Student Help Line', 'situation' => 'Blood donation requests & urgent coordination', 'contact' => '+880 1644 139432 (President)', 'support_needed' => 'Post requests in the Facebook group and contact management for rapid donor matching.', 'status' => 'active'],
            ['person_name' => 'Sylhet Flood Victims Support', 'situation' => 'Flood-affected families in rural Sylhet', 'contact' => '+880 1619 500255 (GS)', 'support_needed' => 'Financial donations and relief volunteers.', 'status' => 'active']
        ];
        
        $insertEmergency = $pdo->prepare("INSERT INTO emergency_cases (person_name, situation, contact, support_needed, status) VALUES (:person_name, :situation, :contact, :support_needed, :status)");
        foreach ($emergencies as $em) {
            $insertEmergency->execute($em);
        }
        $message .= "✔ Emergency cases successfully seeded.<br>";
    }
    
    // 11. Seed Gallery
    $stmt = $pdo->query("SELECT COUNT(*) FROM gallery");
    if ($stmt->fetchColumn() == 0) {
        $gallery = [
            ['title' => 'Campfire Gathering', 'caption' => 'Welcoming freshers and farewell to seniors', 'category' => 'featured', 'filename' => 'img/gallery/476246187_1172976380844428_1202847445971021648_n.jpg'],
            ['title' => 'Iftar Gathering 2025', 'caption' => 'Seniors and juniors coming together', 'category' => 'wide', 'filename' => 'img/gallery/480958285_3617746228523215_1006179549148778965_n.jpg'],
            ['title' => 'Sports Meet', 'caption' => 'Friendly football tournament', 'category' => 'standard', 'filename' => 'img/gallery/481014876_4094615337462841_9190408291765314636_n.jpg'],
            ['title' => 'BBQ Night', 'caption' => 'Lively moments and dinner', 'category' => 'tall', 'filename' => 'img/gallery/485909190_3643282762636228_2968566113269985611_n.jpg'],
            ['title' => 'Helping Hands Relief', 'caption' => 'Distributing aid', 'category' => 'standard', 'filename' => 'img/gallery/486408712_3642953786002459_5175100300069450156_n.jpg'],
            ['title' => 'Admission Desk', 'caption' => 'Guiding admission test candidates', 'category' => 'standard', 'filename' => 'img/gallery/486441221_3642954189335752_4906628804405918806_n.jpg'],
            ['title' => 'Executive Committee', 'caption' => 'Committee meeting', 'category' => 'wide', 'filename' => 'img/gallery/486546752_3643282759302895_8601998444467959151_n.jpg'],
            ['title' => 'Annual Picnic', 'caption' => 'Group photo at picnic site', 'category' => 'standard', 'filename' => 'img/gallery/489533973_2543308612682652_7032182446963710717_n.jpg']
        ];
        
        $insertGallery = $pdo->prepare("INSERT INTO gallery (title, caption, category, filename) VALUES (:title, :caption, :category, :filename)");
        foreach ($gallery as $gal) {
            $insertGallery->execute($gal);
        }
        $message .= "✔ Gallery images successfully seeded.<br>";
    }
    
    // 12. Seed Contact Info
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_info");
    if ($stmt->fetchColumn() == 0) {
        $contacts = [
            ['role' => 'President', 'name' => 'Chitrodip Sen', 'phone' => '+880 1644 139432', 'email' => 'chitrodip@kuet.ac.bd'],
            ['role' => 'General Secretary', 'name' => 'Preetom Roy Shaibal', 'phone' => '+880 1619 500255', 'email' => 'preetom@kuet.ac.bd'],
            ['role' => 'Treasurer', 'name' => 'H.M. Azrof', 'phone' => 'N/A', 'email' => 'azrof@kuet.ac.bd']
        ];
        
        $insertContact = $pdo->prepare("INSERT INTO contact_info (role, name, phone, email) VALUES (:role, :name, :phone, :email)");
        foreach ($contacts as $cont) {
            $insertContact->execute($cont);
        }
        $message .= "✔ Contact info successfully seeded.<br>";
    }
    
} catch (PDOException $e) {
    $error = true;
    $message .= "❌ Database Error: " . $e->getMessage() . "<br>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Sylhet Association KUET</title>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f0f4f1;
            color: #2e4a3c;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(12, 85, 43, 0.15);
            padding: 30px;
            max-width: 600px;
            width: 100%;
        }
        h2 {
            color: #0c552b;
            margin-top: 0;
            border-bottom: 2px solid #0f7a3b;
            padding-bottom: 10px;
        }
        .log-box {
            background-color: #f9fbf9;
            border: 1px solid #d2e4d5;
            border-radius: 8px;
            padding: 15px;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 20px;
            max-height: 350px;
            overflow-y: auto;
        }
        .btn {
            display: inline-block;
            background-color: #0c552b;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.2s ease;
        }
        .btn:hover {
            background-color: #0f7a3b;
        }
        .status {
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .fail {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sylhet Association KUET Backend Setup</h2>
        <?php if ($error): ?>
            <div class="status fail">Setup encountered errors. Please check the log below.</div>
        <?php else: ?>
            <div class="status success">Setup completed successfully! Database is ready.</div>
        <?php endif; ?>
        
        <div class="log-box">
            <?php echo $message; ?>
        </div>
        
        <?php if (!$error): ?>
            <a href="login.php" class="btn">Proceed to Admin Login</a>
        <?php else: ?>
            <a href="setup.php" class="btn" style="background-color: #b71c1c;">Retry Setup</a>
        <?php endif; ?>
    </div>
</body>
</html>
