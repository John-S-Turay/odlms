<?php
// Start a session to manage user sessions
session_start();

// Disable error reporting for production (enable during development)
error_reporting(0);

// Include the database connection file
include('includes/dbconnection.php');

// Check if the user is logged in by verifying the session variable
if (strlen($_SESSION['odlmsaid']) == 0) {
    // Redirect to logout.php if the session is invalid
    header('location:logout.php');
    exit(); // Stop further execution
} else {
    // Handle form submission
    if (isset($_POST['submit'])) {
        // Retrieve and sanitize form data
        $title = htmlspecialchars($_POST['title']);
        $desc = htmlspecialchars($_POST['description']);
        $interpretation = htmlspecialchars($_POST['interpretation']);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Validate inputs
        if (empty($title) || empty($desc) || empty($interpretation) || empty($price)) {
            echo '<script>alert("All fields are required.");</script>';
        } elseif (!is_numeric($price) || $price <= 0) {
            echo '<script>alert("Invalid price. Please enter a valid number.");</script>';
        } else {
            // Insert new test details into the database
            $sql = "INSERT INTO tbllabtest (TestTitle, TestDescription, TestInterpretation, Price) 
                    VALUES (:title, :desc, :interpretation, :price)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':title', $title, PDO::PARAM_STR);
            $query->bindParam(':desc', $desc, PDO::PARAM_STR);
            $query->bindParam(':interpretation', $interpretation, PDO::PARAM_STR);
            $query->bindParam(':price', $price, PDO::PARAM_STR);

            // Execute the query
            if ($query->execute()) {
                echo '<script>alert("Test detail has been added.");</script>';
                echo "<script>window.location.href ='add-test.php';</script>";
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - Add Test Detail</title>
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
                                <h3 class="widget-title">Add Test Detail</h3>
                            </header>
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <!-- Test form -->
                                <form class="form-horizontal" method="post">
                                    <!-- Test Title -->
                                    <div class="form-group">
                                        <label for="exampleTextInput1" class="col-sm-3 control-label">Test Title:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="exampleTextInput1" name="title" value="" required>
                                        </div>
                                    </div>
                                    <!-- Test Description -->
                                    <div class="form-group">
                                        <label for="email2" class="col-sm-3 control-label">Test Description:</label>
                                        <div class="col-sm-9">
                                            <textarea type="text" class="form-control" id="email2" name="description" value="" required></textarea>
                                        </div>
                                    </div>
                                    <!-- Test Interpretation -->
                                    <div class="form-group">
                                        <label for="email2" class="col-sm-3 control-label">Test Interpretation:</label>
                                        <div class="col-sm-9">
                                            <textarea type="text" class="form-control" id="email2" name="interpretation" value="" required></textarea>
                                        </div>
                                    </div>
                                    <!-- Price -->
                                    <div class="form-group">
                                        <label for="email2" class="col-sm-3 control-label">Price:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="email2" name="price" value="" required>
                                        </div>
                                    </div>
                                    <!-- Submit button -->
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