<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check if employee is logged in
if (strlen($_SESSION['odlmseid']) == 0) {
    header('location:logout.php');
    exit();
}

// Handle password change form submission
if (isset($_POST['submit'])) {
    $eid = $_SESSION['odlmseid'];
    $currentPassword = $_POST['currentpassword'];
    $newPassword = $_POST['newpassword'];
    $confirmPassword = $_POST['confirmpassword'];

    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo '<script>alert("All fields are required.")</script>';
    } elseif ($newPassword !== $confirmPassword) {
        echo '<script>alert("New Password and Confirm Password do not match.")</script>';
    } else {
        // Get current password hash from database
        $sql = "SELECT Password FROM tblemployee WHERE ID = :eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':eid', $eid, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);

        if ($result && password_verify($currentPassword, $result->Password)) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password in database
            $updateSql = "UPDATE tblemployee SET Password = :newpassword WHERE ID = :eid";
            $updateQuery = $dbh->prepare($updateSql);
            $updateQuery->bindParam(':newpassword', $hashedPassword, PDO::PARAM_STR);
            $updateQuery->bindParam(':eid', $eid, PDO::PARAM_INT);
            
            if ($updateQuery->execute()) {
                echo '<script>alert("Your password has been successfully changed.")</script>';
            } else {
                echo '<script>alert("Something went wrong. Please try again.")</script>';
            }
        } else {
            echo '<script>alert("Your current password is incorrect.")</script>';
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
    <!-- CSS includes -->
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
        Breakpoints();
    </script>
    <script type="text/javascript">
        function checkpass() {
            if (document.changepassword.newpassword.value != document.changepassword.confirmpassword.value) {
                alert('New Password and Confirm Password do not match');
                document.changepassword.confirmpassword.focus();
                return false;
            }
            return true;
        }
    </script>
</head>
<body class="menubar-left menubar-unfold menubar-light theme-primary">
    <?php include_once('includes/header.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>

    <main id="app-main" class="app-main">
        <div class="wrap">
            <section class="app-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h3 class="widget-title">Change Password</h3>
                            </header>
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <form class="form-horizontal" onsubmit="return checkpass();" name="changepassword" method="post">
                                    <div class="form-group">
                                        <label for="currentpassword" class="col-sm-3 control-label">Current Password:</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" name="currentpassword" id="currentpassword" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="newpassword" class="col-sm-3 control-label">New Password:</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" name="newpassword" id="newpassword" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmpassword" class="col-sm-3 control-label">Confirm Password:</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" name="confirmpassword" id="confirmpassword" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-9 col-sm-offset-3">
                                            <button type="submit" class="btn btn-success" name="submit">Change</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <?php include_once('includes/footer.php'); ?>
    </main>

    <?php include_once('includes/customizer.php'); ?>

    <!-- JavaScript includes -->
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