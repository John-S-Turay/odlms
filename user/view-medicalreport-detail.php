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

// Get the appointment ID from the URL
$vid = $_GET['viewid'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS || View Medical Report</title>
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
    <script language="javascript" type="text/javascript">
        // JavaScript function to open a pop-up window
        function popUpWindow(URLStr, left, top, width, height) {
            if (popUpWin && !popUpWin.closed) popUpWin.close();
            popUpWin = open(URLStr, 'popUpWin', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',screenX=' + left + ',screenY=' + top);
        }
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
                    <!-- Appointment Details -->
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h4 class="widget-title" style="color: blue">Appointment Details</h4>
                            </header><!-- .widget-header -->
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <div class="table-responsive">
                                    <?php
                                    // Fetch appointment details from the database
                                    $sql = "SELECT * FROM tblappointment WHERE ID = :vid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':vid', $vid, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $row) {
                                            $aptno = $row->AppointmentNumber;
                                    ?>
                                            <table border="1" class="table table-bordered mg-b-0">
                                                <tr>
                                                    <th>Appointment Number</th>
                                                    <td><?php echo htmlspecialchars($aptno); ?></td>
                                                    <th>Patient Name</th>
                                                    <td><?php echo htmlspecialchars($row->PatientName); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Gender</th>
                                                    <td><?php echo htmlspecialchars($row->Gender); ?></td>
                                                    <th>Date of Birth</th>
                                                    <td><?php echo htmlspecialchars($row->DOB); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Mobile Number</th>
                                                    <td><?php echo htmlspecialchars($row->MobileNumber); ?></td>
                                                    <th>Email</th>
                                                    <td><?php echo htmlspecialchars($row->Email); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Appointment Date</th>
                                                    <td><?php echo htmlspecialchars($row->AppointmentDate); ?></td>
                                                    <th>Appointment Time</th>
                                                    <td><?php echo htmlspecialchars($row->AppointmentTime); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Prescription</th>
                                                    <td>
                                                        <?php
                                                        if (!empty($row->Prescription)) {
                                                            echo '<a href="images/' . htmlspecialchars($row->Prescription) . '" target="_blank">Download Prescription</a>';
                                                        } else {
                                                            echo "NA";
                                                        }
                                                        ?>
                                                    </td>
                                                    <th>Date of Birth</th>
                                                    <td><?php echo htmlspecialchars($row->DOB); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Apply Date</th>
                                                    <td><?php echo htmlspecialchars($row->PostDate); ?></td>
                                                    <th>Status</th>
                                                    <td>
                                                        <?php
                                                        $status = $row->Status;
                                                        if (empty($status)) {
                                                            echo "Not Updated Yet";
                                                        } else {
                                                            echo htmlspecialchars($status);
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Admin Remark</th>
                                                    <td>
                                                        <?php
                                                        if (empty($status)) {
                                                            echo "Not Updated Yet";
                                                        } else {
                                                            echo htmlspecialchars($row->Remark);
                                                        }
                                                        ?>
                                                    </td>
                                                    <th>Updation Date</th>
                                                    <td>
                                                        <?php
                                                        if (empty($row->Status)) {
                                                            echo "Not Updated Yet";
                                                        } else {
                                                            echo htmlspecialchars($row->UpdationDate);
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Report</th>
                                                    <td>
                                                        <?php
                                                        if (!empty($row->Report)) {
                                                            echo '<a href="../admin/images/' . htmlspecialchars($row->Report) . '" target="_blank">Download Report</a>';
                                                        } else {
                                                            echo "NA";
                                                        }
                                                        ?>
                                                    </td>
                                                    <th>Report Uploaded Date</th>
                                                    <td><?php echo htmlspecialchars($row->ReportUploadedDate); ?></td>
                                                </tr>
                                            </table>
                                    <?php
                                        }
                                    } else {
                                        echo '<p class="text-center">No appointment details found.</p>';
                                    }
                                    ?>
                                </div>

                                <!-- Test Details -->
                                <h4 style="color: blue; margin-top: 20px;">Test Details</h4>
                                <?php
                                // Fetch test details for the appointment
                                $sql = "SELECT tbllabtest.TestTitle, tbllabtest.Price 
                                        FROM tblappointment 
                                        JOIN tbltestrequest ON tblappointment.AppointmentNumber = tbltestrequest.AppointmentNumber 
                                        JOIN tbllabtest ON tbllabtest.ID = tbltestrequest.TestID 
                                        WHERE tbltestrequest.AppointmentNumber = :aptno";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':aptno', $aptno, PDO::PARAM_STR);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);

                                if ($query->rowCount() > 0) {
                                    $grandtprice = 0;
                                ?>
                                    <table border="1" class="table table-bordered mg-b-0">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Test Title</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $cnt = 1;
                                            foreach ($results as $row) {
                                                $grandtprice += $row->Price;
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($cnt); ?></td>
                                                    <td><?php echo htmlspecialchars($row->TestTitle); ?></td>
                                                    <td><?php echo htmlspecialchars($row->Price); ?></td>
                                                </tr>
                                            <?php
                                                $cnt++;
                                            }
                                            ?>
                                            <tr>
                                                <th colspan="2">Grand Total</th>
                                                <th><?php echo htmlspecialchars($grandtprice); ?></th>
                                            </tr>
                                        </tbody>
                                    </table>
                                <?php
                                } else {
                                    echo '<p class="text-center">No test details found.</p>';
                                }
                                ?>

                                <!-- Tracking History -->
                                <?php
                                if (!empty($status)) {
                                    $aptid = $_GET['aptid'];
                                    $sql = "SELECT tbltracking.OrderCanclledByUser, tbltracking.Remark, tbltracking.Status as astatus, tbltracking.UpdationDate 
                                            FROM tbltracking 
                                            WHERE tbltracking.AppointmentNumeber = :aptid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':aptid', $aptid, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                    if ($query->rowCount() > 0) {
                                ?>
                                        <h4 style="color: blue; margin-top: 20px;">Tracking History</h4>
                                        <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                                <tr align="center">
                                                    <th colspan="4" style="color: blue">Tracking History</th>
                                                </tr>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Remark</th>
                                                    <th>Status</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $cnt = 1;
                                                foreach ($results as $row) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($cnt); ?></td>
                                                        <td><?php echo htmlspecialchars($row->Remark); ?></td>
                                                        <td>
                                                            <?php
                                                            echo htmlspecialchars($row->astatus);
                                                            if ($row->OrderCanclledByUser == 1) {
                                                                echo " (by user)";
                                                            } else {
                                                                echo " (by Lab)";
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row->UpdationDate); ?></td>
                                                    </tr>
                                                <?php
                                                    $cnt++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                <?php
                                    } else {
                                        echo '<p class="text-center">No tracking history found.</p>';
                                    }
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