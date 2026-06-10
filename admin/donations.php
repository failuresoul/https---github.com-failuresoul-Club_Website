<?php
require_once 'header.php';

$message = "";
$msg_type = "success";

if (!$pdo) {
    echo "<div class='toast-msg toast-error'>Database connection failed.</div>";
    require_once 'footer.php';
    exit();
}

// Handle GET Actions (Approve, Reject, Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitize($_GET['action']);
    $donation_id = (int)$_GET['id'];

    try {
        // Fetch donation detail first
        $stmt_don = $pdo->prepare("SELECT * FROM donations WHERE id = :id");
        $stmt_don->execute(['id' => $donation_id]);
        $donation = $stmt_don->fetch();

        if ($donation) {
            $amount = (float)$donation['amount'];
            $campaign_id = $donation['campaign_id'];
            $current_status = $donation['status'];

            if ($action === 'approve' && $current_status !== 'approved') {
                $pdo->beginTransaction();
                // 1. Update donation status
                $stmt = $pdo->prepare("UPDATE donations SET status = 'approved' WHERE id = :id");
                $stmt->execute(['id' => $donation_id]);

                // 2. Add to campaign raised_amount
                if ($campaign_id) {
                    $stmt_camp = $pdo->prepare("UPDATE funding_campaigns SET raised_amount = raised_amount + :amount WHERE id = :id");
                    $stmt_camp->execute(['amount' => $amount, 'id' => $campaign_id]);
                }
                $pdo->commit();
                $_SESSION['msg'] = "Donation verified and approved successfully!";
                $_SESSION['msg_type'] = "success";

            } elseif ($action === 'reject' && $current_status !== 'rejected') {
                $pdo->beginTransaction();
                // 1. Update status
                $stmt = $pdo->prepare("UPDATE donations SET status = 'rejected' WHERE id = :id");
                $stmt->execute(['id' => $donation_id]);

                // 2. Subtract from campaign raised_amount if it was previously approved
                if ($current_status === 'approved' && $campaign_id) {
                    $stmt_camp = $pdo->prepare("UPDATE funding_campaigns SET raised_amount = raised_amount - :amount WHERE id = :id");
                    $stmt_camp->execute(['amount' => $amount, 'id' => $campaign_id]);
                }
                $pdo->commit();
                $_SESSION['msg'] = "Donation application marked as rejected.";
                $_SESSION['msg_type'] = "warning";

            } elseif ($action === 'revoke' && $current_status === 'approved') {
                $pdo->beginTransaction();
                // 1. Revert to pending
                $stmt = $pdo->prepare("UPDATE donations SET status = 'pending' WHERE id = :id");
                $stmt->execute(['id' => $donation_id]);

                // 2. Subtract from campaign
                if ($campaign_id) {
                    $stmt_camp = $pdo->prepare("UPDATE funding_campaigns SET raised_amount = raised_amount - :amount WHERE id = :id");
                    $stmt_camp->execute(['amount' => $amount, 'id' => $campaign_id]);
                }
                $pdo->commit();
                $_SESSION['msg'] = "Donation verification revoked to pending status.";
                $_SESSION['msg_type'] = "warning";

            } elseif ($action === 'delete') {
                $pdo->beginTransaction();
                // If it was approved, subtract from campaign first
                if ($current_status === 'approved' && $campaign_id) {
                    $stmt_camp = $pdo->prepare("UPDATE funding_campaigns SET raised_amount = raised_amount - :amount WHERE id = :id");
                    $stmt_camp->execute(['amount' => $amount, 'id' => $campaign_id]);
                }
                // Delete record
                $stmt = $pdo->prepare("DELETE FROM donations WHERE id = :id");
                $stmt->execute(['id' => $donation_id]);
                $pdo->commit();
                $_SESSION['msg'] = "Donation record deleted completely.";
                $_SESSION['msg_type'] = "success";
            }
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['msg'] = "Database Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    redirect('donations.php');
}

// Fetch all donations with their campaign title
try {
    $donations = $pdo->query("SELECT d.*, c.title as campaign_title 
                             FROM donations d 
                             LEFT JOIN funding_campaigns c ON d.campaign_id = c.id 
                             ORDER BY d.created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $donations = [];
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
    <div class="toast-msg toast-<?php echo $msg_type === 'warning' ? 'error' : $msg_type; ?>">
        <span><?php echo $message; ?></span>
    </div>
<?php endif; ?>

<div class="panel-card">
    <div class="panel-card-header">
        <h2>Donations Log (<?php echo count($donations); ?>)</h2>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Donor Details</th>
                    <th>Campaign Targeted</th>
                    <th>Amount (৳)</th>
                    <th>Transaction ID</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($donations)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #7f8c8d; padding: 20px;">No donations recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($donations as $d): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($d['date'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($d['donor_name']); ?></strong><br>
                                <span style="font-size: 12px; color: #666;">Phone: <?php echo htmlspecialchars($d['phone']); ?></span>
                            </td>
                            <td>
                                <?php if ($d['campaign_title']): ?>
                                    <span style="color: #0c552b; font-weight: bold;"><?php echo htmlspecialchars($d['campaign_title']); ?></span>
                                <?php else: ?>
                                    <span style="color: #aaa; font-style: italic;">Unlinked / Deleted Campaign</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="font-size: 1.1rem; color: #0c552b;">৳<?php echo number_format($d['amount'], 2); ?></strong>
                            </td>
                            <td>
                                <code style="background: #eef5ec; padding: 4px 8px; border-radius: 4px; font-weight: bold; color: #2e7d32;"><?php echo htmlspecialchars($d['transaction_id']); ?></code>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $d['status']; ?>">
                                    <?php echo htmlspecialchars($d['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <?php if ($d['status'] === 'pending'): ?>
                                        <a href="donations.php?action=approve&id=<?php echo $d['id']; ?>" class="btn btn-primary btn-sm">Approve</a>
                                        <a href="donations.php?action=reject&id=<?php echo $d['id']; ?>" class="btn btn-warning btn-sm">Reject</a>
                                    <?php elseif ($d['status'] === 'approved'): ?>
                                        <a href="donations.php?action=revoke&id=<?php echo $d['id']; ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Revoke verification status of this donation?')">Revoke</a>
                                    <?php elseif ($d['status'] === 'rejected'): ?>
                                        <a href="donations.php?action=approve&id=<?php echo $d['id']; ?>" class="btn btn-primary btn-sm">Approve</a>
                                    <?php endif; ?>
                                    <a href="donations.php?action=delete&id=<?php echo $d['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this transaction log permanently?')">Delete</a>
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
