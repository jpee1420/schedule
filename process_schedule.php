<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $subject = $conn->real_escape_string($_POST['subject']);
    $course_id = (int)$_POST['course_id'];
    $professor_id = (int)$_POST['professor_id'];
    $room_id = (int)$_POST['room_id'];
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);
    $day = $conn->real_escape_string($_POST['day']);

    // Check for duplicate schedule
    $check_sql = "SELECT COUNT(*) as count FROM schedules 
                  WHERE professor_id = $professor_id 
                  AND room_id = $room_id 
                  AND day = '$day' 
                  AND start_time = '$start_time' 
                  AND end_time = '$end_time'";
                  
    if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $check_sql .= " AND id != $id";
    }
    
    $result = $conn->query($check_sql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $_SESSION['message'] = 'This schedule already exists!';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit();
    }
    
    if (isset($_POST['id'])) {
        // Update existing schedule
        $id = (int)$_POST['id'];
        $sql = "UPDATE schedules SET 
                course_id = '$course_id',
                professor_id = $professor_id,
                room_id = $room_id,
                start_time = '$start_time',
                end_time = '$end_time',
                day = '$day'
                WHERE id = $id";
        $message = 'Schedule updated successfully!';
    } else {
        // Add new schedule
        $sql = "INSERT INTO schedules (room_id, professor_id, course_id, start_time, end_time, day)
                VALUES ('$course_id', $professor_id, $room_id, '$start_time', '$end_time', '$day')";
        $message = 'Schedule added successfully!';
    }
    
    if ($conn->query($sql)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error: ' . $conn->error;
        $_SESSION['message_type'] = 'danger';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
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

header('Location: index.php');
exit();
?>