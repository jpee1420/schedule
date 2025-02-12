<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/uploads';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'updateStatus') {
        $professorId = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        
        // Validate status
        $validStatuses = ['Present', 'Absent', 'On Leave'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }

        // Update all schedules for this professor
        $sql = "UPDATE schedules SET professor_status = ? WHERE professor_id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("si", $status, $professorId);
            $success = $stmt->execute();
            
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }
        exit;
    }

    $response = ['success' => false, 'message' => ''];
    
    // Handle professor data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    if (empty($name)) {
        $_SESSION['message'] = 'Professor name is required';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit();
    }

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $tmpName = $_FILES['profile_image']['tmp_name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $_SESSION['message'] = 'Invalid file type. Allowed: jpg, jpeg, png, gif';
            $_SESSION['message_type'] = 'danger';
            header('Location: index.php');
            exit();
        }

        // Generate unique filename
        $new_filename = uniqid('prof_', true) . '.' . $ext;
        $upload_path = $uploadDir . '/' . $new_filename;
        
        // Upload new image
        if (!move_uploaded_file($tmpName, $upload_path)) {
            $_SESSION['message'] = 'Failed to upload image';
            $_SESSION['message_type'] = 'danger';
            header('Location: index.php');
            exit();
        }

        $profile_image = $new_filename;
    } else {
        $profile_image = 'placeholder.png'; // Default image for new professors
    }

    // Database operations
    if (isset($_POST['id'])) {
        // Update existing professor
        $id = (int)$_POST['id'];
        
        // Get current profile image
        $stmt = $conn->prepare("SELECT profile_image FROM professors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_prof = $result->fetch_assoc();
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            // Delete old image if it exists and is not the default
            if ($current_prof && $current_prof['profile_image'] !== 'placeholder.png') {
                $old_image_path = $uploadDir . '/' . $current_prof['profile_image'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            
            $sql = "UPDATE professors SET name = ?, profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $profile_image, $id);
        } else {
            $sql = "UPDATE professors SET name = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $name, $id);
        }
    } else {
        // Add new professor
        $sql = "INSERT INTO professors (name, profile_image) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $profile_image);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = isset($_POST['id']) ? 'Professor updated successfully!' : 'Professor added successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error: ' . $stmt->error;
        $_SESSION['message_type'] = 'danger';
    }

    header('Location: index.php');
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    
    // Check if professor is being used in schedules
    $check_sql = "SELECT COUNT(*) as count FROM schedules WHERE professor_id = $id";
    $result = $conn->query($check_sql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $_SESSION['message'] = 'Cannot delete professor: They have assigned schedules';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Get professor image
        $img_sql = "SELECT profile_image FROM professors WHERE id = $id";
        $img_result = $conn->query($img_sql);
        $professor = $img_result->fetch_assoc();
        
        $sql = "DELETE FROM professors WHERE id = $id";
        if ($conn->query($sql)) {
            // Delete profile image if not default
            if ($professor['profile_image'] !== 'placeholder.png') {
                @unlink('uploads/' . $professor['profile_image']);
            }
            $_SESSION['message'] = 'Professor deleted successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error deleting professor: ' . $conn->error;
            $_SESSION['message_type'] = 'danger';
        }
    }
}

header('Location: index.php');
exit();

// $conn->close();
?>