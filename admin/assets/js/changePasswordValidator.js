function checkpass() {
    const newPassword = document.changepassword.newpassword.value;
    const confirmPassword = document.changepassword.confirmpassword.value;
    
    if (newPassword !== confirmPassword) {
        alert('New Password and Confirm Password do not match.');
        document.changepassword.confirmpassword.focus();
        return false;
    }
    
    // Client-side password strength validation
    const minLength = 8;
    const hasUpper = /[A-Z]/.test(newPassword);
    const hasNumber = /\d/.test(newPassword);
    const hasSpecial = /\W/.test(newPassword);
    
    if (newPassword.length < minLength || !hasUpper || !hasNumber || !hasSpecial) {
        alert('Password must be at least 8 characters long, include an uppercase letter, a number, and a special character.');
        return false;
    }
    
    return true;
}