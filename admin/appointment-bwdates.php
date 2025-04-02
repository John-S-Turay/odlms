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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - Appointment Between Dates Report</title>
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
    <script>
        function validateForm() {
            const fromDate = document.forms["bwdatesreport"]["fromdate"].value;
            const toDate = document.forms["bwdatesreport"]["todate"].value;

            if (fromDate === "" || toDate === "") {
                alert("Please select both From Date and To Date.");
                return false;
            }

            if (new Date(toDate) < new Date(fromDate)) {
                alert("To Date must be greater than or equal to From Date.");
                return false;
            }

            return true;
        }
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
                                <h3 class="widget-title">Between Dates Report of Appointments</h3>
                            </header><!-- .widget-header -->
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <!-- Form for selecting date range -->
                                <form class="form-horizontal" method="post" name="bwdatesreport" action="appointment-bwdates-reports-details.php" onsubmit="return validateForm()">
                                    <!-- From Date -->
                                    <div class="form-group">
                                        <label for="fromdate" class="col-sm-3 control-label">From Date:</label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control" id="fromdate" name="fromdate" required>
                                        </div>
                                    </div>
                                    <!-- To Date -->
                                    <div class="form-group">
                                        <label for="todate" class="col-sm-3 control-label">To Date:</label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control" id="todate" name="todate" required>
                                        </div>
                                    </div>
                                    <!-- Submit button -->
                                    <div class="row">
                                        <div class="col-sm-9 col-sm-offset-3">
                                            <button type="submit" class="btn btn-success" name="submit">Submit</button>
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