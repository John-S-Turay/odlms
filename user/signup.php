<?php
// Start a session to manage user data across pages
session_start();

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection file
include('includes/dbconnection.php');

// Check if the signup form is submitted
if (isset($_POST['submit'])) {
    // Get form data
    $fname = $_POST['fname'];
    $mobno = $_POST['mobno'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the email already exists
    $ret = "SELECT Email FROM tbluser WHERE Email = :email";
    $query = $dbh->prepare($ret);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() == 0) {
        // Hash the password using password_hash()
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $sql = "INSERT INTO tbluser (FullName, MobileNumber, Email, Password) VALUES (:fname, :mobno, :email, :password)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':mobno', $mobno, PDO::PARAM_INT);
        $query->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $query->execute();

        // Get the last inserted ID
        $lastInsertId = $dbh->lastInsertId();

        if ($lastInsertId) {
            // Show success message
            echo "<script>alert('You have signed up successfully.');</script>";
        } else {
            // Show error message if something went wrong
            echo "<script>alert('Something went wrong. Please try again.');</script>";
        }
    } else {
        // Show error message if the email already exists
        echo "<script>alert('Email already exists. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - User Sign Up</title>
    <!-- Include necessary CSS and JavaScript libraries -->
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/misc-pages.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">
</head>
<body class="simple-page">
    <!-- Home Button -->
    <div id="back-to-home">
        <a href="../index.php" class="btn btn-outline btn-default"><i class="fa fa-home animated zoomIn"></i></a>
    </div>

    <!-- Signup Form -->
    <div class="simple-page-wrap">
        <!-- Logo -->
        <div class="simple-page-logo animated swing">
            <span style="color: white">ODLMS</span>
        </div><!-- .simple-page-logo -->

        <!-- Signup Form -->
        <div class="simple-page-form animated flipInY" id="signup-form">
            <h4 class="form-title m-b-xl text-center">Sign Up For a New Account</h4>
            <form action="" method="post">
                <!-- Full Name Field -->
                <div class="form-group">
                    <input id="fname" type="text" class="form-control" placeholder="Full Name" name="fname" required>
                </div>

                <!-- Email Field -->
                <div class="form-group">
                    <input id="email" type="email" class="form-control" placeholder="Email" name="email" required>
                </div>

                <!-- Mobile Number Field -->
                <div class="form-group">
                    <input id="mobno" type="text" class="form-control" placeholder="Mobile" name="mobno" maxlength="10" pattern="[0-9]+" required>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <input id="password" type="password" class="form-control" placeholder="Password" name="password" required>
                </div>

                <!-- Submit Button -->
                <input type="submit" class="btn btn-primary" value="Register" name="submit">
            </form>
        </div><!-- #signup-form -->

        <!-- Footer Links -->
        <div class="simple-page-footer">
            <p>
                <small>Do you have an account?</small>
                <a href="login.php">SIGN IN</a>
            </p>
        </div><!-- .simple-page-footer -->
    </div><!-- .simple-page-wrap -->
</body>
</html>