$(document).ready(function() {
    // When date changes, load available times
    $('#aptdate').change(function() {
        const date = $(this).val();
        const isLab = $('input[name="tids[]"]:checked').length > 0;
        
        if (!date) {
            $('#apttime').html('<option value="">Select Date first</option>').prop('disabled', true);
            return;
        }

        $('#apttime').html('<option value="">Loading...</option>');
        
        $.ajax({
            url: "get_available_times.php",
            type: "POST",
            data: { 
                date: date,
                is_lab: isLab ? 1 : 0
            },
            success: function(response) {
                if (response.times && response.times.length > 0) {
                    let options = '<option value="">Select Time</option>';
                    response.times.forEach(function(slot) {
                        const start = new Date('1970-01-01T' + slot.start_time + 'Z');
                       // const end = new Date('1970-01-01T' + slot.end_time + 'Z');
                        const startStr = start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                       // const endStr = end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                         
                        options += `<option value="${slot.start_time}" 
                                    data-max="${slot.max_slots}" 
                                    data-booked="${slot.booked}">
                                    ${startStr} (${slot.max_slots - slot.booked} slots left)
                                    </option>`;
                    });
                    $('#apttime').html(options).prop('disabled', false);
                    $('#slot-details').html(`Showing ${response.times.length} available time slots`);
                    
                    // Show booked slots information
                    if (response.booked_slots && response.booked_slots.length > 0) {
                        $('#slot-details').append('<div class="text-danger mt-1">Fully booked: ' + 
                            response.booked_slots.map(slot => {
                                const start = new Date('1970-01-01T' + slot.start_time + 'Z');
                                //const end = new Date('1970-01-01T' + slot.end_time + 'Z');
                                return start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            }).join(', ') + '</div>');
                    }
                } else {
                    $('#apttime').html('<option value="">Select a Lab Test</option>');
                    let message = 'No available time slots for selected date';
                    if (response.booked_slots && response.booked_slots.length > 0) {
                        message += '. Fully booked time slots: ' + 
                            response.booked_slots.map(slot => {
                                const start = new Date('1970-01-01T' + slot.start_time + 'Z');
                               // const end = new Date('1970-01-01T' + slot.end_time + 'Z');
                                return start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            }).join(', ');
                    }
                    $('#slot-details').html(message);
                }
            },
            error: function() {
                $('#apttime').html('<option value="">Error loading times</option>');
                $('#slot-details').html('Error loading available time slots');
            }
        });
    });

    // When test selection changes, reload times if date is already selected
    $('input[name="tids[]"]').change(function() {
        if ($('#aptdate').val()) {
            $('#aptdate').trigger('change');
        }
    });
});