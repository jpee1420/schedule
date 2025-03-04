<?php
session_start();
require_once 'config.php';

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle status update separately
    if (isset($_POST['action']) && $_POST['action'] === 'updateStatus') {
        header('Content-Type: application/json');
        $professorId = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        
        $validStatuses = ['Present', 'Absent', 'On Leave'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }

        $sql = "UPDATE schedules SET professor_status = ? WHERE professor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $professorId);
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }

    // Handle professor data
    $name = trim($_POST['name']);
    if (empty($name)) {
        $_SESSION['message'] = 'Professor name is required';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit();
    }

    $profile_image = 'placeholder.png'; // Default image
    
    // Handle image upload if provided
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $_SESSION['message'] = 'Invalid file type. Allowed: jpg, jpeg, png, gif';
            $_SESSION['message_type'] = 'danger';
            header('Location: index.php');
            exit();
        }

        $new_filename = uniqid('prof_', true) . '.' . $ext;
        $upload_path = $uploadDir . '/' . $new_filename;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            $profile_image = $new_filename;
        }
    }

    if (isset($_POST['id'])) {
        // Update existing professor
        $id = (int)$_POST['id'];
        
        // Get current profile image
        $stmt = $conn->prepare("SELECT profile_image FROM professors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_prof = $result->fetch_assoc();
        
        // Only update image if new one was uploaded
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            // Delete old image if it exists and is not the default
            if ($current_prof && $current_prof['profile_image'] !== 'placeholder.png') {
                @unlink($uploadDir . '/' . $current_prof['profile_image']);
            }
            $sql = "UPDATE professors SET name = ?, profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $profile_image, $id);
        } else {
            $sql = "UPDATE professors SET name = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $name, $id);
        }
        $message = 'Professor updated successfully!';
    } else {
        // Add new professor
        $sql = "INSERT INTO professors (name, profile_image) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $profile_image);
        $message = 'Professor added successfully!';
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error: ' . $stmt->error;
        $_SESSION['message_type'] = 'danger';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    
    // Check if professor is being used in schedules
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM schedules WHERE professor_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $_SESSION['message'] = 'Cannot delete professor: They have assigned schedules';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Get professor image before deleting
        $stmt = $conn->prepare("SELECT profile_image FROM professors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $professor = $result->fetch_assoc();
        
        $stmt = $conn->prepare("DELETE FROM professors WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete profile image if not default
            if ($professor && $professor['profile_image'] !== 'placeholder.png') {
                @unlink($uploadDir . '/' . $professor['profile_image']);
            }
            $_SESSION['message'] = 'Professor deleted successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error deleting professor: ' . $stmt->error;
            $_SESSION['message_type'] = 'danger';
        }
    }
}

header('Location: index.php');
exit();