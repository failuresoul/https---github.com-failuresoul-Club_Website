<?php
require_once 'header.php';

$message = "";
$msg_type = "success";

if (!$pdo) {
    echo "<div class='toast-msg toast-error'>Database connection failed.</div>";
    require_once 'footer.php';
    exit();
}

// Handle Image Upload Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $title = sanitize($_POST['title']);
    $caption = sanitize($_POST['caption']);
    $category = sanitize($_POST['category']); // maps to standard, featured, wide, tall

    if (!in_array($category, ['standard', 'featured', 'wide', 'tall'])) {
        $category = 'standard';
    }

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_name = $_FILES['photo']['name'];
        $file_size = $_FILES['photo']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 8 * 1024 * 1024; // 8MB

        if (!in_array($file_ext, $allowed_exts)) {
            $_SESSION['msg'] = "Only JPG, JPEG, PNG, GIF, and WEBP image formats are allowed.";
            $_SESSION['msg_type'] = "error";
            redirect('gallery.php');
        }

        if ($file_size > $max_size) {
            $_SESSION['msg'] = "Image size must not exceed 8MB.";
            $_SESSION['msg_type'] = "error";
            redirect('gallery.php');
        }

        $upload_dir = '../uploads/gallery/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $new_file_name = 'gallery_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $dest_path)) {
            $photo_path = 'uploads/gallery/' . $new_file_name;

            try {
                $stmt = $pdo->prepare("INSERT INTO gallery (title, caption, category, filename) VALUES (:title, :caption, :category, :filename)");
                $stmt->execute([
                    'title' => $title,
                    'caption' => $caption,
                    'category' => $category,
                    'filename' => $photo_path
                ]);
                $_SESSION['msg'] = "Photo uploaded and added to gallery successfully!";
                $_SESSION['msg_type'] = "success";
            } catch (PDOException $e) {
                $_SESSION['msg'] = "Database Error: " . $e->getMessage();
                $_SESSION['msg_type'] = "error";
            }
        } else {
            $_SESSION['msg'] = "Failed to write image file to disk.";
            $_SESSION['msg_type'] = "error";
        }
    } else {
        $_SESSION['msg'] = "An image file is required!";
        $_SESSION['msg_type'] = "error";
    }

    redirect('gallery.php');
}

// Handle GET Actions (Delete image)
if (isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];

    try {
        // Fetch filename to delete image from disk
        $stmt_file = $pdo->prepare("SELECT filename FROM gallery WHERE id = :id");
        $stmt_file->execute(['id' => $id]);
        $filename = $stmt_file->fetchColumn();

        if ($filename && file_exists('../' . $filename) && strpos($filename, 'uploads/') === 0) {
            unlink('../' . $filename);
        }

        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $_SESSION['msg'] = "Photo deleted from gallery successfully!";
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    redirect('gallery.php');
}

// Fetch all gallery items
try {
    $photos = $pdo->query("SELECT * FROM gallery ORDER BY upload_date DESC")->fetchAll();
} catch (PDOException $e) {
    $photos = [];
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

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: flex-start;">
    
    <!-- Upload Form -->
    <div class="panel-card">
        <h2>Upload New Photo</h2>
        <form action="gallery.php" method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
            <div class="admin-form-group">
                <label for="photo">Select Image (Max 8MB)</label>
                <input type="file" id="photo" name="photo" accept="image/*" required>
            </div>
            
            <div class="admin-form-group">
                <label for="category">Grid Layout Style</label>
                <select id="category" name="category" required>
                    <option value="standard">Standard (Square/Aspect-ratio block)</option>
                    <option value="featured">Featured (Spans 2 columns, 2 rows)</option>
                    <option value="wide">Wide (Spans 2 columns)</option>
                    <option value="tall">Tall (Spans 2 rows)</option>
                </select>
                <p style="margin: 5px 0 0 0; font-size: 11px; color: #666; line-height: 1.3;">
                    Determines how the photo displays in the masonry layout.
                </p>
            </div>
            
            <div class="admin-form-group">
                <label for="title">Title (Optional)</label>
                <input type="text" id="title" name="title" placeholder="e.g., BBQ Night 2026">
            </div>
            
            <div class="admin-form-group">
                <label for="caption">Caption (Optional)</label>
                <textarea id="caption" name="caption" rows="3" placeholder="Write a short description..."></textarea>
            </div>
            
            <button type="submit" name="upload_image" class="btn btn-primary" style="margin-top: 10px; width: 100%;">Upload Photo</button>
        </form>
    </div>

    <!-- Gallery List -->
    <div class="panel-card">
        <h2>Gallery Photos (<?php echo count($photos); ?>)</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; margin-top: 20px;">
            <?php if (empty($photos)): ?>
                <p style="grid-column: 1/-1; text-align: center; color: #7f8c8d;">No photos in the gallery.</p>
            <?php else: ?>
                <?php foreach ($photos as $p): ?>
                    <div style="background: rgba(12, 85, 43, 0.03); border: 1px solid rgba(12, 85, 43, 0.08); border-radius: 12px; padding: 10px; display: flex; flex-direction: column; justify-content: space-between; align-items: stretch; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
                        <div style="position: relative;">
                            <img src="../<?php echo htmlspecialchars($p['filename']); ?>" alt="Thumbnail" style="width: 100%; height: 110px; object-fit: cover; border-radius: 8px; margin-bottom: 8px;">
                            <span class="badge" style="position: absolute; top: 6px; right: 6px; font-size: 9px; padding: 3px 6px; font-weight: bold; background: #0c552b; color: white;">
                                <?php echo htmlspecialchars($p['category']); ?>
                            </span>
                        </div>
                        <div>
                            <strong style="font-size: 13px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo !empty($p['title']) ? htmlspecialchars($p['title']) : 'Untitled'; ?></strong>
                            <span style="font-size: 10px; color: #888;"><?php echo date('M d, Y', strtotime($p['upload_date'])); ?></span>
                        </div>
                        <div style="margin-top: 10px;">
                            <a href="gallery.php?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" style="width: 100%;" onclick="return confirm('Delete this image from gallery?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
require_once 'footer.php';
?>
