<?php
// Start a session with secure settings
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

// Disable error reporting for production (enable during development)
error_reporting(0);

// Include the database connection file
include('includes/dbconnection.php');

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if the user is logged in by verifying the session variable
if (strlen($_SESSION['odlmsaid']) == 0) {
    header('location:logout.php');
    exit(); // Stop further execution
}

// Handle form submission
if (isset($_POST['submit'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $adminid = $_SESSION['odlmsaid'];
    $cpassword = $_POST['currentpassword'];
    $newpassword = $_POST['newpassword'];
    $confirmpassword = $_POST['confirmpassword'];

    // Validate inputs
    if (empty($cpassword) || empty($newpassword) || empty($confirmpassword)) {
        echo '<script>alert("All fields are required.");</script>';
    } elseif ($newpassword !== $confirmpassword) {
        echo '<script>alert("New Password and Confirm Password do not match.");</script>';
    } elseif (strlen($newpassword) < 8 || !preg_match("/[A-Z]/", $newpassword) || !preg_match("/\d/", $newpassword) || !preg_match("/\W/", $newpassword)) {
        echo '<script>alert("Password must be at least 8 characters long, include an uppercase letter, a number, and a special character.");</script>';
    } else {
        // Fetch the current password from the database
        $sql = "SELECT Password FROM tbladmin WHERE ID = :adminid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':adminid', $adminid, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);

        // Verify the current password
        if ($result && password_verify($cpassword, $result->Password)) {
            // Hash the new password with current options
            $hashedPassword = password_hash($newpassword, PASSWORD_DEFAULT);
            
            // Update the password in the database
            $updateSql = "UPDATE tbladmin SET Password = :newpassword WHERE ID = :adminid";
            $updateQuery = $dbh->prepare($updateSql);
            $updateQuery->bindParam(':newpassword', $hashedPassword, PDO::PARAM_STR);
            $updateQuery->bindParam(':adminid', $adminid, PDO::PARAM_STR);

            if ($updateQuery->execute()) {
                // Regenerate session ID after password change
                session_regenerate_id(true);
                
                echo '<script>alert("Your password has been successfully changed.");</script>';
            } else {
                echo '<script>alert("Something went wrong. Please try again.");</script>';
            }
        } else {
            echo '<script>alert("Your current password is incorrect.");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - Change Password</title>
    <!-- Include CSS files -->
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.css">
    <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
    <link rel="stylesheet" href="libs/bower/fullcalendar/dist/fullcalendar.min.css">
    <link rel="stylesheet" href="libs/bower/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">
    <!-- Include JavaScript for Breakpoints -->
    <script src="libs/bower/breakpoints.js/dist/breakpoints.min.js"></script>
    <script>
        Breakpoints();
    </script>
    <!-- Include JavaScript for client-side validation -->
    <script src="assets/js/changePasswordValidator.js"></script>
</head>
<body class="menubar-left menubar-unfold menubar-light theme-primary">
    <!-- Include header -->
    <?php include_once('includes/header.php'); ?>

    <!-- Include sidebar -->
    <?php include_once('includes/sidebar.php'); ?>

    <!-- Main content -->
    <main id="app-main" class="app-main">
        <div class="wrap">
            <section class="app-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h3 class="widget-title">Change Password</h3>
                            </header><!-- .widget-header -->
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <!-- Change password form -->
                                <form class="form-horizontal" onsubmit="return checkpass();" name="changepassword" method="post">
                                    <!-- CSRF Token -->
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    
                                    <!-- Current Password -->
                                    <div class="form-group">
                                        <label for="currentpassword" class="col-sm-3 control-label">Current Password:</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" name="currentpassword" id="currentpassword" required>
                                        </div>
                                    </div>
                                    <!-- New Password -->
                                    <div class="form-group">
                                        <label for="newpassword" class="col-sm-3 control-label">New Password:</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" name="newpassword" required>
                                            <small class="text-muted">Must be at least 8 characters with uppercase, number, and special character</small>
                                        </div>
                                    </div>
                                    <!-- Confirm Password -->
                                    <div class="form-group">
                                        <label for="confirmpassword" class="col-sm-3 control-label">Confirm Password:</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" name="confirmpassword" id="confirmpassword" required>
                                        </div>
                                    </div>
                                    <!-- Submit button -->
                                    <div class="row">
                                        <div class="col-sm-9 col-sm-offset-3">
                                            <button type="submit" class="btn btn-success" name="submit">Change</button>
                                        </div>
                                    </div>
                                </form>
                            </div><!-- .widget-body -->
                        </div><!-- .widget -->
                    </div><!-- END column -->
                </div><!-- .row -->
            </section><!-- .app-content -->
        </div><!-- .wrap -->
        <!-- Include footer -->
        <?php include_once('includes/footer.php'); ?>
    </main>
    <!-- Include customizer -->
    <?php include_once('includes/customizer.php'); ?>

    <!-- Include JavaScript files -->
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
    <script src="libs/bower/moment/moment.js"></script>
    <script src="libs/bower/fullcalendar/dist/fullcalendar.min.js"></script>
    <script src="assets/js/fullcalendar.js"></script>
</body>
</html>