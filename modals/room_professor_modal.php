<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process_room.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Room Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Room</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Professor Modal -->
<div class="modal fade" id="addProfessorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Professor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process_professor.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Professor Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Image</label>
                        <input type="file" class="form-control" name="profile_image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Professor</button>
                </form>
            </div>
        </div>
    </div>
</div>