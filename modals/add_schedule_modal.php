<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process_schedule.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Professor</label>
                        <select class="form-select" name="professor_id" required>
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
                        <label class="form-label">Room</label>
                        <select class="form-select" name="room_id" required>
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
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Day</label>
                        <select class="form-select" name="day" required>
                            <option value="MWF">MWF</option>
                            <option value="TTH">TTH</option>
                            <option value="Sat">Saturday</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </form>
            </div>
        </div>
    </div>
</div>