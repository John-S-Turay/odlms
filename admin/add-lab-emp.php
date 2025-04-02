<?php
// Start a session to manage user sessions
session_start();

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection file
include('includes/dbconnection.php');

// Check if the user is logged in by verifying the session variable
if (!isset($_SESSION['odlmsaid']) || empty($_SESSION['odlmsaid'])) {
    // Redirect to logout.php if the session is invalid
    header('location:logout.php');
    exit(); // Stop further execution
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Retrieve and sanitize form data
    $empid = htmlspecialchars($_POST['empid']);
    $name = htmlspecialchars($_POST['name']);
    $mobnum = htmlspecialchars($_POST['mobnum']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars($_POST['address']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($empid) || empty($name) || empty($mobnum) || empty($email) || empty($address) || empty($password)) {
        echo "<script>alert('All fields are required.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email address.');</script>";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobnum)) {
        echo "<script>alert('Invalid mobile number. It must be 10 digits.');</script>";
    } else {
        // Check if email, mobile number, or employee ID already exists
        $ret = "SELECT Email FROM tblemployee WHERE Email = :email OR MobileNumber = :mobnum OR EmpID = :empid";
        $query = $dbh->prepare($ret);
        $query->bindParam(':empid', $empid, PDO::PARAM_STR);
        $query->bindParam(':mobnum', $mobnum, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() > 0) {
            echo "<script>alert('Email, Employee ID, or Mobile Number already exists.');</script>";
        } else {
            // Hash the password securely
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new employee details into the database
            $sql = "INSERT INTO tblemployee (EmpID, Name, MobileNumber, Email, Address, Password) 
                    VALUES (:empid, :name, :mobnum, :email, :address, :password)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':empid', $empid, PDO::PARAM_STR);
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':mobnum', $mobnum, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':address', $address, PDO::PARAM_STR);
            $query->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

            if ($query->execute()) {
                echo '<script>alert("Employee details added successfully.");</script>';
                echo "<script>window.location.href ='add-lab-emp.php';</script>";
            } else {
                echo '<script>alert("Something went wrong. Please try again.");</script>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>ODLMS - Add Employee Detail</title>
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
    <!-- Include Breakpoints script -->
    <script src="libs/bower/breakpoints.js/dist/breakpoints.min.js"></script>
    <script>
        Breakpoints(); // Initialize Breakpoints
    </script>
</head>
<body class="menubar-left menubar-unfold menubar-light theme-primary">
    <!-- Include header -->
    <?php include_once('includes/header.php'); ?>

    <!-- Include sidebar -->
    <?php include_once('includes/sidebar.php'); ?>

    <!-- Main content area -->
    <main id="app-main" class="app-main">
        <div class="wrap">
            <section class="app-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h3 class="widget-title">Add Employee Detail</h3>
                            </header>
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <!-- Form to add employee details -->
                                <form class="form-horizontal" method="post">
                                    <div class="form-group">
                                        <label for="exampleTextInput1" class="col-sm-3 control-label">Employee ID:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="exampleTextInput1" name="empid" value="" required="true">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleTextInput1" class="col-sm-3 control-label">Name:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="exampleTextInput1" name="name" value="" required="true">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="email2" class="col-sm-3 control-label">Mobile Number:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="email2" name="mobnum" value="" required="true" maxlength="10" pattern="[0-9]+">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="email2" class="col-sm-3 control-label">Email:</label>
                                        <div class="col-sm-9">
                                            <input type="email" class="form-control" id="email2" name="email" value="" required="true">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="email2" class="col-sm-3 control-label">Address:</label>
                                        <div class="col-sm-9">
                                            <textarea type="text" class="form-control" id="email2" name="address" value="" required="true"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="email2" class="col-sm-3 control-label">Password:</label>
                                        <div class="col-sm-9">
                                            <input type="password" class="form-control" id="email2" name="password" value="" required="true">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-9 col-sm-offset-3">
                                            <button type="submit" class="btn btn-success" name="submit">Add</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
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