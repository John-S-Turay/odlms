<?php
// Start session to manage user authentication
session_start();

// Error reporting configuration (disabled for production)
error_reporting(0);

// Include database connection file
include('includes/dbconnection.php');

// Check if admin is logged in, redirect to logout if not
if (strlen($_SESSION['odlmsaid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    // Process form submission for updating appointment status
    if (isset($_POST['submit'])) {
        // Get parameters from URL
        $eid = $_GET['editid'];
        $aptid = $_GET['aptid'];
        
        // Get form data
        $status = $_POST['status'];
        $remark = $_POST['remark'];
        $assignee = $_POST['assignee'];

        // Insert tracking information
        $sql = "INSERT INTO tbltracking(AppointmentNumeber, Remark, Status) VALUES (:aptid, :remark, :status)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':aptid', $aptid, PDO::PARAM_STR); 
        $query->bindParam(':remark', $remark, PDO::PARAM_STR); 
        $query->bindParam(':status', $status, PDO::PARAM_STR); 
        $query->execute();

        // Update appointment information
        $sql = "UPDATE tblappointment SET AssignTo = :assignee, Status = :status, Remark = :remark WHERE ID = :eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':assignee', $assignee, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':remark', $remark, PDO::PARAM_STR);
        $query->bindParam(':eid', $eid, PDO::PARAM_STR);
        $query->execute();

        // Show success message and redirect
        echo '<script>alert("Remark has been updated")</script>';
        echo "<script>window.location.href ='approved-appointment.php'</script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ODLMS || View Appointment Detail</title>
    
    <!-- CSS Includes -->
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.css">
    <!-- build:css assets/css/app.min.css -->
    <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
    <link rel="stylesheet" href="libs/bower/fullcalendar/dist/fullcalendar.min.css">
    <link rel="stylesheet" href="libs/bower/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <!-- endbuild -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">
    
    <!-- JavaScript Includes -->
    <script src="libs/bower/breakpoints.js/dist/breakpoints.min.js"></script>
    <script>
        Breakpoints(); // Initialize responsive breakpoints
    </script>
</head>

<body class="menubar-left menubar-unfold menubar-light theme-primary">
    <!-- Header and Sidebar Includes -->
    <?php include_once('includes/header.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>

    <!-- Main Content Area -->
    <main id="app-main" class="app-main">
        <div class="wrap">
            <section class="app-content">
                <div class="row">
                    <!-- DOM dataTable -->
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h4 class="widget-title" style="color: blue">Appointment Details</h4>
                            </header>
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <div class="table-responsive">
                                    <?php
                                    // Get appointment ID from URL
                                    $eid = $_GET['editid'];
                                    
                                    // Query to get appointment details with prescription data
                                    $sql = "SELECT a.*, p.id as prescription_id, p.file_name, p.mime_type, p.file_size
                                            FROM tblappointment a 
                                            LEFT JOIN tbl_prescriptions p ON a.ID = p.appointment_id 
                                            WHERE a.ID = :eid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                    $cnt = 1;
                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $row) {
                                    ?>
                                    <!-- Appointment Details Table -->
                                    <table border="1" class="table table-bordered mg-b-0">
                                        <tr>
                                            <th>Appointment Number</th>
                                            <td><?php echo $aptno = ($row->AppointmentNumber); ?></td>
                                            <th>Patient Name</th>
                                            <td><?php echo $row->PatientName; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Gender</th>
                                            <td><?php echo $row->Gender; ?></td>
                                            <th>Date of Birth</th>
                                            <td><?php echo $row->DOB; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Mobile Number</th>
                                            <td><?php echo $row->MobileNumber; ?></td>
                                            <th>Email</th>
                                            <td><?php echo $row->Email; ?></td>
                                        </tr>
                                        
                                        <tr>
                                            <th>Home Address</th>
                                            <td><?php echo !empty($row->address) ? $row->address : 'Not provided'; ?></td>
                                            <th>Appointment Time</th>
                                            <td><?php echo $row->AppointmentTime; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Appointment Date</th>
                                            <td><?php echo $row->AppointmentDate; ?></td>
                                            <th>Apply Date</th>
                                            <td><?php echo $row->PostDate; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Assign To</th>
                                            <?php if ($row->AssignTo == "") { ?>
                                                <td><?php echo "Not Updated Yet"; ?></td>
                                            <?php } else { ?>
                                                <td><?php echo htmlentities($row->AssignTo); ?></td>
                                            <?php } ?>  
                                            
                                            <th>Prescription</th>
                                            <td colspan="3">
                                                <?php if (!empty($row->prescription_id)) { ?>
                                                    <a href="download-prescription.php?id=<?php echo $row->prescription_id; ?>" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-primary">
                                                       <i class="fa fa-download"></i> Download Prescription
                                                       <?php if (!empty($row->file_name)): ?>
                                                           (<?php echo htmlentities($row->file_name); ?>)
                                                       <?php endif; ?>
                                                    </a>
                                                <?php } else {
                                                    echo "NA";
                                                } ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Apply Date</th>
                                            <td><?php echo $row->PostDate; ?></td>
                                            <th>Order Final Status</th>
                                            <td colspan="4">
                                                <?php
                                                $status = $row->Status;
                                                if ($row->Status == "Approved") {
                                                    echo "Your Appointment has been approved";
                                                } elseif ($row->Status == "Report Uploaded") {
                                                    echo "Your Report has been sent";
                                                } elseif ($row->Status == "Rejected") {
                                                    echo "Your Appointment has been cancelled";
                                                } else {
                                                    echo "Not Response Yet";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Admin Remark</th>
                                            <?php if ($row->Status == "") { ?>
                                                <td><?php echo "Not Updated Yet"; ?></td>
                                            <?php } else { ?>
                                                <td><?php echo htmlentities($row->Status); ?></td>
                                            <?php } ?>  
                                            
                                        </tr>
                                    <?php $cnt = $cnt + 1; } } ?>
                                    </table> 
                                    <br>
                                    
                                    <!-- Test Details Section -->
                                    <?php
                                    $aptid = $_GET['aptid'];            
                                    $sql = "SELECT tbllabtest.TestTitle, tbllabtest.TestDescription, tbllabtest.TestInterpretation, 
                                            tbllabtest.Price, tblappointment.UserID, tblappointment.AppointmentNumber,  
                                            tbltestrequest.AppointmentNumber, tbltestrequest.TestID 
                                            FROM tblappointment 
                                            JOIN tbltestrequest ON tblappointment.AppointmentNumber = tbltestrequest.AppointmentNumber 
                                            JOIN tbllabtest ON tbllabtest.ID = tbltestrequest.TestID  
                                            WHERE tbltestrequest.AppointmentNumber = :aptid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':aptid', $aptid, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                    $cnt = 1;
                                    if ($query->rowCount() > 0) {
                                    ?>
                                    <table border="1" class="table table-bordered mg-b-0">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Test Title</th>
                                                <th>Price</th>                                            
                                            </tr>
                                        </thead>
                                        <?php 
                                        foreach ($results as $row) { 
                                            $tprice = $row->Price;
                                            $grandtprice += $tprice;
                                        ?>
                                        <tr>
                                            <td><?php echo htmlentities($cnt); ?></td>
                                            <td><?php echo $row->TestTitle; ?></td>
                                            <td><?php echo $tprice; ?></td>
                                        </tr>
                                        <?php $cnt = $cnt + 1; } ?>
                                        <tr>
                                            <th colspan="2">Grand Total</th>
                                            <th><?php echo $grandtprice; ?></th>
                                        </tr>
                                    </table> 
                                    <?php } ?>
                                    <br>
                                    
                                    <!-- Tracking History Section -->
                                    <?php 
                                    $aptid = $_GET['aptid']; 
                                    if ($status != "") {
                                        $ret = "SELECT tbltracking.OrderCanclledByUser, tbltracking.Remark, 
                                               tbltracking.Status as astatus, tbltracking.UpdationDate 
                                               FROM tbltracking 
                                               WHERE tbltracking.AppointmentNumeber = :aptid";
                                        $query = $dbh->prepare($ret);
                                        $query->bindParam(':aptid', $aptid, PDO::PARAM_STR);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;
                                        $cancelledby = $row->OrderCanclledByUser;
                                    ?>
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <tr align="center">
                                            <th colspan="4" style="color: blue">Tracking History</th> 
                                        </tr>
                                        <tr>
                                            <th>#</th>
                                            <th>Remark</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                        </tr>
                                        <?php  
                                        foreach ($results as $row) { 
                                        ?>
                                        <tr>
                                            <td><?php echo $cnt; ?></td>
                                            <td><?php echo $row->Remark; ?></td> 
                                            <td>
                                                <?php  
                                                echo $row->astatus;
                                                if ($row->OrderCanclledByUser == 1) {
                                                    echo " (by user)";
                                                } else {
                                                    echo " (by Lab)";
                                                }
                                                ?>
                                            </td> 
                                            <td><?php echo $row->UpdationDate; ?></td> 
                                        </tr>
                                        <?php $cnt = $cnt + 1; } ?>
                                    </table>
                                    <?php } ?>
                                    
                                    <!-- Action Button (shown only if status is not set) -->
                                    <?php if ($status == "") { ?> 
                                    <p align="center" style="padding-top: 20px">                            
                                        <button class="btn btn-primary waves-effect waves-light w-lg" data-toggle="modal" data-target="#myModal">Take Action</button>
                                    </p>  
                                    <?php } ?>
                                    
                                    <!-- Action Modal -->
                                    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Take Action</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-bordered table-hover data-tables">
                                                        <form method="post" name="submit">
                                                            <tr>
                                                                <th>Remark:</th>
                                                                <td>
                                                                    <textarea name="remark" placeholder="Remark" rows="12" cols="14" class="form-control wd-450" required="true"></textarea>
                                                                </td>
                                                            </tr> 
                                                            <tr>
                                                                <th>Assign to:</th>
                                                                <td>
                                                                    <select name="assignee" placeholder="Assign To" class="form-control wd-450">
                                                                        <option value="">Assign To</option>
                                                                        <?php 
                                                                        $sql2 = "SELECT * FROM tblemployee";
                                                                        $query2 = $dbh->prepare($sql2);
                                                                        $query2->execute();
                                                                        $result2 = $query2->fetchAll(PDO::FETCH_OBJ);

                                                                        foreach ($result2 as $row) {          
                                                                        ?>  
                                                                        <option value="<?php echo htmlentities($row->EmpID); ?>"><?php echo htmlentities($row->EmpID); ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </td>
                                                            </tr> 
                                                            <tr>
                                                                <th>Status:</th>
                                                                <td>
                                                                    <select name="status" class="form-control wd-450" required="true">
                                                                        <option value="Approved" selected="true">Approved</option>
                                                                        <option value="Rejected">Rejected</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                    </table>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" name="submit" class="btn btn-primary">Update</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <!-- Footer Include -->
        <?php include_once('includes/footer.php'); ?>
    </main>

    <!-- Customizer Include -->
    <?php include_once('includes/customizer.php'); ?>

    <!-- JavaScript Libraries -->
    <!-- build:js assets/js/core.min.js -->
    <script src="libs/bower/jquery/dist/jquery.js"></script>
    <script src="libs/bower/jquery-ui/jquery-ui.min.js"></script>
    <script src="libs/bower/jQuery-Storage-API/jquery.storageapi.min.js"></script>
    <script src="libs/bower/bootstrap-sass/assets/javascripts/bootstrap.js"></script>
    <script src="libs/bower/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="libs/bower/perfect-scrollbar/js/perfect-scrollbar.jquery.js"></script>
    <script src="libs/bower/PACE/pace.min.js"></script>
    <!-- endbuild -->

    <!-- build:js assets/js/app.min.js -->
    <script src="assets/js/library.js"></script>
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/app.js"></script>
    <!-- endbuild -->
    
    <!-- Calendar Scripts -->
    <script src="libs/bower/moment/moment.js"></script>
    <script src="libs/bower/fullcalendar/dist/fullcalendar.min.js"></script>
    <script src="assets/js/fullcalendar.js"></script>
</body>
</html>
<?php } ?>