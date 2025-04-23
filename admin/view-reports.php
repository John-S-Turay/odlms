<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['odlmsaid'])==0) {
    header('location:logout.php');
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ODLMS || View Lab Reports</title>
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
                        <h4 class="widget-title">Lab Reports</h4>
                    </header><!-- .widget-header -->
                    <hr class="widget-separator">
                    <div class="widget-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover js-basic-example dataTable table-custom">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Report Number</th>
                                        <th>Patient Name</th>
                                        <th>Collection Date</th>
                                        <th>Report Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            
                                <tbody>
                  <?php
$sql="SELECT r.*, p.full_name
      FROM lab_reports r
      JOIN patients p ON r.patient_id = p.patient_id
      WHERE r.doctor_id = :doctor_id
      ORDER BY r.report_date DESC";
$query = $dbh->prepare($sql);
$query->bindParam(':doctor_id', $_SESSION['odlmsaid'], PDO::PARAM_INT);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);

$cnt=1;
if($query->rowCount() > 0) {
    foreach($results as $row) {               
                  ?>
                                    <tr>
                                        <td><?php echo htmlentities($cnt);?></td>
                                        <td><?php echo htmlentities($row->report_number);?></td>
                                        <td><?php echo htmlentities($row->full_name);?></td>
                                        <td><?php echo htmlentities(date("d/m/Y", strtotime($row->collection_date)));?></td>
                                        <td><?php echo htmlentities(date("d/m/Y", strtotime($row->report_date)));?></td>
                                        <td><?php echo htmlentities($row->status);?></td>                
                                        <td>
                                            <a href="view-report-detail.php?report_id=<?php echo htmlentities($row->report_id);?>" title="View Full Report">
                                                <i class="fa fa-eye" aria-hidden="true"></i>
                                            </a>
                                            <a href="generate-pdf.php?report_id=<?php echo htmlentities($row->report_id);?>" title="Download PDF" class="ml-2">
                                                <i class="fa fa-download" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                  <?php 
                  $cnt=$cnt+1;
                  }
                  } 
                  ?> 
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Report Number</th>
                                        <th>Patient Name</th>
                                        <th>Collection Date</th>
                                        <th>Report Date</th>
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
  <!-- APP FOOTER -->
  <?php include_once('includes/footer.php');?>
  <!-- /#app-footer -->
</main>
<!--========== END app main -->

<!-- APP CUSTOMIZER -->
<?php include_once('includes/customizer.php');?>

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
<script src="libs/bower/moment/moment.js"></script>
<script src="libs/bower/fullcalendar/dist/fullcalendar.min.js"></script>
<script src="assets/js/fullcalendar.js"></script>
</body>
</html>
<?php } ?>