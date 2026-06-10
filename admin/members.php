<?php
require_once 'header.php';

$message = "";
$status_type = "success";

if (!$pdo) {
    echo "<div class='toast-msg toast-error'>Database connection failed.</div>";
    require_once 'footer.php';
    exit();
}

// Handle GET Actions (Approve, Reject, Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitize($_GET['action']);
    $member_id = (int)$_GET['id'];

    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE members SET status = 'approved' WHERE id = :id");
            $stmt->execute(['id' => $member_id]);
            $_SESSION['msg'] = "Member approved successfully!";
            $_SESSION['msg_type'] = "success";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE members SET status = 'rejected' WHERE id = :id");
            $stmt->execute(['id' => $member_id]);
            $_SESSION['msg'] = "Member application rejected.";
            $_SESSION['msg_type'] = "warning";
        } elseif ($action === 'delete') {
            // Delete photo file if exists
            $stmt_photo = $pdo->prepare("SELECT photo FROM members WHERE id = :id");
            $stmt_photo->execute(['id' => $member_id]);
            $photo = $stmt_photo->fetchColumn();
            
            if ($photo && file_exists('../' . $photo) && strpos($photo, 'uploads/') === 0) {
                unlink('../' . $photo);
            }

            $stmt = $pdo->prepare("DELETE FROM members WHERE id = :id");
            $stmt->execute(['id' => $member_id]);
            $_SESSION['msg'] = "Member record deleted successfully.";
            $_SESSION['msg_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }
    
    redirect('members.php');
}

// Handle POST Actions (Update Position)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_position'])) {
    $member_id = (int)$_POST['member_id'];
    $position = sanitize($_POST['position']);

    try {
        $stmt = $pdo->prepare("UPDATE members SET position = :position WHERE id = :id");
        $stmt->execute(['position' => $position, 'id' => $member_id]);
        $_SESSION['msg'] = "Member position updated successfully!";
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    redirect('members.php');
}

// Fetch all members grouped by status
try {
    $pending_members = $pdo->query("SELECT * FROM members WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
    $approved_members = $pdo->query("SELECT * FROM members WHERE status = 'approved' ORDER BY batch DESC, position ASC, name ASC")->fetchAll();
    $rejected_members = $pdo->query("SELECT * FROM members WHERE status = 'rejected' ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $pending_members = [];
    $approved_members = [];
    $rejected_members = [];
}

// Flash messages
if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    $status_type = $_SESSION['msg_type'];
    unset($_SESSION['msg']);
    unset($_SESSION['msg_type']);
}
?>

<?php if (!empty($message)): ?>
    <div class="toast-msg toast-<?php echo $status_type === 'warning' ? 'error' : $status_type; ?>">
        <span><?php echo $message; ?></span>
    </div>
<?php endif; ?>

<!-- 1. PENDING APPLICATIONS SECTION -->
<div class="panel-card">
    <div class="panel-card-header">
        <h2>Pending Applications (<?php echo count($pending_members); ?>)</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name / Roll</th>
                    <th>Dept / Batch</th>
                    <th>Phone / Email</th>
                    <th>Connection Reason</th>
                    <th>Details</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending_members)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #7f8c8d; padding: 20px;">No pending member applications.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pending_members as $m): ?>
                        <tr>
                            <td>
                                <?php if ($m['photo']): ?>
                                    <img src="../<?php echo htmlspecialchars($m['photo']); ?>" alt="Photo" width="60" height="60">
                                <?php else: ?>
                                    <div style="width: 60px; height: 60px; background: #e2e2e2; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 11px;">No Photo</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($m['name']); ?></strong><br>
                                <span style="font-size: 12px; color: #666;">ID: <?php echo htmlspecialchars($m['student_id']); ?></span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($m['department']); ?><br>
                                <span style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($m['batch']); ?></span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($m['phone']); ?><br>
                                <span style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($m['email']); ?></span>
                            </td>
                            <td>
                                <div style="max-width: 200px; max-height: 80px; overflow-y: auto; font-size: 13px; color: #444; line-height: 1.4;">
                                    <?php echo nl2br(htmlspecialchars($m['reason'])); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12px; line-height: 1.3;">
                                    <strong>Home District:</strong> <?php echo htmlspecialchars($m['district']); ?><br>
                                    <strong>School:</strong> <?php echo htmlspecialchars($m['school']); ?><br>
                                    <strong>College:</strong> <?php echo htmlspecialchars($m['college']); ?><br>
                                    <strong>Expectations:</strong> <?php echo htmlspecialchars($m['expectations']); ?><br>
                                    <strong>Memory:</strong> <?php echo htmlspecialchars($m['memory']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="members.php?action=approve&id=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm">Approve</a>
                                    <a href="members.php?action=reject&id=<?php echo $m['id']; ?>" class="btn btn-warning btn-sm">Reject</a>
                                    <a href="members.php?action=delete&id=<?php echo $m['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this applicant?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 2. APPROVED MEMBERS SECTION -->
<div class="panel-card">
    <div class="panel-card-header">
        <h2>Approved Members (<?php echo count($approved_members); ?>)</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name / Roll</th>
                    <th>Dept / Batch</th>
                    <th>Phone / Email</th>
                    <th>Current Position</th>
                    <th>Set Custom Position</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($approved_members)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #7f8c8d; padding: 20px;">No approved members found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($approved_members as $m): ?>
                        <tr>
                            <td>
                                <?php if ($m['photo']): ?>
                                    <img src="../<?php echo htmlspecialchars($m['photo']); ?>" alt="Photo" width="50" height="50">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #e2e2e2; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 10px;">No Photo</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($m['name']); ?></strong><br>
                                <span style="font-size: 12px; color: #666;">ID: <?php echo htmlspecialchars($m['student_id']); ?></span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($m['department']); ?><br>
                                <span style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($m['batch']); ?></span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($m['phone']); ?><br>
                                <span style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($m['email']); ?></span>
                            </td>
                            <td>
                                <span class="badge" style="background-color: #d1ecf1; color: #0c5460; font-weight: 800; font-size: 0.85rem; border: 1px solid #bee5eb;">
                                    <?php echo htmlspecialchars($m['position']); ?>
                                </span>
                            </td>
                            <td>
                                <form action="members.php" method="POST" style="display: flex; gap: 8px; align-items: center;">
                                    <input type="hidden" name="member_id" value="<?php echo $m['id']; ?>">
                                    <select name="position" style="padding: 6px; border: 1px solid #ccc; border-radius: 6px; font-size: 13px;" required>
                                        <option value="Member" <?php echo ($m['position'] == 'Member') ? 'selected' : ''; ?>>Member</option>
                                        <option value="President" <?php echo ($m['position'] == 'President') ? 'selected' : ''; ?>>President</option>
                                        <option value="Vice President" <?php echo ($m['position'] == 'Vice President') ? 'selected' : ''; ?>>Vice President</option>
                                        <option value="General Secretary" <?php echo ($m['position'] == 'General Secretary') ? 'selected' : ''; ?>>General Secretary</option>
                                        <option value="Assistant General Secretary" <?php echo ($m['position'] == 'Assistant General Secretary') ? 'selected' : ''; ?>>Assistant General Secretary</option>
                                        <option value="Joint Secretary" <?php echo ($m['position'] == 'Joint Secretary') ? 'selected' : ''; ?>>Joint Secretary</option>
                                        <option value="Organizing Secretary" <?php echo ($m['position'] == 'Organizing Secretary') ? 'selected' : ''; ?>>Organizing Secretary</option>
                                        <option value="Publicity Secretary" <?php echo ($m['position'] == 'Publicity Secretary') ? 'selected' : ''; ?>>Publicity Secretary</option>
                                        <option value="Cultural Secretary" <?php echo ($m['position'] == 'Cultural Secretary') ? 'selected' : ''; ?>>Cultural Secretary</option>
                                        <option value="Treasurer" <?php echo ($m['position'] == 'Treasurer') ? 'selected' : ''; ?>>Treasurer</option>
                                        <option value="Advisor" <?php echo ($m['position'] == 'Advisor') ? 'selected' : ''; ?>>Advisor</option>
                                    </select>
                                    <button type="submit" name="update_position" class="btn btn-primary btn-sm" style="padding: 6px 10px;">Update</button>
                                </form>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="members.php?action=reject&id=<?php echo $m['id']; ?>" class="btn btn-warning btn-sm" onclick="return confirm('Revoke membership and move back to rejected/pending status?')">Revoke</a>
                                    <a href="members.php?action=delete&id=<?php echo $m['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete member record entirely?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 3. REJECTED APPLICATIONS SECTION -->
<div class="panel-card">
    <div class="panel-card-header">
        <h2>Rejected Applications (<?php echo count($rejected_members); ?>)</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name / Roll</th>
                    <th>Dept / Batch</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rejected_members)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #7f8c8d; padding: 20px;">No rejected applications.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rejected_members as $m): ?>
                        <tr>
                            <td>
                                <?php if ($m['photo']): ?>
                                    <img src="../<?php echo htmlspecialchars($m['photo']); ?>" alt="Photo" width="45" height="45">
                                <?php else: ?>
                                    <div style="width: 45px; height: 45px; background: #e2e2e2; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 9px;">No Photo</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($m['name']); ?></strong><br>
                                <span style="font-size: 11px; color: #666;">ID: <?php echo htmlspecialchars($m['student_id']); ?></span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($m['department']); ?><br>
                                <span style="font-size: 11px; color: #666;"><?php echo htmlspecialchars($m['batch']); ?></span>
                            </td>
                            <td>
                                <div style="font-size: 12px; color: #666; max-width: 300px; overflow-wrap: break-word;">
                                    <?php echo htmlspecialchars($m['reason']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="members.php?action=approve&id=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm">Re-Approve</a>
                                    <a href="members.php?action=delete&id=<?php echo $m['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record completely?')">Delete Permanently</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'footer.php';
?>
