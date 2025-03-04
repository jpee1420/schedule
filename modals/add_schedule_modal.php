<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm" action="process_schedule.php" method="POST">
                    <div class="mb-3">
                        <label for="course_id" class="form-label">Course</label>
                        <select class="form-control" name="course_id" required>
                            <?php
                            $courses = getAllCourses($conn);
                            foreach ($courses as $course):
                            ?>
                            <option value="<?php echo $course['id']; ?>">
                                <?php echo $course['course_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="professor_id" class="form-label">Professor</label>
                        <select name="professor_id" class="form-control" required>
                            <?php
                            $professors = getAllProfessors($conn);
                            foreach ($professors as $professor):
                            ?>
                            <option value="<?php echo $professor['id']; ?>">
                                <?php echo $professor['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Room</label>
                        <select name="room_id" class="form-control" required>
                            <?php
                            $rooms = getAllRooms($conn);
                            foreach ($rooms as $room):
                            ?>
                            <option value="<?php echo $room['id']; ?>">
                                <?php echo $room['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="day" class="form-label">Day</label>
                        <select name="day" class="form-control" required>
                            <option value="MWF">MWF</option>
                            <option value="TTH">TTH</option>
                            <option value="Sat">Saturday</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>