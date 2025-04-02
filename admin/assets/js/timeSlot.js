function addTimeSlot() {
    const container = document.getElementById('timeSlotsContainer');
    const div = document.createElement('div');
    div.className = 'time-slot-box';
    div.innerHTML = `
        <input type="time" class="form-control" name="time_slots[]" required>
        <button type="button" class="btn btn-danger btn-sm remove-time-btn" onclick="removeTimeSlot(this)">
            <i class="fa fa-minus"></i>
        </button>
    `;
    container.appendChild(div);
}

// Remove time slot input field
function removeTimeSlot(button) {
    const container = document.getElementById('timeSlotsContainer');
    const boxes = container.getElementsByClassName('time-slot-box');
    
    // Don't allow removing the last time slot
    if (boxes.length > 1) {
        button.parentNode.remove();
    } else {
        alert('You must have at least one time slot.');
    }
}

// Form validation
$(document).ready(function() {
    $('#addSlotsForm').submit(function() {
        const timeSlots = $('input[name="time_slots[]"]').filter(function() {
            return $(this).val() !== '';
        });
        
        if (timeSlots.length === 0) {
            alert('Please add at least one time slot.');
            return false;
        }
        
        return true;
    });
});