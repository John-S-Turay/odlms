<?php
// Start a session to manage user data across pages
session_start();

// Include the database connection file
include('includes/dbconnection.php');

// Check if the login form is submitted
if (isset($_POST['login'])) {
    // Get the email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch the user's hashed password from the database
    $sql = "SELECT ID, Password FROM tbluser WHERE Email = :email";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        // Verify the password
        if (password_verify($password, $result->Password)) {
            // Set session variables
            $_SESSION['odlmsuid'] = $result->ID;
            $_SESSION['login'] = $email;

            // Redirect to the dashboard
            echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>";
        } else {
            // Show error message if the password is incorrect
            echo "<script>alert('Invalid Email or Password');</script>";
        }
    } else {
        // Show error message if the email is not found
        echo "<script>alert('Invalid Email or Password');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - Login Page</title>
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

    <!-- Login Form -->
    <div class="simple-page-wrap">
        <!-- Logo -->
        <div class="simple-page-logo animated swing">
            <span style="color: white"><i class="fa fa-gg"></i></span>
            <span style="color: white">ODLMS</span>
        </div><!-- .simple-page-logo -->

        <!-- Login Form -->
        <div class="simple-page-form animated flipInY" id="login-form">
            <h4 class="form-title m-b-xl text-center">Sign In With Your ODLMS Account</h4>
            <form action="#" method="post" name="login">
                <!-- Email Field -->
                <div class="form-group">
                    <input type="email" class="form-control" placeholder="Email" required="true" name="email">
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="Password" name="password" required="true">
                </div>

                <!-- Submit Button -->
                <input type="submit" class="btn btn-primary" name="login" value="Sign IN">
            </form>
        </div><!-- #login-form -->

        <!-- Footer Links -->
        <div class="simple-page-footer">
            <p><a href="forgot-password.php">FORGOT YOUR PASSWORD ?</a></p>
            <p><a href="signup.php">Don't have an account ? CREATE AN ACCOUNT</a></p>
        </div><!-- .simple-page-footer -->
    </div><!-- .simple-page-wrap -->
</body>
</html>