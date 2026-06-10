<?php
require_once 'header.php';

$message = "";
$msg_type = "success";

if (!$pdo) {
    echo "<div class='toast-msg toast-error'>Database connection failed.</div>";
    require_once 'footer.php';
    exit();
}

// Handle Add/Edit Job Offer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_job'])) {
    $title = sanitize($_POST['title']);
    $company = sanitize($_POST['company']);
    $description = sanitize($_POST['description']);
    $requirements = sanitize($_POST['requirements']);
    $deadline = !empty($_POST['deadline']) ? sanitize($_POST['deadline']) : null;
    $contact = sanitize($_POST['contact']);
    $status = sanitize($_POST['status']);
    $job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;

    if (!in_array($status, ['active', 'inactive'])) {
        $status = 'active';
    }

    try {
        if ($job_id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE job_offers SET title = :title, company = :company, description = :description, requirements = :requirements, deadline = :deadline, contact = :contact, status = :status WHERE id = :id");
            $stmt->execute([
                'title' => $title,
                'company' => $company,
                'description' => $description,
                'requirements' => $requirements,
                'deadline' => $deadline,
                'contact' => $contact,
                'status' => $status,
                'id' => $job_id
            ]);
            $_SESSION['msg'] = "Job offer updated successfully!";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO job_offers (title, company, description, requirements, deadline, contact, status) VALUES (:title, :company, :description, :requirements, :deadline, :contact, :status)");
            $stmt->execute([
                'title' => $title,
                'company' => $company,
                'description' => $description,
                'requirements' => $requirements,
                'deadline' => $deadline,
                'contact' => $contact,
                'status' => $status
            ]);
            $_SESSION['msg'] = "Job offer posted successfully!";
        }
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Database Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    redirect('jobs.php');
}

// Handle GET Actions (Delete, Toggle Status, Edit trigger)
$edit_job = null;
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitize($_GET['action']);
    $id = (int)$_GET['id'];

    if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM job_offers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['msg'] = "Job offer deleted successfully!";
            $_SESSION['msg_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['msg'] = "Error: " . $e->getMessage();
            $_SESSION['msg_type'] = "error";
        }
        redirect('jobs.php');
    } elseif ($action === 'toggle') {
        try {
            $stmt = $pdo->prepare("UPDATE job_offers SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['msg'] = "Job status toggled successfully!";
            $_SESSION['msg_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['msg'] = "Error: " . $e->getMessage();
            $_SESSION['msg_type'] = "error";
        }
        redirect('jobs.php');
    } elseif ($action === 'edit') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM job_offers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $edit_job = $stmt->fetch();
        } catch (PDOException $e) {
            // silently fail
        }
    }
}

// Fetch all jobs
try {
    $jobs = $pdo->query("SELECT * FROM job_offers ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $jobs = [];
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
    
    <!-- Job Offer Add/Edit Form -->
    <div class="panel-card">
        <h2><?php echo $edit_job ? 'Edit Job Posting' : 'Post New Job'; ?></h2>
        <form action="jobs.php" method="POST" style="margin-top: 15px;">
            <?php if ($edit_job): ?>
                <input type="hidden" name="job_id" value="<?php echo $edit_job['id']; ?>">
            <?php endif; ?>
            
            <div class="admin-form-group">
                <label for="title">Job Title</label>
                <input type="text" id="title" name="title" value="<?php echo $edit_job ? htmlspecialchars($edit_job['title']) : ''; ?>" placeholder="e.g., Web Developer Intern" required>
            </div>
            
            <div class="admin-form-group">
                <label for="company">Company / Client</label>
                <input type="text" id="company" name="company" value="<?php echo $edit_job ? htmlspecialchars($edit_job['company']) : ''; ?>" placeholder="e.g., Tech Company or Tuition Parent" required>
            </div>
            
            <div class="admin-form-group">
                <label for="contact">Contact Details</label>
                <input type="text" id="contact" name="contact" value="<?php echo $edit_job ? htmlspecialchars($edit_job['contact']) : ''; ?>" placeholder="e.g., Email, phone, or application link" required>
            </div>
            
            <div class="admin-form-group">
                <label for="deadline">Application Deadline</label>
                <input type="date" id="deadline" name="deadline" value="<?php echo ($edit_job && $edit_job['deadline']) ? htmlspecialchars($edit_job['deadline']) : ''; ?>">
            </div>
            
            <div class="admin-form-group">
                <label for="status">Posting Status</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo ($edit_job && $edit_job['status'] == 'active') ? 'selected' : ''; ?>>Active (Display on website)</option>
                    <option value="inactive" <?php echo ($edit_job && $edit_job['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive (Hidden)</option>
                </select>
            </div>
            
            <div class="admin-form-group">
                <label for="requirements">Requirements Summary</label>
                <textarea id="requirements" name="requirements" rows="3" placeholder="HTML, CSS, JavaScript, etc..." required><?php echo $edit_job ? htmlspecialchars($edit_job['requirements']) : ''; ?></textarea>
            </div>
            
            <div class="admin-form-group">
                <label for="description">Job Description</label>
                <textarea id="description" name="description" rows="5" placeholder="Enter job responsibilities, schedule, stipend details..." required><?php echo $edit_job ? htmlspecialchars($edit_job['description']) : ''; ?></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" name="save_job" class="btn btn-primary"><?php echo $edit_job ? 'Update Post' : 'Submit Post'; ?></button>
                <?php if ($edit_job): ?>
                    <a href="jobs.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Jobs List -->
    <div class="panel-card">
        <h2>Job Postings & Opportunities (<?php echo count($jobs); ?>)</h2>
        <div class="table-responsive" style="margin-top: 15px;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Job Title / Source</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jobs)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #7f8c8d; padding: 20px;">No postings found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $j): ?>
                            <tr>
                                <td>
                                    <strong style="color: #0c552b; font-size: 1.05rem;"><?php echo htmlspecialchars($j['title']); ?></strong><br>
                                    <span style="font-size: 12px; color: #666;">Company: <strong><?php echo htmlspecialchars($j['company']); ?></strong></span>
                                </td>
                                <td>
                                    <div style="font-size: 13px; line-height: 1.4; max-height: 120px; overflow-y: auto;">
                                        <strong>Description:</strong> <?php echo htmlspecialchars($j['description']); ?><br>
                                        <strong>Reqs:</strong> <span style="font-style: italic;"><?php echo htmlspecialchars($j['requirements']); ?></span><br>
                                        <strong>Contact:</strong> <code><?php echo htmlspecialchars($j['contact']); ?></code><br>
                                        <strong>Deadline:</strong> <?php echo $j['deadline'] ? date('M d, Y', strtotime($j['deadline'])) : 'Open'; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="jobs.php?action=toggle&id=<?php echo $j['id']; ?>" class="badge badge-<?php echo $j['status']; ?>" style="border: none; cursor: pointer; text-decoration: none;">
                                        <?php echo htmlspecialchars($j['status']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="jobs.php?action=edit&id=<?php echo $j['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="jobs.php?action=delete&id=<?php echo $j['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this posting?')">Delete</a>
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
