$(document).ready(function() {
    // Toggle credit card fields based on payment method selection
    $('#payment_method').change(function() {
        if ($(this).val() === 'card') {
            $('#card_payment_fields').show();
            $('#card_number, #card_expiry, #card_cvc, #card_name').prop('required', true);
        } else {
            $('#card_payment_fields').hide();
            $('#card_number, #card_expiry, #card_cvc, #card_name').prop('required', false);
        }
    });
    
    // Format card number
    $('#card_number').on('input', function() {
        this.value = this.value.replace(/[^\d]/g, '').substring(0, 16);
    });
    
    // Format expiry date
    $('#card_expiry').on('input', function() {
        this.value = this.value.replace(/[^\d\/]/g, '');
        if (this.value.length === 2 && !this.value.includes('/')) {
            this.value = this.value + '/';
        }
        this.value = this.value.substring(0, 5);
    });
    
    // Format CVC
    $('#card_cvc').on('input', function() {
        this.value = this.value.replace(/[^\d]/g, '').substring(0, 3);
    });
});