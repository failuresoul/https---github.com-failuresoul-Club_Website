<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if PDO is active
    if (!$pdo) {
        $_SESSION['msg_status'] = 'error';
        $_SESSION['msg_text'] = 'Database connection error. Please try again later.';
        redirect('../index.php');
    }

    // Sanitize input data
    $name = sanitize($_POST['name']);
    $department = sanitize($_POST['department']);
    $batch = sanitize($_POST['batch']);
    $student_id = sanitize($_POST['roll']); // roll maps to student_id
    $district = sanitize($_POST['district']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $reason = sanitize($_POST['connect']); // connect maps to reason
    $school = sanitize($_POST['school']);
    $college = sanitize($_POST['college']);
    $expectations = sanitize($_POST['expect']); // expect maps to expectations
    $memory = sanitize($_POST['memory']);

    // Check if student ID already exists
    $checkQuery = $pdo->prepare("SELECT id FROM members WHERE student_id = :student_id");
    $checkQuery->execute(['student_id' => $student_id]);
    if ($checkQuery->fetch()) {
        $_SESSION['msg_status'] = 'error';
        $_SESSION['msg_text'] = 'An application with this Student ID already exists!';
        redirect('../index.php');
    }

    $photo_path = null;

    // Handle Photo Upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_name = $_FILES['photo']['name'];
        $file_size = $_FILES['photo']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Define allowed extensions and max size (5MB)
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file_ext, $allowed_exts)) {
            $_SESSION['msg_status'] = 'error';
            $_SESSION['msg_text'] = 'Only JPG, JPEG, PNG, GIF, and WEBP photos are allowed!';
            redirect('../index.php');
        }

        if ($file_size > $max_size) {
            $_SESSION['msg_status'] = 'error';
            $_SESSION['msg_text'] = 'Photo file size must not exceed 5MB!';
            redirect('../index.php');
        }

        // Create uploads directory if not exists
        $upload_dir = '../uploads/members/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique name for the file
        $new_file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $student_id) . '.' . $file_ext;
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $dest_path)) {
            // Store relative path in DB
            $photo_path = 'uploads/members/' . $new_file_name;
        } else {
            $_SESSION['msg_status'] = 'error';
            $_SESSION['msg_text'] = 'Failed to upload photo. Please check folder permissions.';
            redirect('../index.php');
        }
    } else {
        $_SESSION['msg_status'] = 'error';
        $_SESSION['msg_text'] = 'A profile photo upload is required!';
        redirect('../index.php');
    }

    try {
        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO members 
            (name, student_id, department, batch, phone, email, photo, reason, position, status, district, school, college, expectations, memory) 
            VALUES 
            (:name, :student_id, :department, :batch, :phone, :email, :photo, :reason, 'Member', 'pending', :district, :school, :college, :expectations, :memory)");
        
        $stmt->execute([
            'name' => $name,
            'student_id' => $student_id,
            'department' => $department,
            'batch' => $batch,
            'phone' => $phone,
            'email' => $email,
            'photo' => $photo_path,
            'reason' => $reason,
            'district' => $district,
            'school' => $school,
            'college' => $college,
            'expectations' => $expectations,
            'memory' => $memory
        ]);

        $_SESSION['msg_status'] = 'success';
        $_SESSION['msg_text'] = 'Your application has been submitted successfully and is pending admin approval!';
    } catch (PDOException $e) {
        $_SESSION['msg_status'] = 'error';
        $_SESSION['msg_text'] = 'Database error: ' . $e->getMessage();
    }

    redirect('../index.php');
} else {
    redirect('../index.php');
}
?>
