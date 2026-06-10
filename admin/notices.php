<?php
require_once 'header.php';

$message = "";
$msg_type = "success";

if (!$pdo) {
    echo "<div class='toast-msg toast-error'>Database connection failed.</div>";
    require_once 'footer.php';
    exit();
}

// Handle Add/Edit Notice Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notice'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $date = sanitize($_POST['date']);
    $notice_id = isset($_POST['notice_id']) ? (int)$_POST['notice_id'] : 0;
    $attachment_path = isset($_POST['existing_attachment']) ? $_POST['existing_attachment'] : null;

    // Handle File Attachment Upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_name = $_FILES['attachment']['name'];
        $file_size = $_FILES['attachment']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
        $max_size = 10 * 1024 * 1024; // 10MB

        if (in_array($file_ext, $allowed_exts) && $file_size <= $max_size) {
            $upload_dir = '../uploads/notices/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old attachment if updating
            if ($notice_id > 0 && $attachment_path && file_exists('../' . $attachment_path) && strpos($attachment_path, 'uploads/') === 0) {
                unlink('../' . $attachment_path);
            }

            $new_file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($file_name, PATHINFO_FILENAME)) . '.' . $file_ext;
            $dest_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $dest_path)) {
                $attachment_path = 'uploads/notices/' . $new_file_name;
            }
        }
    }

    try {
        if ($notice_id > 0) {
            // Update Existing
            $stmt = $pdo->prepare("UPDATE notices SET title = :title, description = :description, date = :date, attachment = :attachment WHERE id = :id");
            $stmt->execute([
                'title' => $title,
                'description' => $description,
                'date' => $date,
                'attachment' => $attachment_path,
                'id' => $notice_id
            ]);
            $_SESSION['msg'] = "Notice updated successfully!";
        } else {
            // Insert New
            $stmt = $pdo->prepare("INSERT INTO notices (title, description, date, attachment) VALUES (:title, :description, :date, :attachment)");
            $stmt->execute([
                'title' => $title,
                'description' => $description,
                'date' => $date,
                'attachment' => $attachment_path
            ]);
            $_SESSION['msg'] = "Notice created successfully!";
        }
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Database Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }
    
    redirect('notices.php');
}

// Handle GET Actions (Delete/Edit trigger)
$edit_notice = null;
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitize($_GET['action']);
    $id = (int)$_GET['id'];

    if ($action === 'delete') {
        try {
            // Get attachment path to delete file from disk
            $stmt_file = $pdo->prepare("SELECT attachment FROM notices WHERE id = :id");
            $stmt_file->execute(['id' => $id]);
            $attachment = $stmt_file->fetchColumn();

            if ($attachment && file_exists('../' . $attachment) && strpos($attachment, 'uploads/') === 0) {
                unlink('../' . $attachment);
            }

            $stmt = $pdo->prepare("DELETE FROM notices WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['msg'] = "Notice deleted successfully!";
            $_SESSION['msg_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['msg'] = "Error: " . $e->getMessage();
            $_SESSION['msg_type'] = "error";
        }
        redirect('notices.php');
    } elseif ($action === 'edit') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM notices WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $edit_notice = $stmt->fetch();
        } catch (PDOException $e) {
            // fail silently
        }
    }
}

// Fetch all notices
try {
    $notices = $pdo->query("SELECT * FROM notices ORDER BY date DESC, created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $notices = [];
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
    
    <!-- Add / Edit Form -->
    <div class="panel-card">
        <h2><?php echo $edit_notice ? 'Edit Notice' : 'Add New Notice'; ?></h2>
        <form action="notices.php" method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
            <?php if ($edit_notice): ?>
                <input type="hidden" name="notice_id" value="<?php echo $edit_notice['id']; ?>">
                <input type="hidden" name="existing_attachment" value="<?php echo htmlspecialchars($edit_notice['attachment']); ?>">
            <?php endif; ?>
            
            <div class="admin-form-group">
                <label for="title">Notice Title</label>
                <input type="text" id="title" name="title" value="<?php echo $edit_notice ? htmlspecialchars($edit_notice['title']) : ''; ?>" placeholder="Enter notice title" required>
            </div>
            
            <div class="admin-form-group">
                <label for="date">Notice Date</label>
                <input type="date" id="date" name="date" value="<?php echo $edit_notice ? htmlspecialchars($edit_notice['date']) : date('Y-m-d'); ?>" required>
            </div>
            
            <div class="admin-form-group">
                <label for="description">Notice Description</label>
                <textarea id="description" name="description" rows="6" placeholder="Enter detailed notice content..." required><?php echo $edit_notice ? htmlspecialchars($edit_notice['description']) : ''; ?></textarea>
            </div>
            
            <div class="admin-form-group">
                <label for="attachment">Attachment File (PDF, DOC, Images - Max 10MB)</label>
                <input type="file" id="attachment" name="attachment">
                <?php if ($edit_notice && $edit_notice['attachment']): ?>
                    <p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">
                        Current: <a href="../<?php echo htmlspecialchars($edit_notice['attachment']); ?>" target="_blank">View File</a>
                    </p>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" name="save_notice" class="btn btn-primary"><?php echo $edit_notice ? 'Update Notice' : 'Publish Notice'; ?></button>
                <?php if ($edit_notice): ?>
                    <a href="notices.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Notices List -->
    <div class="panel-card">
        <h2>Published Notices (<?php echo count($notices); ?>)</h2>
        <div class="table-responsive" style="margin-top: 15px;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title / Description</th>
                        <th>File</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notices)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #7f8c8d; padding: 20px;">No notices published yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notices as $n): ?>
                            <tr>
                                <td style="white-space: nowrap;">
                                    <strong><?php echo date('M d, Y', strtotime($n['date'])); ?></strong>
                                </td>
                                <td>
                                    <strong style="color: #0c552b; font-size: 1.05rem;"><?php echo htmlspecialchars($n['title']); ?></strong>
                                    <p style="margin: 6px 0 0 0; font-size: 13px; color: #444; line-height: 1.4; max-height: 80px; overflow-y: auto;">
                                        <?php echo nl2br(htmlspecialchars($n['description'])); ?>
                                    </p>
                                </td>
                                <td>
                                    <?php if ($n['attachment']): ?>
                                        <a href="../<?php echo htmlspecialchars($n['attachment']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">View</a>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: #aaa;">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="notices.php?action=edit&id=<?php echo $n['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="notices.php?action=delete&id=<?php echo $n['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this notice?')">Delete</a>
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
