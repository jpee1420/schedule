<?php
// Get all rooms from database
function getAllRooms($conn) {
    $sql = "SELECT * FROM rooms ORDER BY id ASC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all professors from database
function getAllProfessors($conn) {
    $sql = "SELECT * FROM professors ORDER BY id ASC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all courses from database
function getAllCourses($conn) {
    $sql = "SELECT * FROM courses ORDER BY id ASC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all schedules with related information
function getSchedules($conn) {
    $sql = "SELECT s.*, 
            p.name AS professor_name, 
            p.profile_image,
            r.name AS room_name
            FROM schedules s
            LEFT JOIN professors p ON s.professor_id = p.id
            LEFT JOIN rooms r ON s.room_id = r.id";
            
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
    
    $schedules = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $schedules[] = $row;
    }
    
    return $schedules;
}

function getProfessorStatus($conn, $professorId) {
    $sql = "SELECT professor_status FROM schedules 
            WHERE professor_id = ? 
            ORDER BY id DESC LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $professorId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['professor_status'];
    }
    
    return 'Present'; // Default status
}

function updateProfessorStatus($conn, $professorId, $status) {
    $sql = "UPDATE schedules SET professor_status = ? 
            WHERE professor_id = ? AND DATE(created_at) = CURDATE()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $professorId);
    return $stmt->execute();
}

function checkScheduleConflict($conn, $roomId, $professorId, $day, $startTime, $endTime, $excludeId = null) {
    // Validate inputs
    if (!$roomId || !$professorId || !$day || !$startTime || !$endTime) {
        return [
            'hasConflict' => true,
            'message' => 'All fields are required'
        ];
    }

    // Check for overlapping schedules
    $sql = "SELECT s.*, r.name as room_name, p.name as professor_name 
            FROM schedules s
            JOIN rooms r ON s.room_id = r.id
            JOIN professors p ON s.professor_id = p.id
            WHERE (s.room_id = ? OR s.professor_id = ?)
            AND s.day = ?
            AND (
                (s.start_time <= ? AND s.end_time > ?) OR
                (s.start_time < ? AND s.end_time >= ?) OR
                (s.start_time >= ? AND s.end_time <= ?)
            )";

    // Add exclusion for edit mode
    if ($excludeId) {
        $sql .= " AND s.id != ?";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            'hasConflict' => true,
            'message' => 'Database error: ' . $conn->error
        ];
    }

    // Bind parameters
    if ($excludeId) {
        $stmt->bind_param("iissssssi", 
            $roomId, $professorId, $day, 
            $endTime, $startTime, 
            $startTime, $endTime,
            $startTime, $endTime,
            $excludeId
        );
    } else {
        $stmt->bind_param("iissssss", 
            $roomId, $professorId, $day, 
            $endTime, $startTime, 
            $startTime, $endTime,
            $startTime, $endTime
        );
    }

    // Execute query
    if (!$stmt->execute()) {
        return [
            'hasConflict' => true,
            'message' => 'Error checking conflicts: ' . $stmt->error
        ];
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $conflict = $result->fetch_assoc();
        return [
            'hasConflict' => true,
            'message' => sprintf(
                "Schedule conflicts with %s in %s on %s from %s to %s",
                $conflict['professor_name'],
                $conflict['room_name'],
                $conflict['day'],
                date('h:i A', strtotime($conflict['start_time'])),
                date('h:i A', strtotime($conflict['end_time']))
            )
        ];
    }

    return ['hasConflict' => false];
}
?>