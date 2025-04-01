<?php
// Start a session to manage user sessions
session_start();

// Disable error reporting for production (enable during development)
error_reporting(0);

// Include the database connection file
include('includes/dbconnection.php');

// Check if the user is logged in by verifying the session variable
if (strlen($_SESSION['odlmsaid']) == 0) {
    header('location:logout.php');
    exit(); // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - Dashboard</title>
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
                    <!-- Total Registered Users -->
                    <div class="col-md-3 col-sm-6">
                        <div class="widget stats-widget">
                            <div class="widget-body clearfix">
                                <div class="pull-left">
                                    <?php
                                    $sql1 = "SELECT COUNT(*) as total FROM tbluser";
                                    $query1 = $dbh->prepare($sql1);
                                    $query1->execute();
                                    $result1 = $query1->fetch(PDO::FETCH_OBJ);
                                    $totregusers = $result1->total;
                                    ?>
                                    <h3 class="widget-title text-primary"><span class="counter" data-plugin="counterUp"><?php echo htmlentities($totregusers); ?></span></h3>
                                    <small class="text-color">Total Reg Users</small>
                                </div>
                                <span class="pull-right big-icon watermark"><i class="fa fa-paperclip"></i></span>
                            </div>
                            <footer class="widget-footer bg-primary">
                                <a href="view-regusers.php"><small>View Detail</small></a>
                                <span class="small-chart pull-right" data-plugin="sparkline" data-options="[4,3,5,2,1], { type: 'bar', barColor: '#ffffff', barWidth: 5, barSpacing: 2 }"></span>
                            </footer>
                        </div><!-- .widget -->
                    </div>

                    <!-- Total New Appointments -->
                    <div class="col-md-3 col-sm-6">
                        <div class="widget stats-widget">
                            <div class="widget-body clearfix">
                                <div class="pull-left">
                                    <?php
                                    $sql2 = "SELECT COUNT(*) as total FROM tblappointment WHERE Status IS NULL";
                                    $query2 = $dbh->prepare($sql2);
                                    $query2->execute();
                                    $result2 = $query2->fetch(PDO::FETCH_OBJ);
                                    $totnewapt = $result2->total;
                                    ?>
                                    <h3 class="widget-title text-danger"><span class="counter" data-plugin="counterUp"><?php echo htmlentities($totnewapt); ?></span></h3>
                                    <small class="text-color">Total New Appointment</small>
                                </div>
                                <span class="pull-right big-icon watermark"><i class="fa fa-ban"></i></span>
                            </div>
                            <footer class="widget-footer bg-danger">
                                <a href="new-appointment.php"><small>View Detail</small></a>
                                <span class="small-chart pull-right" data-plugin="sparkline" data-options="[1,2,3,5,4], { type: 'bar', barColor: '#ffffff', barWidth: 5, barSpacing: 2 }"></span>
                            </footer>
                        </div><!-- .widget -->
                    </div>

                    <!-- Total Approved Appointments -->
                    <div class="col-md-3 col-sm-6">
                        <div class="widget stats-widget">
                            <div class="widget-body clearfix">
                                <div class="pull-left">
                                    <?php
                                    $sql3 = "SELECT COUNT(*) as total FROM tblappointment WHERE Status = 'Approved'";
                                    $query3 = $dbh->prepare($sql3);
                                    $query3->execute();
                                    $result3 = $query3->fetch(PDO::FETCH_OBJ);
                                    $totaprapt = $result3->total;
                                    ?>
                                    <h3 class="widget-title text-success"><span class="counter" data-plugin="counterUp"><?php echo htmlentities($totaprapt); ?></span></h3>
                                    <small class="text-color">Total Approved Appointment</small>
                                </div>
                                <span class="pull-right big-icon watermark"><i class="fa fa-unlock-alt"></i></span>
                            </div>
                            <footer class="widget-footer bg-success">
                                <a href="approved-appointment.php"><small>View Detail</small></a>
                                <span class="small-chart pull-right" data-plugin="sparkline" data-options="[2,4,3,4,3], { type: 'bar', barColor: '#ffffff', barWidth: 5, barSpacing: 2 }"></span>
                            </footer>
                        </div><!-- .widget -->
                    </div>

                    <!-- Total Rejected Appointments -->
                    <div class="col-md-3 col-sm-6">
                        <div class="widget stats-widget">
                            <div class="widget-body clearfix">
                                <div class="pull-left">
                                    <?php
                                    $sql4 = "SELECT COUNT(*) as total FROM tblappointment WHERE Status = 'Rejected'";
                                    $query4 = $dbh->prepare($sql4);
                                    $query4->execute();
                                    $result4 = $query4->fetch(PDO::FETCH_OBJ);
                                    $totrejapt = $result4->total;
                                    ?>
                                    <h3 class="widget-title text-warning"><span class="counter" data-plugin="counterUp"><?php echo htmlentities($totrejapt); ?></span></h3>
                                    <small class="text-color">Total Rejected Appointment</small>
                                </div>
                                <span class="pull-right big-icon watermark"><i class="fa fa-file-text-o"></i></span>
                            </div>
                            <footer class="widget-footer bg-warning">
                                <a href="rejected-appointment.php"><small>View Detail</small></a>
                                <span class="small-chart pull-right" data-plugin="sparkline" data-options="[5,4,3,5,2],{ type: 'bar', barColor: '#ffffff', barWidth: 5, barSpacing: 2 }"></span>
                            </footer>
                        </div><!-- .widget -->
                    </div>
                </div><!-- .row -->

                <div class="row">
                    <!-- Appointment Cancelled -->
                    <div class="col-md-3 col-sm-6">
                        <div class="widget stats-widget">
                            <div class="widget-body clearfix">
                                <div class="pull-left">
                                    <?php
                                    $sql5 = "SELECT COUNT(*) as total FROM tblappointment WHERE Status = 'Order Cancelled'";
                                    $query5 = $dbh->prepare($sql5);
                                    $query5->execute();
                                    $result5 = $query5->fetch(PDO::FETCH_OBJ);
                                    $totuserscan = $result5->total;
                                    ?>
                                    <h3 class="widget-title text-primary"><span class="counter" data-plugin="counterUp"><?php echo htmlentities($totuserscan); ?></span></h3>
                                    <small class="text-color">Appointment Cancelled</small>
                                </div>
                                <span class="pull-right big-icon watermark"><i class="fa fa-paperclip"></i></span>
                            </div>
                            <footer class="widget-footer bg-primary">
                                <a href="usercancel-appointment.php"><small>View Detail</small></a>
                                <span class="small-chart pull-right" data-plugin="sparkline" data-options="[4,3,5,2,1], { type: 'bar', barColor: '#ffffff', barWidth: 5, barSpacing: 2 }"></span>
                            </footer>
                        </div><!-- .widget -->
                    </div>

                    <!-- Total Sample Received -->
                    <div class="col-md-3 col-sm-6">
                        <div class="widget stats-widget">
                            <div class="widget-body clearfix">
                                <div class="pull-left">
                                    <?php
                                    $sql6 = "SELECT COUNT(*) as total FROM tblappointment WHERE Status = 'Delivered to Lab'";
                                    $query6 = $dbh->prepare($sql6);
                                    $query6->execute();
                                    $result6 = $query6->fetch(PDO::FETCH_OBJ);
                                    $totsamreiv = $result6->total;
                                    ?>
                                    <h3 class="widget-title text-danger"><span class="counter" data-plugin="counterUp"><?php echo htmlentities($totsamreiv); ?></span></h3>
                                    <small class="text-color">Total Sample Received</small>
                                </div>
                                <span class="pull-right big-icon watermark"><i class="fa fa-ban"></i></span>
                            </div>
                            <footer class="widget-footer bg-danger">
                                <a href="sample-received.php"><small>View Detail</small></a>
                                <span class="small-chart pull-right" data-plugin="sparkline" data-options="[1,2,3,5,4], { type: 'bar', barColor: '#ffffff', barWidth: 5, barSpacing: 2 }"></span>
                            </footer>
                        </div><!-- .widget -->
                    </div>

                    <!-- Total Report Uploaded -->
                    <div class="col-md-3 col-sm-6">
                        <div class="widget stats-widget">
                            <div class="widget-body clearfix">
                                <div class="pull-left">
                                    <?php
                                    $sql7 = "SELECT COUNT(*) as total FROM tblappointment WHERE Status = 'Report Uploaded'";
                                    $query7 = $dbh->prepare($sql7);
                                    $query7->execute();
                                    $result7 = $query7->fetch(PDO::FETCH_OBJ);
                                    $totrepup = $result7->total;
                                    ?>
                                    <h3 class="widget-title text-success"><span class="counter" data-plugin="counterUp"><?php echo htmlentities($totrepup); ?></span></h3>
                                    <small class="text-color">Total Report Uploaded</small>
                                </div>
                                <span class="pull-right big-icon watermark"><i class="fa fa-unlock-alt"></i></span>
                            </div>
                            <footer class="widget-footer bg-success">
                                <a href="uploaded-reports.php"><small>View Detail</small></a>
                                <span class="small-chart pull-right" data-plugin="sparkline" data-options="[2,4,3,4,3], { type: 'bar', barColor: '#ffffff', barWidth: 5, barSpacing: 2 }"></span>
                            </footer>
                        </div><!-- .widget -->
                    </div>

                    <!-- Total Employees -->
                    <div class="col-md-3 col-sm-6">
                        <div class="widget stats-widget">
                            <div class="widget-body clearfix">
                                <div class="pull-left">
                                    <?php
                                    $sql8 = "SELECT COUNT(*) as total FROM tblemployee";
                                    $query8 = $dbh->prepare($sql8);
                                    $query8->execute();
                                    $result8 = $query8->fetch(PDO::FETCH_OBJ);
                                    $totemp = $result8->total;
                                    ?>
                                    <h3 class="widget-title text-warning"><span class="counter" data-plugin="counterUp"><?php echo htmlentities($totemp); ?></span></h3>
                                    <small class="text-color">Total Employee</small>
                                </div>
                                <span class="pull-right big-icon watermark"><i class="fa fa-file-text-o"></i></span>
                            </div>
                            <footer class="widget-footer bg-warning">
                                <a href="manage-lab-emp.php"><small>View Detail</small></a>
                                <span class="small-chart pull-right" data-plugin="sparkline" data-options="[5,4,3,5,2],{ type: 'bar', barColor: '#ffffff', barWidth: 5, barSpacing: 2 }"></span>
                            </footer>
                        </div><!-- .widget -->
                    </div>
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