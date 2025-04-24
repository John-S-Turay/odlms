<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['odlmsaid']==0)) {
    header('location:logout.php');
} else {
    if(isset($_POST['submit'])) {
        $eid = $_GET['editid'];
        $aptid = $_GET['aptid'];
        $status = $_POST['status'];
        $remark = $_POST['remark'];
        $assignee = $_POST['assignee'];
        
        // File validation
        $report = $_FILES["report"]["name"];
        $extension = substr($report, strlen($report)-4, strlen($report));
        $allowed_extensions = array(".pdf");
        
        if(!in_array($extension, $allowed_extensions)) {
            echo "<script>alert('Report has been Invalid format. Only pdf format allowed');</script>";
        } else {
            // Start transaction
            $dbh->beginTransaction();
            
            try {
                // Get file contents
                $fileData = file_get_contents($_FILES["report"]["tmp_name"]);
                $mimeType = mime_content_type($_FILES["report"]["tmp_name"]);
                $fileSize = $_FILES["report"]["size"];
                
                // Insert tracking record
                $sql = "INSERT INTO tbltracking(AppointmentNumeber, Remark, Status) VALUES (:aptid, :remark, :status)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':aptid', $aptid, PDO::PARAM_STR); 
                $query->bindParam(':remark', $remark, PDO::PARAM_STR); 
                $query->bindParam(':status', $status, PDO::PARAM_STR); 
                $query->execute();
                
                // Insert file into reports table
                $sql = "INSERT INTO tbl_reports 
                        (appointment_id, file_name, file_data, mime_type, file_size, uploaded_by) 
                        VALUES (:aptid, :filename, :filedata, :mimetype, :filesize, :userid)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':aptid', $eid, PDO::PARAM_INT);
                $query->bindParam(':filename', $report, PDO::PARAM_STR);
                $query->bindParam(':filedata', $fileData, PDO::PARAM_LOB);
                $query->bindParam(':mimetype', $mimeType, PDO::PARAM_STR);
                $query->bindParam(':filesize', $fileSize, PDO::PARAM_INT);
                $query->bindParam(':userid', $_SESSION['odlmsaid'], PDO::PARAM_INT);
                $query->execute();
                $fileId = $dbh->lastInsertId();
                
                // Update appointment with file reference
                $sql = "UPDATE tblappointment 
                        SET report_id = :reportid, 
                            Status = :status, 
                            Remark = :remark,
                            ReportUploadedDate = NOW() 
                        WHERE ID = :eid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':reportid', $fileId, PDO::PARAM_INT);
                $query->bindParam(':status', $status, PDO::PARAM_STR);
                $query->bindParam(':remark', $remark, PDO::PARAM_STR);
                $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                $query->execute();
                
                $dbh->commit();
                echo '<script>alert("Remark has been updated and report uploaded")</script>';
                echo "<script>window.location.href ='uploaded-reports.php'</script>";
            } catch (Exception $e) {
                $dbh->rollBack();
                echo '<script>alert("Error: '.$e->getMessage().'");</script>';
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ODLMS|| View Detail</title>
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
        Breakpoints();
    </script>
</head>
    
<body class="menubar-left menubar-unfold menubar-light theme-primary">
<!--============= start main area -->
<?php include_once('includes/header.php');?>
<?php include_once('includes/sidebar.php');?>

<!-- APP MAIN ==========-->
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
                            $eid = $_GET['editid'];
                            $sql = "SELECT a.*, 
                                p.id as prescription_id, 
                                p.file_name as prescription_file, 
                                p.mime_type as prescription_mime,
                                p.file_size as prescription_size,
                                r.id as report_id,
                                r.file_name as report_filename,
                                r.mime_type as report_mime,
                                r.file_size as report_size,
                                r.uploaded_by as report_uploader,
                                r.upload_date as report_date
                            FROM tblappointment a 
                            LEFT JOIN tbl_prescriptions p ON a.ID = p.appointment_id 
                            LEFT JOIN tbl_reports r ON a.report_id = r.id
                            WHERE a.ID = :eid";

                            $query = $dbh->prepare($sql);
                            $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                            $cnt = 1;
                            if($query->rowCount() > 0) {
                                foreach($results as $row) {
                            ?>
                            <table border="1" class="table table-bordered mg-b-0">
                                <tr>
                                    <th>Appointment Number</th>
                                    <td><?php echo $aptno = ($row->AppointmentNumber);?></td>
                                    <th>Patient Name</th>
                                    <td><?php echo $row->PatientName;?></td>
                                </tr>
                                <tr>
                                    <th>Gender</th>
                                    <td><?php echo $row->Gender;?></td>
                                    <th>Date of Birth</th>
                                    <td><?php echo $row->DOB;?></td>
                                </tr>
                                <tr>
                                    <th>Mobile Number</th>
                                    <td><?php echo $row->MobileNumber;?></td>
                                    <th>Email</th>
                                    <td><?php echo $row->Email;?></td>
                                </tr>
                                <tr>
                                    <th>Home Address</th>
                                    <td><?php echo htmlspecialchars($row->address); ?></td>
                                    </tr>
                                <tr>
                                    <th>Appointment Date</th>
                                    <td><?php echo $row->AppointmentDate;?></td>
                                    <th>Appointment Time</th>
                                    <td><?php echo $row->AppointmentTime;?></td>
                                </tr>
                                <tr>
                                    <th>Assign To</th>
                                    <?php if($row->AssignTo == "") { ?>
                                        <td><?php echo "Not Updated Yet"; ?></td>
                                    <?php } else { ?>
                                        <td><?php echo htmlentities($row->AssignTo);?></td>
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
                                    <td><?php echo $row->PostDate;?></td>
                                    <th>Order Final Status</th>
                                    <td colspan="4">
                                        <?php
                                        $status = $row->Status;
                                        if($row->Status == "Report Uploaded") {
                                            echo "Report Has been Uploaded";
                                        } elseif($row->Status == "Delivered to Lab") {
                                            echo "Report Not Uploaded Yet";
                                        } else {
                                            echo $status;
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Report Status</th>
                                    <td>
                                    <?php if (!empty($row->report_id)) { ?>
                                        <a href="download-report.php?id=<?php echo $row->report_id; ?>" 
                                        target="_blank" 
                                        class="btn btn-sm btn-primary">
                                        <i class="fa fa-download"></i> Download Report
                                        (<?php echo htmlentities($row->report_filename); ?>)
                                        </a>
                                    <?php } else { echo "Report Not Available"; } ?>
                                    </td>
                                </tr>
                            </table>
                            <?php $cnt = $cnt+1;}} ?>
                            <br>
                            <h4 style="color: blue">Test Detail</h4>
                            <?php
                            $aptid = $_GET['aptid'];
                            $sql = "SELECT tbllabtest.TestTitle,tbllabtest.TestDescription,tbllabtest.TestInterpretation,tbllabtest.Price,tblappointment.UserID,tblappointment.AppointmentNumber, tbltestrequest.AppointmentNumber, tbltestrequest.TestID from tblappointment join tbltestrequest on tblappointment.AppointmentNumber= tbltestrequest.AppointmentNumber join tbllabtest on tbllabtest.ID=tbltestrequest.TestID where tbltestrequest.AppointmentNumber=:aptid";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':aptid', $aptid, PDO::PARAM_STR);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                            $cnt = 1;
                            if($query->rowCount() > 0) {
                            ?>
                            <table border="1" class="table table-bordered mg-b-0">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Test Title</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <?php foreach($results as $row) { ?>
                                <tr>
                                    <td><?php echo htmlentities($cnt);?></td>
                                    <td><?php echo $row->TestTitle;?></td>
                                    <td><?php echo $tprice = $row->Price;?></td>
                                </tr>
                                <?php 
                                $grandtprice += $tprice;
                                $cnt = $cnt+1;
                                } ?>
                                <tr>
                                    <th colspan="2">Grand Total</th>
                                    <th><?php echo $grandtprice; ?></th>
                                </tr>
                            </table>
                            <?php } ?>
                            <br>
                            <?php 
                            $aptid = $_GET['aptid'];
                            if($status != "") {
                                $ret = "select tbltracking.OrderCanclledByUser,tbltracking.Remark,tbltracking.Status as astatus,tbltracking.UpdationDate from tbltracking where tbltracking.AppointmentNumeber =:aptid";
                                $query = $dbh->prepare($ret);
                                $query->bindParam(':aptid', $aptid, PDO::PARAM_STR);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                $cnt = 1;
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
                                <?php foreach($results as $row) { ?>
                                <tr>
                                    <td><?php echo $cnt;?></td>
                                    <td><?php echo $row->Remark;?></td>
                                    <td>
                                        <?php echo $row->astatus;
                                        if($row->OrderCanclledByUser == 1) {
                                            echo "("."by user".")";
                                        } else {
                                            echo "("."by Lab".")";
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $row->UpdationDate;?></td>
                                </tr>
                                <?php $cnt = $cnt+1;} ?>
                            </table>
                            <?php } ?>
                            <?php if ($status == "Delivered to Lab") { ?>
                            <p align="center" style="padding-top: 20px">
                                <button class="btn btn-primary waves-effect waves-light w-lg" data-toggle="modal" data-target="#myModal">Take Action</button>
                            </p>
                            <?php } ?>
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
                                                <form method="post" name="submit" enctype="multipart/form-data">
                                                    <tr>
                                                        <th>Remark :</th>
                                                        <td>
                                                            <textarea type="text" name="remark" placeholder="Remark" rows="12" cols="14" class="form-control wd-450" required="true"></textarea>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Upload Report :</th>
                                                        <td>
                                                            <input type="file" name="report" class="form-control wd-450" required="true">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status :</th>
                                                        <td>
                                                            <select name="status" class="form-control wd-450" required="true">
                                                                <option value="Report Uploaded" selected="true">Report Uploaded</option>
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
  <?php include_once('includes/footer.php');?>
</main>
<?php include_once('includes/customizer.php');?>

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
<?php } ?>