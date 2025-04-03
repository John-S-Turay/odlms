<?php
// Start a session to manage user data across pages
session_start();

// Include the database connection file
include('includes/dbconnection.php');

// Check if the user is logged in by verifying the session variable 'odlmsuid'
if (!isset($_SESSION['odlmsuid']) || strlen($_SESSION['odlmsuid']) == 0) {
    // Redirect to logout.php if the user is not logged in
    header('location:logout.php');
    exit(); // Stop further execution
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Get the user ID from the session
    $uid = $_SESSION['odlmsuid'];

    // Get the current and new passwords from the form
    $currentpassword = $_POST['currentpassword'];
    $newpassword = $_POST['newpassword'];

    // Fetch the current hashed password from the database
    $sql = "SELECT Password FROM tbluser WHERE ID = :uid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uid', $uid, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        // Verify the current password
        if (password_verify($currentpassword, $result->Password)) {
            // Hash the new password
            $hashedNewPassword = password_hash($newpassword, PASSWORD_DEFAULT);

            // Update the password in the database
            $con = "UPDATE tbluser SET Password = :newpassword WHERE ID = :uid";
            $chngpwd1 = $dbh->prepare($con);
            $chngpwd1->bindParam(':uid', $uid, PDO::PARAM_STR);
            $chngpwd1->bindParam(':newpassword', $hashedNewPassword, PDO::PARAM_STR);
            $chngpwd1->execute();

            // Show success message
            echo '<script>alert("Your password has been successfully changed.")</script>';
        } else {
            // Show error message if the current password is incorrect
            echo '<script>alert("Your current password is incorrect.")</script>';
        }
    } else {
        // Show error message if the user is not found
        echo '<script>alert("User not found.")</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - Change Password</title>
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
    <script src="libs/bower/breakpoints.js/dist/breakpoints.min.js"></script>
    <script>
        Breakpoints(); // Initialize Breakpoints
    </script>
    <script type="text/javascript">
        // JavaScript function to validate the new and confirm password fields
        function checkpass() {
            if (document.changepassword.newpassword.value != document.changepassword.confirmpassword.value) {
                alert('New Password and Confirm Password fields do not match.');
                document.changepassword.confirmpassword.focus();
                return false;
            }
            return true;
        }
    </script>
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
                                <h3 class="widget-title">Change Password</h3>
                            </header><!-- .widget-header -->
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <!-- Change Password Form -->
                                <form class="form-horizontal" onsubmit="return checkpass();" name="changepassword" method="post">
                                    <!-- Current Password Field -->
                                    <div class="form-group">
                                        <label for="currentpassword" class="col-sm-3 control-label">Current Password:</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" name="currentpassword" id="currentpassword" required>
                                        </div>
                                    </div>
                                    <!-- New Password Field -->
                                    <div class="form-group">
                                        <label for="newpassword" class="col-sm-3 control-label">New Password:</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" name="newpassword" id="newpassword" required>
                                        </div>
                                    </div>
                                    <!-- Confirm Password Field -->
                                    <div class="form-group">
                                        <label for="confirmpassword" class="col-sm-3 control-label">Confirm Password:</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" name="confirmpassword" id="confirmpassword" required>
                                        </div>
                                    </div>
                                    <!-- Submit Button -->
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
    <script src="libs/bower/moment/moment.js"></script>
    <script src="libs/bower/fullcalendar/dist/fullcalendar.min.js"></script>
    <script src="assets/js/fullcalendar.js"></script>
</body>
</html>