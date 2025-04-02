<?php
// Start a session to manage user sessions
session_start();

// Disable error reporting for production (enable during development)
error_reporting(0);

// Include the database connection file
include('includes/dbconnection.php');

// Handle form submission
if (isset($_POST['login'])) {
    // Retrieve and sanitize form data
    $empid = htmlspecialchars($_POST['empid']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($empid) || empty($password)) {
        echo "<script>alert('Please fill in all fields.');</script>";
    } else {
        // Fetch employee details from the database
        $sql = "SELECT ID, EmpID, Password FROM tblemployee WHERE EmpID = :empid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid', $empid, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
            foreach ($results as $result) {
                // Verify the password
                if (password_verify($password, $result->Password)) {
                    // Set session variables
                    $_SESSION['odlmseid'] = $result->ID;
                    $_SESSION['odlmsempid'] = $result->EmpID;
                    $_SESSION['login'] = $empid;

                    // Redirect to the dashboard
                    echo "<script>document.location ='dashboard.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Invalid password.');</script>";
                }
            }
        } else {
            echo "<script>alert('Invalid Employee ID.');</script>";
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
            <form action="#" method="post" name="login">
                <!-- Employee ID input -->
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Employee ID" required name="empid">
                </div>

                <!-- Password input -->
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="Password" required name="password">
                </div>

                <!-- Submit button -->
                <input type="submit" class="btn btn-primary" name="login" value="Sign IN">
            </form>
        </div><!-- #login-form -->

        <!-- Forgot password link -->
        <div class="simple-page-footer">
            <p><a href="forgot-password.php">FORGOT YOUR PASSWORD?</a></p>
        </div><!-- .simple-page-footer -->
    </div><!-- .simple-page-wrap -->
</body>
</html>