<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    if (!isset($_POST['action'])) {
        echo json_encode(['success' => false, 'message' => 'No action specified']);
        exit;
    }

    // Validation action
    if ($_POST['action'] === 'validate') {
        if (!isset($_POST['room_id']) || !isset($_POST['professor_id']) || 
            !isset($_POST['day']) || !isset($_POST['start_time']) || 
            !isset($_POST['end_time'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $roomId = $_POST['room_id'];
        $professorId = $_POST['professor_id'];
        $day = $_POST['day'];
        $startTime = $_POST['start_time'];
        $endTime = $_POST['end_time'];
        $excludeId = isset($_POST['id']) ? $_POST['id'] : null;

        // Check for time validity
        if (strtotime($startTime) >= strtotime($endTime)) {
            echo json_encode(['success' => false, 'message' => 'End time must be after start time']);
            exit;
        }

        // Check for conflicts
        $conflict = checkScheduleConflict($conn, $roomId, $professorId, $day, 
                                        $startTime, $endTime, $excludeId);
        
        if ($conflict['hasConflict']) {
            echo json_encode(['success' => false, 'message' => $conflict['message']]);
            exit;
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // Add or update schedule
    $subject = $conn->real_escape_string($_POST['subject']);
    $professor_id = (int)$_POST['professor_id'];
    $room_id = (int)$_POST['room_id'];
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);
    $day = $conn->real_escape_string($_POST['day']);
    $notes = $conn->real_escape_string($_POST['notes']);

    if (isset($_POST['id'])) {
        // Update existing schedule
        $id = (int)$_POST['id'];
        $sql = "UPDATE schedules SET 
                subject = '$subject',
                professor_id = $professor_id,
                room_id = $room_id,
                start_time = '$start_time',
                end_time = '$end_time',
                day = '$day',
                notes = '$notes'
                WHERE id = $id";
    } else {
        // Add new schedule
        $sql = "INSERT INTO schedules (subject, professor_id, room_id, start_time, end_time, day, notes)
                VALUES ('$subject', $professor_id, $room_id, '$start_time', '$end_time', '$day', '$notes')";

        if ($conn->query($sql)) {
            $response['success'] = true;
            $response['message'] = 'Schedule added successfully!';
        } else {
            $response['message'] = 'Error saving schedule: ' . $conn->error;
        }

        echo json_encode($response);
        exit;
    }

    if ($conn->query($sql)) {
        $_SESSION['message'] = 'Schedule saved successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error saving schedule: ' . $conn->error;
        $_SESSION['message_type'] = 'danger';
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    // Delete schedule
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM schedules WHERE id = $id";

    if ($conn->query($sql)) {
        $_SESSION['message'] = 'Schedule deleted successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error deleting schedule: ' . $conn->error;
        $_SESSION['message_type'] = 'danger';
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit();
?>