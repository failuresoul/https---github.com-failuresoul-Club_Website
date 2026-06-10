<?php
require_once 'header.php';

$message = "";
$msg_type = "success";

if (!$pdo) {
    echo "<div class='toast-msg toast-error'>Database connection failed.</div>";
    require_once 'footer.php';
    exit();
}

// Handle Add/Edit Campaign Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_campaign'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $goal_amount = (float)$_POST['goal_amount'];
    $purpose = sanitize($_POST['purpose']);
    $deadline = !empty($_POST['deadline']) ? sanitize($_POST['deadline']) : null;
    $campaign_id = isset($_POST['campaign_id']) ? (int)$_POST['campaign_id'] : 0;

    try {
        if ($campaign_id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE funding_campaigns SET title = :title, description = :description, goal_amount = :goal_amount, purpose = :purpose, deadline = :deadline WHERE id = :id");
            $stmt->execute([
                'title' => $title,
                'description' => $description,
                'goal_amount' => $goal_amount,
                'purpose' => $purpose,
                'deadline' => $deadline,
                'id' => $campaign_id
            ]);
            $_SESSION['msg'] = "Campaign updated successfully!";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO funding_campaigns (title, description, goal_amount, raised_amount, purpose, deadline) VALUES (:title, :description, :goal_amount, 0.00, :purpose, :deadline)");
            $stmt->execute([
                'title' => $title,
                'description' => $description,
                'goal_amount' => $goal_amount,
                'purpose' => $purpose,
                'deadline' => $deadline
            ]);
            $_SESSION['msg'] = "Funding campaign created successfully!";
        }
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Database Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }
    
    redirect('funding.php');
}

// Handle GET Actions (Delete/Edit trigger)
$edit_camp = null;
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitize($_GET['action']);
    $id = (int)$_GET['id'];

    if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM funding_campaigns WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['msg'] = "Campaign deleted successfully!";
            $_SESSION['msg_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['msg'] = "Error: " . $e->getMessage();
            $_SESSION['msg_type'] = "error";
        }
        redirect('funding.php');
    } elseif ($action === 'edit') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM funding_campaigns WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $edit_camp = $stmt->fetch();
        } catch (PDOException $e) {
            // silently fail
        }
    }
}

// Fetch all campaigns
try {
    $campaigns = $pdo->query("SELECT * FROM funding_campaigns ORDER BY deadline ASC, created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $campaigns = [];
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
    
    <!-- Campaign Create / Edit Form -->
    <div class="panel-card">
        <h2><?php echo $edit_camp ? 'Edit Campaign' : 'Create Campaign'; ?></h2>
        <form action="funding.php" method="POST" style="margin-top: 15px;">
            <?php if ($edit_camp): ?>
                <input type="hidden" name="campaign_id" value="<?php echo $edit_camp['id']; ?>">
            <?php endif; ?>
            
            <div class="admin-form-group">
                <label for="title">Campaign Title</label>
                <input type="text" id="title" name="title" value="<?php echo $edit_camp ? htmlspecialchars($edit_camp['title']) : ''; ?>" placeholder="e.g., Flood Relief Drive" required>
            </div>
            
            <div class="admin-form-group">
                <label for="goal_amount">Goal Amount (৳)</label>
                <input type="number" id="goal_amount" name="goal_amount" min="1" step="0.01" value="<?php echo $edit_camp ? htmlspecialchars($edit_camp['goal_amount']) : ''; ?>" placeholder="e.g., 100000" required>
            </div>
            
            <div class="admin-form-group">
                <label for="purpose">Campaign Purpose</label>
                <input type="text" id="purpose" name="purpose" value="<?php echo $edit_camp ? htmlspecialchars($edit_camp['purpose']) : ''; ?>" placeholder="e.g., Dry food, blankets, medicine distribution" required>
            </div>
            
            <div class="admin-form-group">
                <label for="deadline">Deadline (Optional)</label>
                <input type="date" id="deadline" name="deadline" value="<?php echo ($edit_camp && $edit_camp['deadline']) ? htmlspecialchars($edit_camp['deadline']) : ''; ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="description">Campaign Description</label>
                <textarea id="description" name="description" rows="5" placeholder="Detailed campaign notes..." required><?php echo $edit_camp ? htmlspecialchars($edit_camp['description']) : ''; ?></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" name="save_campaign" class="btn btn-primary"><?php echo $edit_camp ? 'Update Campaign' : 'Create Campaign'; ?></button>
                <?php if ($edit_camp): ?>
                    <a href="funding.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Campaigns List -->
    <div class="panel-card">
        <h2>Active Funding Campaigns (<?php echo count($campaigns); ?>)</h2>
        <div class="table-responsive" style="margin-top: 15px;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Goal / Raised</th>
                        <th>Progress</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($campaigns)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #7f8c8d; padding: 20px;">No campaigns available.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($campaigns as $c): ?>
                            <?php 
                            $percent = $c['goal_amount'] > 0 ? round(($c['raised_amount'] / $c['goal_amount']) * 100) : 0;
                            if ($percent > 100) $percent = 100;
                            ?>
                            <tr>
                                <td>
                                    <strong style="color: #0c552b; font-size: 1rem;"><?php echo htmlspecialchars($c['title']); ?></strong><br>
                                    <span style="font-size: 12px; color: #666;">Purpose: <?php echo htmlspecialchars($c['purpose']); ?></span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <strong>Raised:</strong> ৳<?php echo number_format($c['raised_amount']); ?><br>
                                    <strong>Goal:</strong> ৳<?php echo number_format($c['goal_amount']); ?>
                                </td>
                                <td>
                                    <div style="background: #e2e2e2; border-radius: 99px; height: 8px; width: 100px; overflow: hidden; margin-bottom: 4px;">
                                        <div style="background: linear-gradient(90deg, #f1c40f, #0f7a3b); height: 100%; width: <?php echo $percent; ?>%;"></div>
                                    </div>
                                    <span style="font-size: 11px; font-weight: bold; color: #444;"><?php echo $percent; ?>% completed</span>
                                </td>
                                <td>
                                    <?php echo $c['deadline'] ? date('M d, Y', strtotime($c['deadline'])) : '<span style="color:#aaa;">Open</span>'; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="funding.php?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="funding.php?action=delete&id=<?php echo $c['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this campaign? It will unlink related donations.')">Delete</a>
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
