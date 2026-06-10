<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo) {
        $_SESSION['msg_status'] = 'error';
        $_SESSION['msg_text'] = 'Database connection error. Please try again later.';
        redirect('../index.php');
    }

    // Sanitize input data
    $donor_name = sanitize($_POST['donor_name']);
    $campaign_id = (int)$_POST['campaign_id'];
    $amount = (float)$_POST['amount'];
    $transaction_id = sanitize($_POST['transaction_id']);
    $phone = sanitize($_POST['phone']);
    $date = date('Y-m-d'); // Today's date

    if ($amount <= 0) {
        $_SESSION['msg_status'] = 'error';
        $_SESSION['msg_text'] = 'Donation amount must be greater than zero!';
        redirect('../index.php#Funding');
    }

    try {
        // Confirm campaign exists
        $campCheck = $pdo->prepare("SELECT id FROM funding_campaigns WHERE id = :id");
        $campCheck->execute(['id' => $campaign_id]);
        if (!$campCheck->fetch()) {
            $_SESSION['msg_status'] = 'error';
            $_SESSION['msg_text'] = 'Selected campaign does not exist!';
            redirect('../index.php#Funding');
        }

        // Insert donation record as pending
        $stmt = $pdo->prepare("INSERT INTO donations (campaign_id, donor_name, amount, transaction_id, phone, date, status) 
                               VALUES (:campaign_id, :donor_name, :amount, :transaction_id, :phone, :date, 'pending')");
        $stmt->execute([
            'campaign_id' => $campaign_id,
            'donor_name' => $donor_name,
            'amount' => $amount,
            'transaction_id' => $transaction_id,
            'phone' => $phone,
            'date' => $date
        ]);

        $_SESSION['msg_status'] = 'success';
        $_SESSION['msg_text'] = 'Your donation has been recorded! Thank you. It will be verified by the Treasurer and updated soon.';
    } catch (PDOException $e) {
        $_SESSION['msg_status'] = 'error';
        $_SESSION['msg_text'] = 'Database error: ' . $e->getMessage();
    }

    redirect('../index.php#Funding');
} else {
    redirect('../index.php');
}
?>
