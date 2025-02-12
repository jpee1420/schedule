// Generic edit function
function editRecord(modalId, data) {
    const modal = document.querySelector(modalId);
    if (!modal) return;

    // Clear any existing hidden id field
    const existingHiddenField = modal.querySelector('input[name="id"]');
    if (existingHiddenField) existingHiddenField.remove();

    // Populate form fields
    Object.keys(data).forEach(key => {
        const input = modal.querySelector(`[name="${key}"]`);
        if (input) input.value = data[key];
    });

    // Add hidden id field
    const form = modal.querySelector('form');
    const hiddenField = document.createElement('input');
    hiddenField.type = 'hidden';
    hiddenField.name = 'id';
    hiddenField.value = data.id;
    form.appendChild(hiddenField);

    // Show modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}

// Generic delete function
function deleteRecord(url, id) {
    if (confirm('Are you sure you want to delete this record?')) {
        window.location.href = `${url}?action=delete&id=${id}`;
    }
}

// Specific functions for each type
function editRoom(id, name) {
    editRecord('#addRoomModal', {
        id: id,
        name: name
    });
}

function editProfessor(id, name) {
    editRecord('#addProfessorModal', {
        id: id,
        name: name
    });
}

function editSchedule(id, roomId, subject, professorId, day, startTime, endTime, note, status) {
    editRecord('#addScheduleModal', {
        id: id,
        subject: subject,
        room_id: roomId,
        professor_id: professorId,
        start_time: startTime,
        end_time: endTime,
        day: day,
        notes: note
    });
}

function editCourse(id, code, name) {
    editRecord('#addCourseModal', {
        id: id,
        course_code: code,
        course_name: name
    });
}

// Delete functions
function deleteRoom(id) {
    deleteRecord('process_room.php', id);
}

function deleteProfessor(id) {
    deleteRecord('process_professor.php', id);
}

function deleteSchedule(id) {
    deleteRecord('process_schedule.php', id);
}

function deleteCourse(id) {
    deleteRecord('process_course.php', id);
}

function updateProfessorStatus(professorId, status) {
    fetch('process_professor.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=updateStatus&id=${professorId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh the schedule cards that show this professor
            location.reload();
        } else {
            alert('Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating status');
    });
}

// Keep tab state after page reload
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash) {
        const tab = new bootstrap.Tab(document.querySelector(`a[data-bs-target="${hash}"]`));
        tab.show();
    }
});

// Modal reset handlers
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                const hiddenId = form.querySelector('input[name="id"]');
                if (hiddenId) hiddenId.remove();
            }
        });
    });
});