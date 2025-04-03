<?php
// Start a session to manage user data across pages
session_start();

// Enable error reporting for debugging (disable in production)
error_reporting(0);
ini_set('display_errors', 1);

// Include the database connection file
include('includes/dbconnection.php');

// Check if the user is logged in by verifying the session variable 'odlmsuid'
if (!isset($_SESSION['odlmsuid']) || strlen($_SESSION['odlmsuid']) == 0) {
  // Redirect to logout.php if the user is not logged in
  header('location:logout.php');
  exit(); // Stop further execution
}

// Get the test ID from the URL
$vid = $_GET['viewid'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - View Test Detail</title>
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
                    <!-- Test Details -->
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h4 class="widget-title" style="color: blue">Test Details</h4>
                            </header><!-- .widget-header -->
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <?php
                                // Fetch test details from the database
                                $sql = "SELECT * FROM tbllabtest WHERE ID = :vid";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':vid', $vid, PDO::PARAM_STR);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);

                                if ($query->rowCount() > 0) {
                                    foreach ($results as $row) {
                                ?>
                                        <table border="1" class="table table-bordered mg-b-0">
                                            <tr>
                                                <th>Test Name</th>
                                                <td><?php echo htmlspecialchars($row->TestTitle); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Test Description</th>
                                                <td><?php echo htmlspecialchars($row->TestDescription); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Test Interpretation</th>
                                                <td><?php echo htmlspecialchars($row->TestInterpretation); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Price</th>
                                                <td><?php echo htmlspecialchars($row->Price); ?></td>
                                            </tr>
                                        </table>
                                <?php
                                    }
                                } else {
                                    echo '<p class="text-center">No test details found.</p>';
                                }
                                ?>
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