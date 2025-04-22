<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (!isset($_SESSION['odlmsaid']) || strlen($_SESSION['odlmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Get report ID from URL
$report_id = $_GET['report_id'] ?? 0;

// Fetch report details
$report = [];
$patient = [];
$clinic = [];
$blood_tests = [];
$imaging = [];

try {
    // Main report data with patient and clinic info
    $stmt = $dbh->prepare("
        SELECT r.*, p.*, c.*, a.AdminName AS doctor_name, 
               a.Email AS doctor_email, a.MobileNumber AS doctor_phone
        FROM lab_reports r
        JOIN patients p ON r.patient_id = p.patient_id
        JOIN clinics c ON r.clinic_id = c.clinic_id
        JOIN tbladmin a ON r.doctor_id = a.ID
        WHERE r.report_id = ?
    ");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        die("Report not found");
    }

    // Fetch blood test results
    $stmt = $dbh->prepare("SELECT * FROM blood_tests WHERE report_id = ?");
    $stmt->execute([$report_id]);
    $blood_tests = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch imaging results
    $stmt = $dbh->prepare("SELECT * FROM imaging_results WHERE report_id = ?");
    $stmt->execute([$report_id]);
    $imaging = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate patient age
    $dob = new DateTime($report['date_of_birth']);
    $now = new DateTime();
    $age = $now->diff($dob)->y;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Function to determine if value is normal
function isNormal($value, $min, $max) {
    if ($value === null) return '';
    return ($value >= $min && $value <= $max) ? 'normal-value' : 'abnormal-value';
}

// Function to check organ status
function getOrganStatus($observation) {
    if (empty($observation)) return '';
    return (stripos($observation, 'normal') !== false) ? 'organ-healthy' : 'organ-unhealthy';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Online Diagnostic Lab || Lab Report</title>
    <!-- Your existing CSS includes -->
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.css">
    <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
    <link rel="stylesheet" href="libs/bower/fullcalendar/dist/fullcalendar.min.css">
    <link rel="stylesheet" href="libs/bower/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">
    <style>
        /* Your existing CSS styles */
        .report-header {
            background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #1976d2;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 8px;
            margin-top: 30px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .result-table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #ddd;
        }
        .result-table thead {
            background-color: #1976d2;
            color: white;
        }
        .result-table th, .result-table td {
            border: 1px solid #ddd;
            padding: 12px 15px;
        }
        .result-table th {
            font-weight: 500;
            border-bottom: 2px solid #ddd;
        }
        .normal-value {
            color: #4caf50;
            font-weight: 500;
        }
        .abnormal-value {
            color: #f44336;
            font-weight: 500;
        }
        .patient-info-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #1976d2;
            border: 1px solid #ddd;
        }
        .clinic-info {
            background: #f5f9ff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 40px;
            border-top: 2px solid #e0e0e0;
            border: 1px solid #ddd;
        }
        .highlight-box {
            background: #f5f9ff;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #1976d2;
            border: 1px solid #ddd;
        }
        .organ-healthy {
            color: #4caf50;
            font-weight: 500;
        }
        .organ-unhealthy {
            color: #f44336;
            font-weight: 500;
        }
        .alert {
            border: 1px solid transparent;
        }
        .alert-warning {
            border-color: #faebcc;
        }
    </style>
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
            <div class="col-md-12">
                <div class="report-header text-center">
                    <h2><i class="zmdi zmdi-file-text"></i> Online Diagnostic Lab</h2>
                    <h3>Laboratory Test Report</h3>
                    <p>Report Number: <?= htmlspecialchars($report['report_number']) ?></p>
                </div>
                
                <div class="widget">
                    <div class="widget-body">
                        <div class="patient-info-card">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong><i class="zmdi zmdi-account"></i> Patient Name:</strong> 
                                       <span class="pull-right"><?= htmlspecialchars($report['first_name'] . ' ' . $report['last_name']) ?></span>
                                    </p>
                                    <p><strong><i class="zmdi zmdi-pin"></i> Appointment Number:</strong> 
                                       <span class="pull-right"><?= htmlspecialchars($report['appointment_number'] ?? 'N/A') ?></span>
                                    </p>
                                    <p><strong><i class="zmdi zmdi-calendar"></i> Age:</strong> 
                                       <span class="pull-right"><?= $age ?> years</span>
                                    </p>
                                    <p><strong><i class="zmdi zmdi-phone"></i> Phone Number:</strong> 
                                       <span class="pull-right"><?= htmlspecialchars($report['phone']) ?></span>
                                    </p>
                                    <p><strong><i class="zmdi zmdi-email"></i> Email:</strong> 
                                       <span class="pull-right"><?= htmlspecialchars($report['email']) ?></span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong><i class="zmdi zmdi-male-female"></i> Gender:</strong> 
                                       <span class="pull-right"><?= htmlspecialchars($report['gender']) ?></span>
                                    </p>
                                    <p><strong><i class="zmdi zmdi-pin"></i> Address:</strong> 
                                       <span class="pull-right"><?= htmlspecialchars($report['address']) ?></span>
                                    </p>
                                    <p><strong><i class="zmdi zmdi-hospital"></i> Clinic:</strong> 
                                       <span class="pull-right"><?= htmlspecialchars($report['name']) ?></span>
                                    </p>
                                    <p><strong><i class="zmdi zmdi-time"></i> Report Date:</strong> 
                                       <span class="pull-right"><?= date('d M Y', strtotime($report['report_date'])) ?></span>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <p><strong><i class="zmdi zmdi-stethoscope"></i> Requesting Doctor:</strong> Dr. <?= htmlspecialchars($report['doctor_name']) ?></p>
                                    <p><strong><i class="zmdi zmdi-phone"></i> Doctor's Phone:</strong> <?= htmlspecialchars($report['doctor_phone']) ?></p>
                                    <p><strong><i class="zmdi zmdi-email"></i> Doctor's Email:</strong> <?= htmlspecialchars($report['doctor_email']) ?></p>
                                    <p><strong><i class="zmdi zmdi-label"></i> Suspected Conditions:</strong> <?= !empty($report['suspected_conditions']) ? htmlspecialchars($report['suspected_conditions']) : 'None reported' ?></p>
                                    <p><strong><i class="zmdi zmdi-info"></i> Existing Conditions:</strong> <?= !empty($report['existing_conditions']) ? htmlspecialchars($report['existing_conditions']) : 'None reported' ?></p>
                                </div>
                            </div>
                        </div>

                        <?php if ($blood_tests): ?>
                        <h4 class="section-title"><i class="zmdi zmdi-assignment"></i> Complete Blood Count (CBC)</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered result-table">
                                <thead>
                                    <tr>
                                        <th width="40%">Category</th>
                                        <th width="30%">Result</th>
                                        <th width="30%">Normal Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>White Blood Cells (WBC)</td>
                                        <td class="<?= isNormal($blood_tests['wbc'], 4500, 11000) ?>">
                                            <?= $blood_tests['wbc'] ? number_format($blood_tests['wbc']) . ' /μL' : 'N/A' ?>
                                        </td>
                                        <td>4,500 - 11,000 /μL</td>
                                    </tr>
                                    <tr>
                                        <td>Red Blood Cells (RBC)</td>
                                        <td class="<?= isNormal($blood_tests['rbc'], 4.0, 5.4) ?>">
                                            <?= $blood_tests['rbc'] ? number_format($blood_tests['rbc'], 1) . ' million /μL' : 'N/A' ?>
                                        </td>
                                        <td>4.0 - 5.4 million /μL</td>
                                    </tr>
                                    <tr>
                                        <td>Hemoglobin (Hb)</td>
                                        <td class="<?= isNormal($blood_tests['hemoglobin'], 12.0, 15.5) ?>">
                                            <?= $blood_tests['hemoglobin'] ? number_format($blood_tests['hemoglobin'], 1) . ' g/dL' : 'N/A' ?>
                                        </td>
                                        <td>12.0 - 15.5 g/dL</td>
                                    </tr>
                                    <tr>
                                        <td>Hematocrit (Hct)</td>
                                        <td class="<?= isNormal($blood_tests['hematocrit'], 36, 46) ?>">
                                            <?= $blood_tests['hematocrit'] ? number_format($blood_tests['hematocrit'], 1) . '%' : 'N/A' ?>
                                        </td>
                                        <td>36 - 46%</td>
                                    </tr>
                                    <tr>
                                        <td>Platelets</td>
                                        <td class="<?= isNormal($blood_tests['platelets'], 150000, 450000) ?>">
                                            <?= $blood_tests['platelets'] ? number_format($blood_tests['platelets']) . ' /μL' : 'N/A' ?>
                                        </td>
                                        <td>150,000 - 450,000 /μL</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h4 class="section-title"><i class="zmdi zmdi-assignment"></i> Blood Chemistry Panel</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered result-table">
                                <thead>
                                    <tr>
                                        <th width="40%">Category</th>
                                        <th width="30%">Result</th>
                                        <th width="30%">Normal Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Glucose</td>
                                        <td class="<?= isNormal($blood_tests['glucose'], 70, 99) ?>">
                                            <?= $blood_tests['glucose'] ? number_format($blood_tests['glucose'], 1) . ' mg/dL' : 'N/A' ?>
                                        </td>
                                        <td>70 - 99 mg/dL</td>
                                    </tr>
                                    <tr>
                                        <td>Sodium</td>
                                        <td class="<?= isNormal($blood_tests['sodium'], 135, 145) ?>">
                                            <?= $blood_tests['sodium'] ? number_format($blood_tests['sodium'], 1) . ' mEq/L' : 'N/A' ?>
                                        </td>
                                        <td>135 - 145 mEq/L</td>
                                    </tr>
                                    <tr>
                                        <td>Potassium</td>
                                        <td class="<?= isNormal($blood_tests['potassium'], 3.5, 5.1) ?>">
                                            <?= $blood_tests['potassium'] ? number_format($blood_tests['potassium'], 1) . ' mEq/L' : 'N/A' ?>
                                        </td>
                                        <td>3.5 - 5.1 mEq/L</td>
                                    </tr>
                                    <tr>
                                        <td>Chloride</td>
                                        <td class="<?= isNormal($blood_tests['chloride'], 98, 107) ?>">
                                            <?= $blood_tests['chloride'] ? number_format($blood_tests['chloride'], 1) . ' mEq/L' : 'N/A' ?>
                                        </td>
                                        <td>98 - 107 mEq/L</td>
                                    </tr>
                                    <tr>
                                        <td>AST (Aspartate Aminotransferase)</td>
                                        <td class="<?= isNormal($blood_tests['ast'], 10, 35) ?>">
                                            <?= $blood_tests['ast'] ? number_format($blood_tests['ast'], 1) . ' U/L' : 'N/A' ?>
                                        </td>
                                        <td>10 - 35 U/L</td>
                                    </tr>
                                    <tr>
                                        <td>ALT (Alanine Aminotransferase)</td>
                                        <td class="<?= isNormal($blood_tests['alt'], 7, 35) ?>">
                                            <?= $blood_tests['alt'] ? number_format($blood_tests['alt'], 1) . ' U/L' : 'N/A' ?>
                                        </td>
                                        <td>7 - 35 U/L</td>
                                    </tr>
                                    <tr>
                                        <td>BUN (Blood Urea Nitrogen)</td>
                                        <td class="<?= isNormal($blood_tests['bun'], 7, 20) ?>">
                                            <?= $blood_tests['bun'] ? number_format($blood_tests['bun'], 1) . ' mg/dL' : 'N/A' ?>
                                        </td>
                                        <td>7 - 20 mg/dL</td>
                                    </tr>
                                    <tr>
                                        <td>Creatinine</td>
                                        <td class="<?= isNormal($blood_tests['creatinine'], 0.6, 1.1) ?>">
                                            <?= $blood_tests['creatinine'] ? number_format($blood_tests['creatinine'], 2) . ' mg/dL' : 'N/A' ?>
                                        </td>
                                        <td>0.6 - 1.1 mg/dL</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h4 class="section-title"><i class="zmdi zmdi-assignment"></i> Lipid Profile</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered result-table">
                                <thead>
                                    <tr>
                                        <th width="40%">Category</th>
                                        <th width="30%">Result</th>
                                        <th width="30%">Normal Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Total Cholesterol</td>
                                        <td class="<?= isNormal($blood_tests['total_cholesterol'], 0, 200) ?>">
                                            <?= $blood_tests['total_cholesterol'] ? number_format($blood_tests['total_cholesterol'], 1) . ' mg/dL' : 'N/A' ?>
                                        </td>
                                        <td>&lt; 200 mg/dL</td>
                                    </tr>
                                    <tr>
                                        <td>HDL (High-Density Lipoprotein)</td>
                                        <td class="<?= isNormal($blood_tests['hdl'], 40, 999) ?>">
                                            <?= $blood_tests['hdl'] ? number_format($blood_tests['hdl'], 1) . ' mg/dL' : 'N/A' ?>
                                        </td>
                                        <td>&gt; 40 mg/dL</td>
                                    </tr>
                                    <tr>
                                        <td>LDL (Low-Density Lipoprotein)</td>
                                        <td class="<?= isNormal($blood_tests['ldl'], 0, 130) ?>">
                                            <?= $blood_tests['ldl'] ? number_format($blood_tests['ldl'], 1) . ' mg/dL' : 'N/A' ?>
                                        </td>
                                        <td>&lt; 130 mg/dL</td>
                                    </tr>
                                    <tr>
                                        <td>Triglycerides</td>
                                        <td class="<?= isNormal($blood_tests['triglycerides'], 0, 150) ?>">
                                            <?= $blood_tests['triglycerides'] ? number_format($blood_tests['triglycerides'], 1) . ' mg/dL' : 'N/A' ?>
                                        </td>
                                        <td>&lt; 150 mg/dL</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <?php if ($imaging): ?>
                        <h4 class="section-title"><i class="zmdi zmdi-assignment"></i> Abdominal Ultrasound Results</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered result-table">
                                <thead>
                                    <tr>
                                        <th width="30%">Organ</th>
                                        <th width="70%">Observations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Liver</td>
                                        <td>
                                            <span class="<?= getOrganStatus($imaging['liver']) ?>">
                                                <i class="zmdi zmdi-check-circle"></i> 
                                                <?= empty($imaging['liver']) ? 'Not Examined' : (stripos($imaging['liver'], 'normal') !== false ? 'Normal' : 'Abnormal') ?>
                                            </span> 
                                            <?= htmlspecialchars($imaging['liver']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Gallbladder</td>
                                        <td>
                                            <span class="<?= getOrganStatus($imaging['gallbladder']) ?>">
                                                <i class="zmdi zmdi-check-circle"></i> 
                                                <?= empty($imaging['gallbladder']) ? 'Not Examined' : (stripos($imaging['gallbladder'], 'normal') !== false ? 'Normal' : 'Abnormal') ?>
                                            </span> 
                                            <?= htmlspecialchars($imaging['gallbladder']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Spleen</td>
                                        <td>
                                            <span class="<?= getOrganStatus($imaging['spleen']) ?>">
                                                <i class="zmdi zmdi-check-circle"></i> 
                                                <?= empty($imaging['spleen']) ? 'Not Examined' : (stripos($imaging['spleen'], 'normal') !== false ? 'Normal' : 'Abnormal') ?>
                                            </span> 
                                            <?= htmlspecialchars($imaging['spleen']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Kidneys</td>
                                        <td>
                                            <span class="<?= getOrganStatus($imaging['kidneys']) ?>">
                                                <i class="zmdi zmdi-check-circle"></i> 
                                                <?= empty($imaging['kidneys']) ? 'Not Examined' : (stripos($imaging['kidneys'], 'normal') !== false ? 'Normal' : 'Abnormal') ?>
                                            </span> 
                                            <?= htmlspecialchars($imaging['kidneys']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Bladder</td>
                                        <td>
                                            <span class="<?= getOrganStatus($imaging['bladder']) ?>">
                                                <i class="zmdi zmdi-check-circle"></i> 
                                                <?= empty($imaging['bladder']) ? 'Not Examined' : (stripos($imaging['bladder'], 'normal') !== false ? 'Normal' : 'Abnormal') ?>
                                            </span> 
                                            <?= htmlspecialchars($imaging['bladder']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Stomach</td>
                                        <td>
                                            <span class="<?= getOrganStatus($imaging['stomach']) ?>">
                                                <i class="zmdi zmdi-check-circle"></i> 
                                                <?= empty($imaging['stomach']) ? 'Not Examined' : (stripos($imaging['stomach'], 'normal') !== false ? 'Normal' : 'Abnormal') ?>
                                            </span> 
                                            <?= htmlspecialchars($imaging['stomach']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Intestinal Loops</td>
                                        <td>
                                            <span class="<?= getOrganStatus($imaging['intestinal_loops']) ?>">
                                                <i class="zmdi zmdi-check-circle"></i> 
                                                <?= empty($imaging['intestinal_loops']) ? 'Not Examined' : (stripos($imaging['intestinal_loops'], 'normal') !== false ? 'Normal' : 'Abnormal') ?>
                                            </span> 
                                            <?= htmlspecialchars($imaging['intestinal_loops']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Adrenals</td>
                                        <td>
                                            <span class="<?= getOrganStatus($imaging['adrenals']) ?>">
                                                <i class="zmdi zmdi-check-circle"></i> 
                                                <?= empty($imaging['adrenals']) ? 'Not Examined' : (stripos($imaging['adrenals'], 'normal') !== false ? 'Normal' : 'Abnormal') ?>
                                            </span> 
                                            <?= htmlspecialchars($imaging['adrenals']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Others</td>
                                        <td>
                                            <span class="<?= getOrganStatus($imaging['others']) ?>">
                                                <i class="zmdi zmdi-check-circle"></i> 
                                                <?= empty($imaging['others']) ? 'Not Examined' : (stripos($imaging['others'], 'normal') !== false ? 'Normal' : 'Abnormal') ?>
                                            </span> 
                                            <?= htmlspecialchars($imaging['others']) ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <div class="highlight-box">
                            <h5><i class="zmdi zmdi-assignment-check"></i> General Impression</h5>
                            <p class="lead"><?= !empty($report['general_impression']) ? htmlspecialchars($report['general_impression']) : 'All test results are within normal physiological ranges.' ?></p>
                        </div>

                        <div class="alert alert-warning">
                            <h5><i class="zmdi zmdi-alert-circle"></i> Important Note</h5>
                            <p>The isolated analysis of this exam has no diagnostic value unless evaluated in conjunction with clinical, epidemiological data, and other complementary exams.</p>
                        </div>

                        <div class="clinic-info text-center">
                            <h4><i class="zmdi zmdi-hospital"></i> <?= htmlspecialchars($report['name']) ?></h4>
                            <p><i class="zmdi zmdi-pin"></i> <?= htmlspecialchars($report['address']) ?></p>
                            <p><i class="zmdi zmdi-phone"></i> <?= htmlspecialchars($report['phone']) ?> | <i class="zmdi zmdi-email"></i> <?= htmlspecialchars($report['email']) ?></p>
                            <p class="text-muted">Report generated on: <?= date('F j, Y \a\t H:i', strtotime($report['report_date'])) ?></p>
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