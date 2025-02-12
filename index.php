<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="d-flex flex-column">
                    <div class="p-3 text-white">
                        <h4>Admin Panel</h4>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="#schedules" data-bs-toggle="tab">
                            <i class="fas fa-calendar-alt me-2"></i> Schedules
                        </a>
                        <a class="nav-link" href="#rooms" data-bs-toggle="tab">
                            <i class="fas fa-door-open me-2"></i> Rooms
                        </a>
                        <a class="nav-link" href="#professors" data-bs-toggle="tab">
                            <i class="fas fa-chalkboard-teacher me-2"></i> Professors
                        </a>
                        <a class="nav-link" href="#courses" data-bs-toggle="tab">
                            <i class="fas fa-book me-2"></i> Courses
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-8 p-4">
                <!-- Messages -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="tab-content">
                    <!-- Schedules Tab -->
                    <div class="tab-pane fade show active" id="schedules">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Class Schedules</h2>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                                <i class="fas fa-plus"></i> Add Schedule
                            </button>
                        </div>
                        <div class="row">
                            <?php
                            $schedules = getSchedules($conn);
                            foreach ($schedules as $schedule):
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="card schedule-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <img src="<?php 
                                                echo !empty($schedule['profile_image']) 
                                                    ? 'uploads/' . htmlspecialchars($schedule['profile_image']) 
                                                    : 'uploads/placeholder.png'; 
                                            ?>" class="professor-image me-3" alt="Professor">
                                            <div>
                                                <h5 class="card-title mb-0"><?php echo $schedule['subject']; ?></h5>
                                                <small class="text-muted">Prof. <?php echo $schedule['professor_name']; ?></small>
                                            </div>
                                        </div>
                                        <p class="card-text">
                                            <i class="fas fa-clock me-2"></i>
                                            <?php echo date('h:i A', strtotime($schedule['start_time'])); ?> - 
                                            <?php echo date('h:i A', strtotime($schedule['end_time'])); ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-calendar me-2"></i>
                                            <?php echo $schedule['day']; ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-door-open me-2"></i>
                                            Room <?php echo $schedule['room_name']; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <?php
                                            $badgeClass = 'success';
                                            if ($schedule['professor_status'] === 'Absent') {
                                                $badgeClass = 'danger';
                                            } else if ($schedule['professor_status'] === 'On Leave') {
                                                $badgeClass = 'warning';
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $badgeClass; ?>" 
                                                  data-professor-id="<?php echo $schedule['professor_id']; ?>">
                                                <?php echo $schedule['professor_status']; ?>
                                            </span>
                                            <div>
                                                <button class="btn btn-sm btn-primary me-2" onclick="editSchedule(
                                                    <?php echo $schedule['id']; ?>,
                                                    <?php echo $schedule['room_id']; ?>,
                                                    '<?php echo $schedule['subject']; ?>',
                                                    <?php echo $schedule['professor_id']; ?>,
                                                    '<?php echo $schedule['day']; ?>',
                                                    '<?php echo $schedule['start_time']; ?>',
                                                    '<?php echo $schedule['end_time']; ?>',
                                                    '<?php echo addslashes($schedule['notes']); ?>'
                                                )">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteSchedule(<?php echo $schedule['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Rooms Tab -->
                    <div class="tab-pane fade" id="rooms">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Rooms Management</h2>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                                <i class="fas fa-plus"></i> Add Room
                            </button>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Room Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $rooms = getAllRooms($conn);
                                            foreach ($rooms as $room):
                                            ?>
                                            <tr>
                                                <td><?php echo $room['id']; ?></td>
                                                <td><?php echo $room['name']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" onclick="editRoom(<?php echo $room['id']; ?>, '<?php echo $room['name']; ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteRoom(<?php echo $room['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professors Tab -->
                    <div class="tab-pane fade" id="professors">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Professors Management</h2>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProfessorModal">
                                <i class="fas fa-plus"></i> Add Professor
                            </button>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $professors = getAllProfessors($conn);
                                            foreach ($professors as $professor):
                                            ?>
                                            <tr>
                                                <td><?php echo $professor['id']; ?></td>
                                                <td>
                                                    <img src="<?php 
                                                        echo file_exists('uploads/' . $professor['profile_image']) 
                                                            ? 'uploads/' . htmlspecialchars($professor['profile_image']) 
                                                            : 'uploads/placeholder.png'; 
                                                    ?>" class="professor-profile-image" alt="<?php echo htmlspecialchars($professor['name']); ?>">
                                                </td>
                                                <td><?php echo $professor['name']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary me-2" onclick="editProfessor(<?php echo $professor['id']; ?>, '<?php echo $professor['name']; ?>', '<?php echo $professor['profile_image']; ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteProfessor(<?php echo $professor['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Tab -->
                    <div class="tab-pane fade" id="courses">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Courses Management</h2>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                                <i class="fas fa-plus"></i> Add Course
                            </button>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Course Code</th>
                                                <th>Course Name</th>
                                                <th>Type</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $courses = getAllCourses($conn);
                                            foreach ($courses as $course):
                                            ?>
                                            <tr>
                                                <td><?php echo $course['id']; ?></td>
                                                <td><?php echo $course['course_code']; ?></td>
                                                <td><?php echo $course['course_name']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $course['lab'] ? 'info' : 'primary'; ?>">
                                                        <?php echo $course['lab'] ? 'Laboratory' : 'Lecture'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary me-2" onclick="editCourse(<?php echo $course['id']; ?>, '<?php echo $course['course_code']; ?>', '<?php echo $course['course_name']; ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-md-2 sidebar-right p-0">
                <div class="d-flex flex-column">
                    <div class="p-3 text-white">
                        <h4>Professor Status</h4>
                    </div>
                    <div class="p-3">
                        <?php
                        $professors = getAllProfessors($conn);
                        foreach ($professors as $professor):
                        ?>
                        <div class="professor-status-card mb-3">
                            <div class="d-flex align-items-center">
                                <img src="<?php 
                                    echo file_exists('uploads/' . $professor['profile_image']) 
                                        ? 'uploads/' . htmlspecialchars($professor['profile_image']) 
                                        : 'uploads/placeholder.png'; 
                                ?>" alt="<?php echo htmlspecialchars($professor['name']); ?>" class="professor-image-small me-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo $professor['name']; ?></h6>
                                    <select class="form-select form-select-sm status-select" 
                                            onchange="updateProfessorStatus(<?php echo $professor['id']; ?>, this.value)">
                                        <option value="Present" <?php echo getProfessorStatus($conn, $professor['id']) == 'Present' ? 'selected' : ''; ?>>Present</option>
                                        <option value="Absent" <?php echo getProfessorStatus($conn, $professor['id']) == 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                        <option value="On Leave" <?php echo getProfessorStatus($conn, $professor['id']) == 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modals -->

        <?php include 'modals/add_schedule_modal.php'; ?>
        <?php include 'modals/room_professor_modal.php'; ?>
        <?php include 'modals/course_modal.php'; ?>
        <?php include 'modals/confirm_delete_modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>