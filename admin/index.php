<?php
require_once 'header.php';

// Fetch stats safely
$total_members = 0;
$pending_members = 0;
$notices_count = 0;
$total_raised = 0;
$active_campaigns = 0;
$pending_messages = 0;

$recent_pending_members = [];
$recent_messages = [];

if ($pdo) {
    try {
        // Stats queries
        $total_members = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'approved'")->fetchColumn();
        $pending_members = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'pending'")->fetchColumn();
        $notices_count = $pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();
        $total_raised = $pdo->query("SELECT SUM(raised_amount) FROM funding_campaigns")->fetchColumn();
        $total_raised = $total_raised ? $total_raised : 0;
        $active_campaigns = $pdo->query("SELECT COUNT(*) FROM funding_campaigns WHERE deadline >= CURDATE() OR deadline IS NULL")->fetchColumn();
        $pending_messages = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'pending'")->fetchColumn();

        // Recent listings
        $recent_pending_members = $pdo->query("SELECT * FROM members WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5")->fetchAll();
        $recent_messages = $pdo->query("SELECT * FROM contact_messages WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5")->fetchAll();
    } catch (PDOException $e) {
        // Suppress or handle
    }
}
?>

<div class="panel-card" style="margin-bottom: 25px;">
    <h2>Welcome back, <?php echo $_SESSION['admin_name']; ?>!</h2>
    <p style="margin: 0; color: #617d6e;">Here is a quick overview of what is happening with the Sylhet Association of KUET website today.</p>
</div>

<!-- Metrics Cards -->
<div class="metrics-grid">
    <div class="metric-card">
        <h3>Approved Members</h3>
        <div class="metric-val"><?php echo $total_members; ?></div>
    </div>
    
    <div class="metric-card" style="border-left: 4px solid #f1c40f;">
        <h3>Pending Applications</h3>
        <div class="metric-val" style="color: #d63031;"><?php echo $pending_members; ?></div>
    </div>
    
    <div class="metric-card">
        <h3>Total Notice Count</h3>
        <div class="metric-val"><?php echo $notices_count; ?></div>
    </div>

    <div class="metric-card">
        <h3>Campaign Funding</h3>
        <div class="metric-val">৳<?php echo number_format($total_raised); ?></div>
    </div>
    
    <div class="metric-card">
        <h3>Active Campaigns</h3>
        <div class="metric-val"><?php echo $active_campaigns; ?></div>
    </div>
    
    <div class="metric-card">
        <h3>Pending Inquiries</h3>
        <div class="metric-val" style="color: #b71c1c;"><?php echo $pending_messages; ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; align-items: flex-start; margin-top: 30px;">
    <!-- Recent Member Registrations -->
    <div class="panel-card" style="margin-bottom: 0;">
        <div class="panel-card-header">
            <h2>Recent Applications</h2>
            <a href="members.php" class="btn btn-primary btn-sm">Manage Members</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Dept</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_pending_members)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #7f8c8d;">No pending applications.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_pending_members as $m): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($m['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($m['department']) . ' (' . htmlspecialchars($m['batch']) . ')'; ?></td>
                                <td>
                                    <a href="members.php" class="btn btn-warning btn-sm">Review</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Unread Contact Messages -->
    <div class="panel-card" style="margin-bottom: 0;">
        <div class="panel-card-header">
            <h2>Recent Inquiries</h2>
            <a href="contact.php" class="btn btn-primary btn-sm">View Inquiries</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_messages)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #7f8c8d;">No unread messages.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_messages as $msg): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                                <td><span class="badge badge-pending"><?php echo htmlspecialchars($msg['message_type']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                                <td>
                                    <a href="contact.php" class="btn btn-warning btn-sm">Read</a>
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
