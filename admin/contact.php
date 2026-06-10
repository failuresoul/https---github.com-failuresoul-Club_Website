<?php
require_once 'header.php';

$message = "";
$msg_type = "success";

if (!$pdo) {
    echo "<div class='toast-msg toast-error'>Database connection failed.</div>";
    require_once 'footer.php';
    exit();
}

// Handle Update Contact Info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $info_id = (int)$_POST['info_id'];
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);

    try {
        $stmt = $pdo->prepare("UPDATE contact_info SET name = :name, phone = :phone, email = :email WHERE id = :id");
        $stmt->execute([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'id' => $info_id
        ]);
        $_SESSION['msg'] = "Contact details updated successfully!";
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }
    redirect('contact.php');
}

// Handle GET Actions (Delete, Toggle message status)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitize($_GET['action']);
    $id = (int)$_GET['id'];

    try {
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['msg'] = "Message deleted successfully.";
            $_SESSION['msg_type'] = "success";
        } elseif ($action === 'review') {
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'reviewed' WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['msg'] = "Message marked as reviewed.";
            $_SESSION['msg_type'] = "success";
        } elseif ($action === 'pending') {
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'pending' WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['msg'] = "Message marked as pending.";
            $_SESSION['msg_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }
    redirect('contact.php');
}

// Fetch all contact messages
try {
    $inquiries = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $inquiries = [];
}

// Fetch current contact info (President, GS, Treasurer, etc.)
try {
    $contact_details = $pdo->query("SELECT * FROM contact_info ORDER BY id ASC")->fetchAll();
} catch (PDOException $e) {
    $contact_details = [];
}

// Flash messages
if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    $msg_type = $_SESSION['msg_type'];
    unset($_SESSION['msg']);
    unset($_SESSION['msg_type']);
}
?>

<?php if (!empty($message)): ?>
    <div class="toast-msg toast-<?php echo $msg_type; ?>">
        <span><?php echo $message; ?></span>
    </div>
<?php endif; ?>

<!-- Main Grid Layout -->
<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; align-items: flex-start;">
    
    <!-- 1. EDIT CONTACT INFO CARDS -->
    <div>
        <div class="panel-card" style="margin-bottom: 25px;">
            <h2>Association Representatives</h2>
            <p style="font-size: 13px; color: #666; line-height: 1.4; margin-top: 5px;">
                Update the official contact info. These values are displayed dynamically on the public homepage.
            </p>
        </div>
        
        <?php foreach ($contact_details as $info): ?>
            <div class="panel-card" style="margin-bottom: 20px;">
                <h3 style="color: #0c552b; margin: 0 0 15px 0; font-size: 1.15rem; font-weight: bold; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                    Role: <?php echo htmlspecialchars($info['role']); ?>
                </h3>
                <form action="contact.php" method="POST">
                    <input type="hidden" name="info_id" value="<?php echo $info['id']; ?>">
                    
                    <div class="admin-form-group">
                        <label>Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($info['name']); ?>" required>
                    </div>
                    
                    <div class="admin-form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($info['phone']); ?>" required>
                    </div>
                    
                    <div class="admin-form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($info['email']); ?>" required>
                    </div>
                    
                    <button type="submit" name="update_info" class="btn btn-primary btn-sm" style="margin-top: 8px;">Save Changes</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 2. MESSAGES LIST -->
    <div class="panel-card">
        <h2>Visitor Inquiries (<?php echo count($inquiries); ?>)</h2>
        <div class="table-responsive" style="margin-top: 15px;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Type / Dept</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inquiries)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #7f8c8d; padding: 20px;">No messages received yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inquiries as $msg): ?>
                            <tr>
                                <td style="white-space: nowrap; font-size: 13px;">
                                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong><br>
                                    <span><?php echo htmlspecialchars($msg['email']); ?></span><br>
                                    <span style="color: #666; font-size: 11px;"><?php echo htmlspecialchars($msg['phone']); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo ($msg['status'] == 'pending') ? 'pending' : 'reviewed'; ?>" style="font-size: 10px; margin-bottom: 5px;">
                                        <?php echo htmlspecialchars($msg['message_type']); ?>
                                    </span>
                                    <br>
                                    <span style="font-size: 11px; color: #666;"><?php echo htmlspecialchars($msg['department']); ?></span>
                                </td>
                                <td>
                                    <div style="font-size: 13px; max-width: 250px; max-height: 100px; overflow-y: auto; color: #333; line-height: 1.4;">
                                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                    </div>
                                    <span style="font-size: 10px; color: #999; display: block; margin-top: 6px;">
                                        Received: <?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo ($msg['status'] === 'pending') ? 'pending' : 'approved'; ?>">
                                        <?php echo htmlspecialchars($msg['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" style="flex-direction: column; gap: 4px;">
                                        <?php if ($msg['status'] === 'pending'): ?>
                                            <a href="contact.php?action=review&id=<?php echo $msg['id']; ?>" class="btn btn-primary btn-sm" style="padding: 4px 8px;">Mark Read</a>
                                        <?php else: ?>
                                            <a href="contact.php?action=pending&id=<?php echo $msg['id']; ?>" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">Mark Unread</a>
                                        <?php endif; ?>
                                        <a href="contact.php?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-danger btn-sm" style="padding: 4px 8px;" onclick="return confirm('Delete this inquiry?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
require_once 'footer.php';
?>
