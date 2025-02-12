<form method="POST" action="process_professor.php" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="profile_image" class="form-label">Profile Image</label>
        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
        <small class="form-text text-muted">Allowed formats: jpg, jpeg, png, gif</small>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>