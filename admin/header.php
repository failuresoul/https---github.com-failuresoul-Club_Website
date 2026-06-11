<?php
ob_start();
require_once '../config.php';
check_admin_login();

// Get current page to apply active class in sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sylhet Association KUET</title>
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime('../css/admin.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Additional inline icons/helpers */
        .sidebar-menu a svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <span>Sylhet Assoc. Admin</span>
            </div>
            <ul class="sidebar-menu">
                <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <a href="index.php">
                        <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'members.php') ? 'active' : ''; ?>">
                    <a href="members.php">
                        <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        <span>Members</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'notices.php') ? 'active' : ''; ?>">
                    <a href="notices.php">
                        <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/></svg>
                        <span>Notices</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'funding.php') ? 'active' : ''; ?>">
                    <a href="funding.php">
                        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-4 6h-4v2h4v2h-4v2h4v2H9V7h6v2z"/></svg>
                        <span>Campaigns</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'donations.php') ? 'active' : ''; ?>">
                    <a href="donations.php">
                        <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        <span>Donations</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'jobs.php') ? 'active' : ''; ?>">
                    <a href="jobs.php">
                        <svg viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>
                        <span>Job Board</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'emergency.php') ? 'active' : ''; ?>">
                    <a href="emergency.php">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        <span>Emergencies</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'gallery.php') ? 'active' : ''; ?>">
                    <a href="gallery.php">
                        <svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                        <span>Gallery</span>
                    </a>
                </li>
                <li class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">
                    <a href="contact.php">
                        <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        <span>Inquiries</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M13 3h-2v10h2V3zm4.78 2.22l-1.42 1.42C18.43 8.35 19 10.11 19 12c0 3.87-3.13 7-7 7s-7-3.13-7-7c0-1.89.57-3.65 1.64-5.36L5.22 5.22C3.8 6.96 3 9.38 3 12c0 4.97 4.03 9 9 9s9-4.03 9-9c0-2.62-.8-5.04-2.22-6.78z"/></svg>
                    <span>Log Out</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="top-header">
                <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                    <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
                </button>
                <h1>Sylhet Association of KUET</h1>
                <div class="admin-profile">
                    <span>Admin: <?php echo $_SESSION['admin_name']; ?></span>
                </div>
            </header>
