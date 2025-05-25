<?php
// Start a session to manage user sessions
session_start();

// Disable error reporting for production (enable during development)
error_reporting(0);

// Include the database connection file
include('includes/dbconnection.php');
// Include 2FA functions
include('includes/2fa_functions.php'); 

// Handle form submission
if (isset($_POST['login'])) {
    // Retrieve and sanitize form data
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        echo "<script>alert('Please fill in all fields.');</script>";
    } else {
        // Fetch admin details from the database (including 2FA fields)
        $sql = "SELECT ID, Password, two_factor_enabled, two_factor_secret FROM tbladmin WHERE UserName = :username";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);

        // Verify the password
        if ($result && password_verify($password, $result->Password)) {
            
            // Check if 2FA is enabled for this user
            if ($result->two_factor_enabled) {
                // Store temporary session for 2FA verification
                $_SESSION['pending_user_id'] = $result->ID;
                $_SESSION['pending_username'] = $username;
                $_SESSION['two_factor_secret'] = $result->two_factor_secret;
                
                // Handle "Remember Me" functionality
                if (!empty($_POST['remember'])) {
                    // Set cookies for username and password (valid for 10 years)
                    setcookie("user_login", $username, time() + (10 * 365 * 24 * 60 * 60), "/", "", true, true);
                    setcookie("userpassword", $password, time() + (10 * 365 * 24 * 60 * 60), "/", "", true, true);
                } else {
                    // Clear cookies if "Remember Me" is not checked
                    if (isset($_COOKIE['user_login'])) {
                        setcookie("user_login", "", time() - 3600, "/");
                    }
                    if (isset($_COOKIE['userpassword'])) {
                        setcookie("userpassword", "", time() - 3600, "/");
                    }
                }
                
                // Redirect to 2FA verification
                echo "<script>document.location ='verify_2fa.php';</script>";
                exit();
            } else {
                // No 2FA - proceed with normal login
                $_SESSION['odlmsaid'] = $result->ID;
                $_SESSION['login'] = $username;

                // Handle "Remember Me" functionality
                if (!empty($_POST['remember'])) {
                    // Set cookies for username and password (valid for 10 years)
                    setcookie("user_login", $username, time() + (10 * 365 * 24 * 60 * 60), "/", "", true, true);
                    setcookie("userpassword", $password, time() + (10 * 365 * 24 * 60 * 60), "/", "", true, true);
                } else {
                    // Clear cookies if "Remember Me" is not checked
                    if (isset($_COOKIE['user_login'])) {
                        setcookie("user_login", "", time() - 3600, "/");
                    }
                    if (isset($_COOKIE['userpassword'])) {
                        setcookie("userpassword", "", time() - 3600, "/");
                    }
                }

                // Redirect to the dashboard
                echo "<script>document.location ='dashboard.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid username or password.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - Login Page</title>
    <!-- Include CSS files -->
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/misc-pages.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">
</head>
<body class="simple-page">
    <!-- Home button -->
    <div id="back-to-home">
        <a href="../index.php" class="btn btn-outline btn-default"><i class="fa fa-home animated zoomIn"></i></a>
    </div>

    <!-- Login form -->
    <div class="simple-page-wrap">
        <div class="simple-page-logo animated swing">
            <span style="color: white"><i class="fa fa-gg"></i></span>
            <span style="color: white">ODLMS</span>
        </div><!-- logo -->

        <div class="simple-page-form animated flipInY" id="login-form">
            <h4 class="form-title m-b-xl text-center">Sign In With Your ODLMS Account</h4>
            <form action="#" method="post">
                <!-- Username input -->
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="User Name" required name="username" value="<?php if (isset($_COOKIE['user_login'])) { echo htmlspecialchars($_COOKIE['user_login']); } ?>">
                </div>

                <!-- Password input -->
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="Password" required name="password" value="<?php if (isset($_COOKIE['userpassword'])) { echo htmlspecialchars($_COOKIE['userpassword']); } ?>">
                </div>

                <!-- Remember Me checkbox -->
                <div class="form-group m-b-xl">
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" id="remember" name="remember" <?php if (isset($_COOKIE['user_login'])) { echo 'checked'; } ?>>
                        <label for="remember">Keep me signed in</label>
                    </div>
                </div>

                <!-- Submit button -->
                <input type="submit" class="btn btn-primary" name="login" value="SIGN IN">
            </form>
        </div><!-- #login-form -->

        <!-- Forgot password link -->
        <div class="simple-page-footer">
            <p><a href="forgot-password.php">FORGOT YOUR PASSWORD?</a></p>
        </div><!-- .simple-page-footer -->
    </div><!-- .simple-page-wrap -->
</body>
</html>