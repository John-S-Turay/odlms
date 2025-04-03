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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS || View Test Detail</title>
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
                    <!-- DOM dataTable -->
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h4 class="widget-title">Appointment Details</h4>
                            </header><!-- .widget-header -->
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover js-basic-example dataTable table-custom">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Appointment Number</th>
                                                <th>Patient Name</th>
                                                <th>Mobile Number</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Fetch appointment details for the logged-in user
                                            $uid = $_SESSION['odlmsuid'];
                                            $sql = "SELECT * FROM tblappointment WHERE UserID = :uid";
                                            $query = $dbh->prepare($sql);
                                            $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                                            $cnt = 1;
                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $row) {
                                            ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($cnt); ?></td>
                                                        <td><?php echo htmlentities($row->AppointmentNumber); ?></td>
                                                        <td><?php echo htmlentities($row->PatientName); ?></td>
                                                        <td><?php echo htmlentities($row->MobileNumber); ?></td>
                                                        <td><?php echo htmlentities($row->Email); ?></td>
                                                        <td>
                                                            <?php
                                                            if (empty($row->Status)) {
                                                                echo "Not Updated Yet";
                                                            } else {
                                                                echo htmlentities($row->Status);
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <a href="view-appointment-detail.php?viewid=<?php echo htmlentities($row->ID); ?>&&aptid=<?php echo htmlentities($row->AppointmentNumber); ?>" aria-label="View Appointment Details">
                                                                <i class="fa fa-eye" aria-hidden="true"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                            <?php
                                                    $cnt++;
                                                }
                                            } else {
                                                echo '<tr><td colspan="7" class="text-center">No appointments found.</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Appointment Number</th>
                                                <th>Patient Name</th>
                                                <th>Mobile Number</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
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