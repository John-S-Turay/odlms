<?php
session_start();
include('includes/dbconnection.php');
include('includes/2fa_functions.php');

if (!isset($_SESSION['odlmsaid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['odlmsaid'];
$query = $dbh->prepare("SELECT Email, two_factor_enabled, two_factor_secret FROM tbladmin WHERE ID = :id");
$query->bindParam(':id', $user_id, PDO::PARAM_INT);
$query->execute();
$user = $query->fetch(PDO::FETCH_OBJ);

// Handle form submissions
if (isset($_POST['enable_2fa'])) {
    $secret = generate2FASecret();
    $query = $dbh->prepare("UPDATE tbladmin SET two_factor_secret = :secret WHERE ID = :id");
    $query->bindParam(':secret', $secret, PDO::PARAM_STR);
    $query->bindParam(':id', $user_id, PDO::PARAM_INT);
    $query->execute();
    
    // Refresh user data
    $query->execute();
    $user = $query->fetch(PDO::FETCH_OBJ);
}

if (isset($_POST['confirm_2fa'])) {
    $code = $_POST['2fa_code'];
    if (verify2FACode($user->two_factor_secret, $code)) {
        $query = $dbh->prepare("UPDATE tbladmin SET two_factor_enabled = 1 WHERE ID = :id");
        $query->bindParam(':id', $user_id, PDO::PARAM_INT);
        $query->execute();
        
        $success = "Two-factor authentication has been enabled successfully! (DEMO MODE)";
        $user->two_factor_enabled = 1;
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}

if (isset($_POST['disable_2fa'])) {
    $query = $dbh->prepare("UPDATE tbladmin SET two_factor_enabled = 0, two_factor_secret = NULL WHERE ID = :id");
    $query->bindParam(':id', $user_id, PDO::PARAM_INT);
    $query->execute();
    
    $success = "Two-factor authentication has been disabled. (DEMO MODE)";
    $user->two_factor_enabled = 0;
    $user->two_factor_secret = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS || Security Settings</title>
    <!-- Include necessary CSS and JavaScript libraries -->
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.css">
    <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
    <link rel="stylesheet" href="libs/bower/fullcalendar/dist/fullcalendar.min.css">
    <link rel="stylesheet" href="libs/bower/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">
    <style>
        .demo-notice {
            background: #FF5722;
            color: white;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .qr-code-container {
            text-align: center;
            margin: 20px 0;
        }
        .instruction-box {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="menubar-left menubar-unfold menubar-light theme-primary">
    <!-- Include header and sidebar -->
    <?php include_once('includes/header.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>

    <!-- Main Content -->
    <main id="app-main" class="app-main">
        <div class="wrap">
            <section class="app-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h4 class="widget-title" style="color: blue">Security Settings</h4>
                            </header>
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <!-- Demo Notice -->
                                <div class="demo-notice">
                                    <i class="fa fa-info-circle"></i> DEMO MODE: Two-factor authentication is simulated
                                </div>

                                <?php if (isset($success)): ?>
                                    <div class="alert alert-success"><?php echo $success; ?></div>
                                <?php endif; ?>
                                
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>

                                <div class="card">
                                    <div class="card-header">
                                        <h5>Two-Factor Authentication</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($user->two_factor_enabled): ?>
                                            <div class="alert alert-success">
                                                <strong>Status:</strong> Enabled (DEMO MODE)
                                            </div>
                                            <p>Two-factor authentication is currently active for your account.</p>
                                            <form method="post">
                                                <button type="submit" name="disable_2fa" class="btn btn-danger">
                                                    <i class="fa fa-lock"></i> Disable 2FA
                                                </button>
                                            </form>
                                        <?php elseif ($user->two_factor_secret): ?>
                                            <div class="alert alert-warning">
                                                <strong>Status:</strong> Pending Activation (DEMO MODE)
                                            </div>
                                            
                                            <div class="instruction-box">
                                                <h5><i class="fa fa-info-circle"></i> DEMO INSTRUCTIONS:</h5>
                                                <ol>
                                                    <li>Imagine scanning this QR code with your authenticator app</li>
                                                    <li>Enter <strong>any 6-digit number</strong> to complete setup</li>
                                                </ol>
                                            </div>
                                            
                                            <div class="qr-code-container">
                                                <img src="<?php echo getQRCode($user->Email, $user->two_factor_secret); ?>" alt="DEMO QR Code" style="max-width: 200px;">
                                                <p class="text-muted">Secret Key: <?php echo chunk_split($user->two_factor_secret, 4, ' '); ?></p>
                                            </div>
                                            
                                            <form method="post">
                                                <div class="form-group">
                                                    <label>Verification Code</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="2fa_code" 
                                                           placeholder="000000" 
                                                           pattern="\d{6}" 
                                                           title="Enter any 6-digit number"
                                                           required>
                                                    <small class="text-muted">DEMO: Any 6 digits will work</small>
                                                </div>
                                                <button type="submit" name="confirm_2fa" class="btn btn-primary">
                                                    <i class="fa fa-check"></i> Verify & Enable
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <div class="alert alert-danger">
                                                <strong>Status:</strong> Disabled
                                            </div>
                                            <p>Two-factor authentication adds an extra layer of security to your account.</p>
                                            <form method="post">
                                                <button type="submit" name="enable_2fa" class="btn btn-primary">
                                                    <i class="fa fa-lock"></i> Enable 2FA (DEMO)
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div><!-- .widget-body -->
                        </div><!-- .widget -->
                    </div><!-- END column -->
                </div><!-- .row -->
            </section><!-- .app-content -->
        </div><!-- .wrap -->

        <!-- Include footer -->
        <?php include_once('includes/footer.php'); ?>
    </main><!-- .app-main -->

    <!-- Include customizer -->
    <?php include_once('includes/customizer.php'); ?>

    <!-- Include necessary JavaScript libraries -->
    <script src="libs/bower/jquery/dist/jquery.js"></script>
    <script src="libs/bower/jquery-ui/jquery-ui.min.js"></script>
    <script src="libs/bower/jQuery-Storage-API/jquery.storageapi.min.js"></script>
    <script src="libs/bower/bootstrap-sass/assets/javascripts/bootstrap.js"></script>
    <script src="libs/bower/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="libs/bower/perfect-scrollbar/js/perfect-scrollbar.jquery.js"></script>
    <script src="libs/bower/PACE/pace.min.js"></script>
    <script src="assets/js/library.js"></script>
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        // Auto-focus the code input if present
        document.addEventListener('DOMContentLoaded', function() {
            var codeInput = document.querySelector('input[name="2fa_code"]');
            if (codeInput) {
                codeInput.focus();
            }
        });
    </script>
</body>
</html>