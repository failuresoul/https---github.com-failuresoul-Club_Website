<?php
require_once '../config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    redirect('index.php');
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo) {
        $error_msg = "Database connection error. Setup might not be complete.";
    } else {
        $email = sanitize($_POST['email']);
        $password = trim($_POST['password']);

        if (empty($email) || empty($password)) {
            $error_msg = "All fields are required!";
        } else {
            try {
                // Fetch admin from database
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
                $stmt->execute(['email' => $email]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password'])) {
                    // Start authenticated session
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_email'] = $admin['email'];

                    redirect('index.php');
                } else {
                    $error_msg = "Invalid email or password!";
                }
            } catch (PDOException $e) {
                $error_msg = "Database query error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sylhet Association KUET</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-card">
        <img src="../img/logo.png" alt="Sylhet Association KUET Logo" class="logo" onerror="this.src='../img/logo.png'">
        <h2>Admin Portal</h2>
        <p>Sylhet Association of KUET</p>
        
        <?php if (!empty($error_msg)): ?>
            <div class="toast-msg toast-error" style="font-size: 14px; margin-bottom: 20px;">
                <span><?php echo $error_msg; ?></span>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="admin-form-group" style="text-align: left;">
                <label for="email">Admin Email</label>
                <input type="email" id="email" name="email" placeholder="example@kuet.ac.bd" required>
            </div>
            
            <div class="admin-form-group" style="text-align: left; margin-bottom: 25px;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;">Login</button>
        </form>
    </div>
</body>
</html>
