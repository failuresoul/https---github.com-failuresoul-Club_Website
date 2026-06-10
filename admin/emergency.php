<?php
require_once 'header.php';

$message = "";
$msg_type = "success";

if (!$pdo) {
    echo "<div class='toast-msg toast-error'>Database connection failed.</div>";
    require_once 'footer.php';
    exit();
}

// Handle Add/Edit Emergency Case
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_emergency'])) {
    $person_name = sanitize($_POST['person_name']);
    $situation = sanitize($_POST['situation']);
    $contact = sanitize($_POST['contact']);
    $support_needed = sanitize($_POST['support_needed']);
    $status = sanitize($_POST['status']);
    $case_id = isset($_POST['case_id']) ? (int)$_POST['case_id'] : 0;

    if (!in_array($status, ['active', 'resolved'])) {
        $status = 'active';
    }

    try {
        if ($case_id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE emergency_cases SET person_name = :person_name, situation = :situation, contact = :contact, support_needed = :support_needed, status = :status WHERE id = :id");
            $stmt->execute([
                'person_name' => $person_name,
                'situation' => $situation,
                'contact' => $contact,
                'support_needed' => $support_needed,
                'status' => $status,
                'id' => $case_id
            ]);
            $_SESSION['msg'] = "Emergency case updated successfully!";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO emergency_cases (person_name, situation, contact, support_needed, status) VALUES (:person_name, :situation, :contact, :support_needed, :status)");
            $stmt->execute([
                'person_name' => $person_name,
                'situation' => $situation,
                'contact' => $contact,
                'support_needed' => $support_needed,
                'status' => $status
            ]);
            $_SESSION['msg'] = "Emergency case added successfully!";
        }
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Database Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    redirect('emergency.php');
}

// Handle GET Actions (Delete, Toggle Status, Edit trigger)
$edit_case = null;
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitize($_GET['action']);
    $id = (int)$_GET['id'];

    if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM emergency_cases WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['msg'] = "Emergency case deleted successfully!";
            $_SESSION['msg_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['msg'] = "Error: " . $e->getMessage();
            $_SESSION['msg_type'] = "error";
        }
        redirect('emergency.php');
    } elseif ($action === 'toggle') {
        try {
            $stmt = $pdo->prepare("UPDATE emergency_cases SET status = IF(status = 'active', 'resolved', 'active') WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['msg'] = "Case status toggled successfully!";
            $_SESSION['msg_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['msg'] = "Error: " . $e->getMessage();
            $_SESSION['msg_type'] = "error";
        }
        redirect('emergency.php');
    } elseif ($action === 'edit') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM emergency_cases WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $edit_case = $stmt->fetch();
        } catch (PDOException $e) {
            // silently fail
        }
    }
}

// Fetch all emergency cases
try {
    $cases = $pdo->query("SELECT * FROM emergency_cases ORDER BY status ASC, created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $cases = [];
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

<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; align-items: flex-start;">
    
    <!-- Emergency Add/Edit Form -->
    <div class="panel-card">
        <h2><?php echo $edit_case ? 'Edit Emergency Case' : 'Register Emergency Case'; ?></h2>
        <form action="emergency.php" method="POST" style="margin-top: 15px;">
            <?php if ($edit_case): ?>
                <input type="hidden" name="case_id" value="<?php echo $edit_case['id']; ?>">
            <?php endif; ?>
            
            <div class="admin-form-group">
                <label for="person_name">Person / Case Name</label>
                <input type="text" id="person_name" name="person_name" value="<?php echo $edit_case ? htmlspecialchars($edit_case['person_name']) : ''; ?>" placeholder="e.g., Blood Request O+ve or Flood Victims" required>
            </div>
            
            <div class="admin-form-group">
                <label for="contact">Urgent Contact Info</label>
                <input type="text" id="contact" name="contact" value="<?php echo $edit_case ? htmlspecialchars($edit_case['contact']) : ''; ?>" placeholder="e.g., Phone number or FB profile link" required>
            </div>
            
            <div class="admin-form-group">
                <label for="status">Case Status</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo ($edit_case && $edit_case['status'] == 'active') ? 'selected' : ''; ?>>Active (Needs urgent support)</option>
                    <option value="resolved" <?php echo ($edit_case && $edit_case['status'] == 'resolved') ? 'selected' : ''; ?>>Resolved (Close case)</option>
                </select>
            </div>
            
            <div class="admin-form-group">
                <label for="situation">Situation Description</label>
                <textarea id="situation" name="situation" rows="3" placeholder="Explain the situation briefly..." required><?php echo $edit_case ? htmlspecialchars($edit_case['situation']) : ''; ?></textarea>
            </div>
            
            <div class="admin-form-group">
                <label for="support_needed">Support Needed details</label>
                <textarea id="support_needed" name="support_needed" rows="3" placeholder="e.g., 2 bags of blood required by tonight, or financial support..." required><?php echo $edit_case ? htmlspecialchars($edit_case['support_needed']) : ''; ?></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" name="save_emergency" class="btn btn-primary"><?php echo $edit_case ? 'Update Case' : 'Register Case'; ?></button>
                <?php if ($edit_case): ?>
                    <a href="emergency.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Emergencies List -->
    <div class="panel-card">
        <h2>Emergency Support Board (<?php echo count($cases); ?>)</h2>
        <div class="table-responsive" style="margin-top: 15px;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Case / Person</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cases)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #7f8c8d; padding: 20px;">No emergency cases active.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cases as $c): ?>
                            <tr>
                                <td>
                                    <strong style="color: #7a1b1b; font-size: 1.05rem;"><?php echo htmlspecialchars($c['person_name']); ?></strong><br>
                                    <span style="font-size: 12px; color: #666;">Contact: <strong><?php echo htmlspecialchars($c['contact']); ?></strong></span>
                                </td>
                                <td>
                                    <div style="font-size: 13px; line-height: 1.4; max-height: 120px; overflow-y: auto;">
                                        <strong>Situation:</strong> <?php echo htmlspecialchars($c['situation']); ?><br>
                                        <strong>Needed:</strong> <span style="font-style: italic; color: #7a1b1b; font-weight: bold;"><?php echo htmlspecialchars($c['support_needed']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <a href="emergency.php?action=toggle&id=<?php echo $c['id']; ?>" class="badge badge-<?php echo $c['status']; ?>" style="border: none; cursor: pointer; text-decoration: none;">
                                        <?php echo htmlspecialchars($c['status']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="emergency.php?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="emergency.php?action=delete&id=<?php echo $c['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this case?')">Delete</a>
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
