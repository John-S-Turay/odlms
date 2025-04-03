<?php
// Start a session to manage user data across pages
session_start();

// Include the database connection file
include('includes/dbconnection.php');

// Check if the reset password form is submitted
if (isset($_POST['submit'])) {
    // Get form data
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $newpassword = $_POST['newpassword'];

    // Fetch the user's email and mobile number from the database
    $sql = "SELECT Email FROM tbluser WHERE Email = :email AND MobileNumber = :mobile";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() > 0) {
        // Hash the new password using password_hash()
        $hashedNewPassword = password_hash($newpassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $con = "UPDATE tbluser SET Password = :newpassword WHERE Email = :email AND MobileNumber = :mobile";
        $chngpwd1 = $dbh->prepare($con);
        $chngpwd1->bindParam(':email', $email, PDO::PARAM_STR);
        $chngpwd1->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $chngpwd1->bindParam(':newpassword', $hashedNewPassword, PDO::PARAM_STR);
        $chngpwd1->execute();

        // Show success message
        echo "<script>alert('Your password has been successfully changed.');</script>";
    } else {
        // Show error message if the email or mobile number is invalid
        echo "<script>alert('Email or Mobile number is invalid.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - Forgot Password</title>
    <!-- Include necessary CSS and JavaScript libraries -->
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/misc-pages.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">
    <script type="text/javascript">
        // JavaScript function to validate the new and confirm password fields
        function valid() {
            if (document.chngpwd.newpassword.value != document.chngpwd.confirmpassword.value) {
                alert("New Password and Confirm Password fields do not match!");
                document.chngpwd.confirmpassword.focus();
                return false;
            }
            return true;
        }
    </script>
</head>
<body class="simple-page">
    <!-- Home Button -->
    <div id="back-to-home">
        <a href="../index.php" class="btn btn-outline btn-default"><i class="fa fa-home animated zoomIn"></i></a>
    </div>

    <!-- Password Reset Form -->
    <div class="simple-page-wrap">
        <!-- Logo -->
        <div class="simple-page-logo animated swing">
            <span style="color: white"><i class="fa fa-gg"></i></span>
            <span style="color: white">ODLMS</span>
        </div><!-- .simple-page-logo -->

        <!-- Password Reset Form -->
        <div class="simple-page-form animated flipInY" id="login-form">
            <h4 class="form-title m-b-xl text-center">Reset Your Password</h4>
            <form action="#" method="post" name="chngpwd" onsubmit="return valid();">
                <!-- Email Field -->
                <div class="form-group">
                    <input type="email" class="form-control" placeholder="Email Address" name="email" required>
                </div>

                <!-- Mobile Number Field -->
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Mobile Number" name="mobile" maxlength="10" pattern="[0-9]+" required>
                </div>

                <!-- New Password Field -->
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="New Password" name="newpassword" required>
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="Confirm Password" name="confirmpassword" required>
                </div>

                <!-- Submit Button -->
                <input type="submit" class="btn btn-primary" name="submit" value="RESET">
            </form>
        </div><!-- #login-form -->

        <!-- Footer Links -->
        <div class="simple-page-footer">
            <p style="color: white">Do you have an account? <a href="login.php">SIGN IN</a></p>
        </div><!-- .simple-page-footer -->
    </div><!-- .simple-page-wrap -->
</body>
</html>