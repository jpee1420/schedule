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

function editSchedule(id, roomId, courseId, professorId, day, startTime, endTime) {
    editRecord('#addScheduleModal', {
        id: id,
        course_id: courseId,
        room_id: roomId,
        professor_id: professorId,
        start_time: startTime,
        end_time: endTime,
        day: day
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
    if (confirm('Are you sure you want to delete this schedule?')) {
        window.location.href = `process_schedule.php?action=delete&id=${id}`;
    }
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
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update the badge without reloading the page
            const scheduleCards = document.querySelectorAll('.schedule-card');
            scheduleCards.forEach(card => {
                if (card.querySelector(`[data-professor-id="${professorId}"]`)) {
                    const badge = card.querySelector('.badge');
                    let badgeClass = 'success';
                    if (status === 'Absent') {
                        badgeClass = 'danger';
                    } else if (status === 'On Leave') {
                        badgeClass = 'warning';
                    }
                    badge.className = `badge bg-${badgeClass}`;
                    badge.textContent = status;
                }
            });
        } else {
            alert(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating status');
    });
}

function validateScheduleForm(form) {
    const formData = new FormData(form);
    formData.append('action', 'validate'); // Add action parameter
    
    return fetch('process_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server returned ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Schedule validation failed');
            return false;
        }
        return true;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while validating the schedule');
        return false;
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

function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alertContainer');
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    alertContainer.innerHTML = alertHTML;

    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        const alertElement = document.querySelector('.alert');
        if (alertElement) {
            const bsAlert = new bootstrap.Alert(alertElement);
            bsAlert.close();
        }
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 3 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 3000);
    });

    // Schedule form submission
    const scheduleForm = document.querySelector('#addScheduleModal form');
    if (scheduleForm) {
        scheduleForm.addEventListener('submit', function(e) {
            const modal = bootstrap.Modal.getInstance(document.querySelector('#addScheduleModal'));
            if (modal) {
                modal.hide();
            }
        });
    }
});