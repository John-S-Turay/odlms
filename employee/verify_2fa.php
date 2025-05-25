<?php
session_start();
include('includes/dbconnection.php');
include('includes/2fa_functions.php');

// DEMO MODE: Verify any 6-digit code
if (isset($_POST['verify_2fa'])) {
    $code = $_POST['2fa_code'];
    
    if (preg_match('/^\d{6}$/', $code)) {
        // Any 6-digit code works in demo mode
        $_SESSION['odlmseid'] = $_SESSION['pending_user_id'];
        $_SESSION['login'] = $_SESSION['pending_email'];
        
        // Clear temporary session
        unset($_SESSION['pending_user_id']);
        unset($_SESSION['pending_email']);
        unset($_SESSION['two_factor_secret']);
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Please enter a valid 6-digit code";
    }
}

// Redirect if no pending verification
if (!isset($_SESSION['pending_user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - 2FA Verification (DEMO)</title>
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/misc-pages.css">
    <style>
        .demo-notice {
            background: #FF5722;
            color: white;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .instruction-box {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="simple-page">
    <div class="simple-page-wrap">
        <!-- Demo Mode Notice -->
        <div class="demo-notice animated swing">
            <i class="fa fa-info-circle"></i> DEMO MODE: Enter any 6-digit code to proceed
        </div>
        
        <div class="simple-page-logo animated swing">
            <span style="color: white"><i class="fa fa-lock"></i></span>
            <span style="color: white">Two-Factor Authentication</span>
        </div>
        
        <div class="simple-page-form animated flipInY" id="login-form">
            <h4 class="form-title m-b-xl text-center">Enter Verification Code</h4>
            
            <!-- Demo Instructions -->
            <div class="instruction-box">
                <p><strong>DEMO INSTRUCTIONS:</strong></p>
                <ol>
                    <li>Open your authenticator app (or imagine you did)</li>
                    <li>Enter <strong>any 6-digit number</strong> below</li>
                </ol>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="#" method="post">
                <div class="form-group">
                    <input type="text" 
                           class="form-control" 
                           placeholder="000000" 
                           name="2fa_code" 
                           pattern="\d{6}" 
                           title="Enter any 6-digit number"
                           required>
                    <small class="text-muted">DEMO: Any 6 digits will work</small>
                </div>
                
                <input type="submit" class="btn btn-primary btn-block" name="verify_2fa" value="Verify">
            </form>
        </div>
        
        <div class="simple-page-footer">
            <p><a href="login.php"><i class="fa fa-arrow-left"></i> Back to login</a></p>
        </div>
    </div>
    
    <script>
        // Auto-focus the code input
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="2fa_code"]').focus();
        });
    </script>
</body>
</html>