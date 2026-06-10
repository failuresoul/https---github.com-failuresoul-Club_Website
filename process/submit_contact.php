<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo) {
        $_SESSION['msg_status'] = 'error';
        $_SESSION['msg_text'] = 'Database connection error. Please try again later.';
        redirect('../index.php');
    }

    // Sanitize input data
    $name = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $message_type = isset($_POST['message_type']) ? sanitize($_POST['message_type']) : 'general';
    $department = isset($_POST['department']) ? sanitize($_POST['department']) : '';
    $message = sanitize($_POST['message']);

    // Allowed message types
    $allowed_types = ['general', 'job offer', 'emergency', 'funding request'];
    if (!in_array($message_type, $allowed_types)) {
        $message_type = 'general';
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, message_type, department, message, status) 
                               VALUES (:name, :email, :phone, :message_type, :department, :message, 'pending')");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message_type' => $message_type,
            'department' => $department,
            'message' => $message
        ]);

        $_SESSION['msg_status'] = 'success';
        $_SESSION['msg_text'] = 'Your message has been sent successfully! We will review it shortly.';
    } catch (PDOException $e) {
        $_SESSION['msg_status'] = 'error';
        $_SESSION['msg_text'] = 'Database error: ' . $e->getMessage();
    }

    redirect('../index.php#Contact');
} else {
    redirect('../index.php');
}
?>
