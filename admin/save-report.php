<?php
// Start session and include database connection
session_start();
include('includes/dbconnection.php');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['odlmsaid']) || empty($_SESSION['odlmsaid'])) {
    header('location:logout.php');
    exit();
}

// Getting patient data
$patientData = [];
if (isset($_GET['appointment_id']) && !empty($_GET['appointment_id'])) {
    $appointmentId = intval($_GET['appointment_id']);
    $query = "SELECT * FROM tblappointment WHERE ID = ?";
    $stmt = $dbh->prepare($query);
    $stmt->execute([$appointmentId]);
    $patientData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Also check if patient already exists
    if (!empty($patientData['MobileNumber'])) {
        $checkPatient = $dbh->prepare("SELECT * FROM patients WHERE phone = ?");
        $checkPatient->execute([$patientData['MobileNumber']]);
        $existingPatient = $checkPatient->fetch(PDO::FETCH_ASSOC);
        if ($existingPatient) {
            $patientData = array_merge($patientData, $existingPatient);
            $patientData['patient_exists'] = true;
        }
    }
}

// Fetch clinic data
$clinicData = [];
$clinicQuery = "SELECT * FROM clinics LIMIT 1";
$clinicStmt = $dbh->prepare($clinicQuery);
$clinicStmt->execute();
$clinicData = $clinicStmt->fetch(PDO::FETCH_ASSOC);

// Function to generate unique report number
function generateReportNumber() {
    return 'LAB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to validate phone number
function validatePhone($phone) {
    return preg_match('/^[\d\s\-\(\)]{8,20}$/', $phone);
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate date not in future
function validateDateNotFuture($date) {
    try {
        $inputDate = new DateTime($date);
        $currentDate = new DateTime();
        return $inputDate <= $currentDate;
    } catch (Exception $e) {
        return false;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Validate required fields
        $requiredFields = [
            'patient_full_name', 'patient_dob',
            'patient_gender', 'patient_phone', 'clinic_name',
            'clinic_phone', 'clinic_address', 'collection_date'
        ];
        
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $missingFields[] = str_replace('_', ' ', $field);
            }
        }
        
        if (!empty($missingFields)) {
            throw new Exception("These fields are required: " . implode(', ', $missingFields));
        }
        
        // Validate data formats
        if (!validatePhone($_POST['patient_phone'])) {
            throw new Exception("Invalid patient phone number format");
        }
        
        if (!validatePhone($_POST['clinic_phone'])) {
            throw new Exception("Invalid clinic phone number format");
        }
        
        if (!empty($_POST['patient_email']) && !validateEmail($_POST['patient_email'])) {
            throw new Exception("Invalid patient email address");
        }
        
        if (!empty($_POST['clinic_email']) && !validateEmail($_POST['clinic_email'])) {
            throw new Exception("Invalid clinic email address");
        }
        
        if (!validateDateNotFuture($_POST['collection_date'])) {
            throw new Exception("Collection date cannot be in the future");
        }
        
        // Validate numeric test results
        $numericFields = [
            'wbc', 'rbc', 'hemoglobin', 'hematocrit', 'platelets',
            'glucose', 'sodium', 'potassium', 'chloride', 'ast', 'alt',
            'bun', 'creatinine', 'total_cholesterol', 'hdl', 'ldl', 'triglycerides'
        ];
        
        foreach ($numericFields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '' && !is_numeric($_POST[$field])) {
                throw new Exception("Invalid value for " . str_replace('_', ' ', $field) . " - must be numeric");
            }
        }

        // Begin transaction
        $dbh->beginTransaction();

        // 1. Handle patient data
        if (!empty($patientData['patient_exists'])) {
            // Update existing patient
            $patientStmt = $dbh->prepare("
                UPDATE patients SET
                    full_name = ?,
                    date_of_birth = ?,
                    gender = ?,
                    address = ?,
                    phone = ?,
                    email = ?,
                    updated_at = CURRENT_TIMESTAMP()
                WHERE patient_id = ?
            ");
            
            $patientStmt->execute([
                sanitizeInput($_POST['patient_full_name']),
                $_POST['patient_dob'],
                $_POST['patient_gender'],
                sanitizeInput($_POST['patient_address']),
                sanitizeInput($_POST['patient_phone']),
                !empty($_POST['patient_email']) ? filter_var($_POST['patient_email'], FILTER_SANITIZE_EMAIL) : null,
                $patientData['patient_id']
            ]);
            
            $patientId = $patientData['patient_id'];
        } else {
            // Insert new patient
            $patientStmt = $dbh->prepare("
                INSERT INTO patients (
                    full_name, date_of_birth, gender, 
                    address, phone, email, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP())
            ");
            
            $patientStmt->execute([
                sanitizeInput($_POST['patient_full_name']),
                $_POST['patient_dob'],
                $_POST['patient_gender'],
                sanitizeInput($_POST['patient_address']),
                sanitizeInput($_POST['patient_phone']),
                !empty($_POST['patient_email']) ? filter_var($_POST['patient_email'], FILTER_SANITIZE_EMAIL) : null
            ]);
            
            $patientId = $dbh->lastInsertId();
        }

        // 2. Save Clinic Information
        if (!empty($clinicData['clinic_id'])) {
            $clinicId = $clinicData['clinic_id'];
        } else {
            // Fallback - create clinic if somehow missing (shouldn't happen)
            $clinicStmt = $dbh->prepare("
                INSERT INTO clinics (name, address, phone, email) 
                VALUES (?, ?, ?, ?)
            ");
            $clinicStmt->execute([
                $_POST['clinic_name'],
                $_POST['clinic_address'],
                $_POST['clinic_phone'],
                !empty($_POST['clinic_email']) ? $_POST['clinic_email'] : null
            ]);
            $clinicId = $dbh->lastInsertId();
        }

        // 3. Save Lab Report
        $reportStmt = $dbh->prepare("
            INSERT INTO lab_reports (
                patient_id, doctor_id, clinic_id, report_number,
                collection_date, report_date, existing_conditions,
                suspected_conditions, general_impression, notes, status,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP())
        ");
        
        $reportNumber = generateReportNumber();
        $reportDate = date('Y-m-d');
        
        $reportStmt->execute([
            $patientId,
            $_SESSION['odlmsaid'],
            $clinicId,
            $reportNumber,
            $_POST['collection_date'],
            $reportDate,
            !empty($_POST['existing_conditions']) ? sanitizeInput($_POST['existing_conditions']) : null,
            !empty($_POST['suspected_conditions']) ? sanitizeInput($_POST['suspected_conditions']) : null,
            !empty($_POST['general_impression']) ? sanitizeInput($_POST['general_impression']) : null,
            !empty($_POST['notes']) ? sanitizeInput($_POST['notes']) : null,
            'Completed'
        ]);
        
        $reportId = $dbh->lastInsertId();

        // 4. Save Blood Test Results
        $bloodTestValues = [
            'wbc', 'rbc', 'hemoglobin', 'hematocrit', 'platelets',
            'glucose', 'sodium', 'potassium', 'chloride', 'ast', 'alt',
            'bun', 'creatinine', 'total_cholesterol', 'hdl', 'ldl', 'triglycerides'
        ];
        
        $hasBloodTestData = false;
        foreach ($bloodTestValues as $field) {
            if (!empty($_POST[$field])) {
                $hasBloodTestData = true;
                break;
            }
        }
        
        if ($hasBloodTestData) {
            $bloodTestStmt = $dbh->prepare("
                INSERT INTO blood_tests (
                    report_id, test_type, wbc, rbc, hemoglobin, hematocrit,
                    platelets, glucose, sodium, potassium, chloride, ast, alt,
                    bun, creatinine, total_cholesterol, hdl, ldl, triglycerides,
                    created_at
                ) VALUES (?, 'CBC', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP())
            ");
            
            $bloodTestStmt->execute([
                $reportId,
                !empty($_POST['wbc']) ? floatval($_POST['wbc']) : null,
                !empty($_POST['rbc']) ? floatval($_POST['rbc']) : null,
                !empty($_POST['hemoglobin']) ? floatval($_POST['hemoglobin']) : null,
                !empty($_POST['hematocrit']) ? floatval($_POST['hematocrit']) : null,
                !empty($_POST['platelets']) ? floatval($_POST['platelets']) : null,
                !empty($_POST['glucose']) ? floatval($_POST['glucose']) : null,
                !empty($_POST['sodium']) ? floatval($_POST['sodium']) : null,
                !empty($_POST['potassium']) ? floatval($_POST['potassium']) : null,
                !empty($_POST['chloride']) ? floatval($_POST['chloride']) : null,
                !empty($_POST['ast']) ? floatval($_POST['ast']) : null,
                !empty($_POST['alt']) ? floatval($_POST['alt']) : null,
                !empty($_POST['bun']) ? floatval($_POST['bun']) : null,
                !empty($_POST['creatinine']) ? floatval($_POST['creatinine']) : null,
                !empty($_POST['total_cholesterol']) ? floatval($_POST['total_cholesterol']) : null,
                !empty($_POST['hdl']) ? floatval($_POST['hdl']) : null,
                !empty($_POST['ldl']) ? floatval($_POST['ldl']) : null,
                !empty($_POST['triglycerides']) ? floatval($_POST['triglycerides']) : null
            ]);
        }

        // 5. Save Imaging Results
        $imagingFields = [
            'liver', 'gallbladder', 'spleen', 'kidneys', 'bladder',
            'stomach', 'intestinal_loops', 'adrenals', 'others'
        ];
        
        $hasImagingData = false;
        foreach ($imagingFields as $field) {
            if (!empty($_POST[$field])) {
                $hasImagingData = true;
                break;
            }
        }
        
        if ($hasImagingData) {
            $imagingStmt = $dbh->prepare("
                INSERT INTO imaging_results (
                    report_id, imaging_type, liver, gallbladder, spleen,
                    kidneys, bladder, stomach, intestinal_loops, adrenals, others,
                    created_at
                ) VALUES (?, 'Ultrasound', ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP())
            ");
            
            $imagingStmt->execute([
                $reportId,
                !empty($_POST['liver']) ? sanitizeInput($_POST['liver']) : null,
                !empty($_POST['gallbladder']) ? sanitizeInput($_POST['gallbladder']) : null,
                !empty($_POST['spleen']) ? sanitizeInput($_POST['spleen']) : null,
                !empty($_POST['kidneys']) ? sanitizeInput($_POST['kidneys']) : null,
                !empty($_POST['bladder']) ? sanitizeInput($_POST['bladder']) : null,
                !empty($_POST['stomach']) ? sanitizeInput($_POST['stomach']) : null,
                !empty($_POST['intestinal_loops']) ? sanitizeInput($_POST['intestinal_loops']) : null,
                !empty($_POST['adrenals']) ? sanitizeInput($_POST['adrenals']) : null,
                !empty($_POST['others']) ? sanitizeInput($_POST['others']) : null
            ]);
        }

        // Commit transaction
        $dbh->commit();

        // Success response
        $response = [
            'success' => true,
            'message' => 'Lab report submitted successfully!',
            'report_number' => $reportNumber,
            'report_id' => $reportId
        ];
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }
        
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
        
        error_log("Database Error: " . $e->getMessage());
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        
        error_log("Validation Error: " . $e->getMessage());
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Online Diagnostic Lab || Lab Report Submission</title>
    <!-- Include all requested libraries -->
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.css">
    <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
    <link rel="stylesheet" href="libs/bower/fullcalendar/dist/fullcalendar.min.css">
    <link rel="stylesheet" href="libs/bower/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">
    <style>
        /* Validation styles */
        .is-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .is-invalid:focus {
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .is-invalid ~ .invalid-feedback {
            display: block;
        }

        /* Style for required field labels */
        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        /* Your existing custom styles */
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
        .patient-info-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #1976d2;
        }
        .test-group {
            background: #f5f9ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #1976d2;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.25rem rgba(25, 118, 210, 0.25);
        }
        .btn-submit {
            background-color: #1976d2;
            color: white;
            border: none;
            padding: 10px 25px;
            font-weight: 500;
        }
        .btn-submit:hover {
            background-color: #1565c0;
        }
        .btn-submit:disabled {
            background-color: #90caf9;
            cursor: not-allowed;
        }
    </style>
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
                    <h2><i class="zmdi zmdi-file-text"></i> Lab Report Submission</h2>
                    <p class="mb-0">Complete all required fields to submit a new lab report</p>
                </div>
                
                <div class="widget">
                    <div class="widget-body">
                        <form id="labReportForm" class="needs-validation" novalidate>
                            <!-- Patient Information -->
                            <h3 class="section-title"><i class="zmdi zmdi-account"></i> Patient Information</h3>
                            <div class="patient-info-card">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="patient_full_name" class="form-label required-field">Full Name</label>
                                        <input type="text" class="form-control" id="patient_full_name" name="patient_full_name" 
                                            value="<?= !empty($patientData['PatientName']) ? htmlspecialchars($patientData['PatientName']) : '' ?>" required readonly>
                                        <div class="invalid-feedback">Please provide the patient's full name</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="patient_dob" class="form-label required-field">Date of Birth</label>
                                        <input type="date" class="form-control" id="patient_dob" name="patient_dob" 
                                            value="<?= !empty($patientData['DOB']) ? htmlspecialchars($patientData['DOB']) : '' ?>" required readonly>
                                        <div class="invalid-feedback">Please select the patient's date of birth</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="patient_gender" class="form-label required-field">Gender</label>
                                        <select class="form-select" id="patient_gender" name="patient_gender" required>
                                            <option value="">Select...</option>
                                            <option value="Male" <?= (!empty($patientData['Gender']) && $patientData['Gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                                            <option value="Female" <?= (!empty($patientData['Gender']) && $patientData['Gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                                            <option value="Other" <?= (!empty($patientData['Gender']) && $patientData['Gender'] == 'Other') ? 'selected' : '' ?>>Other</option>
                                        </select>
                                        <div class="invalid-feedback">Please select the patient's gender</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="patient_phone" class="form-label required-field">Phone</label>
                                        <input type="tel" class="form-control" id="patient_phone" name="patient_phone" 
                                            value="<?= !empty($patientData['MobileNumber']) ? htmlspecialchars($patientData['MobileNumber']) : '' ?>"
                                            pattern="[\d\s\-\(\)]{8,20}" required readonly>
                                        <div class="invalid-feedback">Please provide a valid phone number (8-20 digits)</div>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="patient_address" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="patient_address" name="patient_address"
                                            value="<?= !empty($patientData['address']) ? htmlspecialchars($patientData['address']) : '' ?>" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="patient_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="patient_email" name="patient_email"
                                            value="<?= !empty($patientData['Email']) ? htmlspecialchars($patientData['Email']) : '' ?>"
                                            pattern="[^@\s]+@[^@\s]+\.[^@\s]+" readonly>
                                        <div class="invalid-feedback">Please provide a valid email address</div>
                                    </div>
                                </div>
                            </div>

                           <!-- Clinic Information -->
                            <h3 class="section-title"><i class="zmdi zmdi-hospital"></i> Clinic Information</h3>
                            <div class="patient-info-card">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="clinic_name" class="form-label required-field">Clinic Name</label>
                                        <input type="text" class="form-control" id="clinic_name" name="clinic_name" 
                                            value="<?= !empty($clinicData['name']) ? htmlspecialchars($clinicData['name']) : 'Our Clinic' ?>" 
                                            required readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="clinic_phone" class="form-label required-field">Phone</label>
                                        <input type="tel" class="form-control" id="clinic_phone" name="clinic_phone"
                                            value="<?= !empty($clinicData['phone']) ? htmlspecialchars($clinicData['phone']) : '1234567890' ?>"
                                            pattern="[\d\s\-\(\)]{8,20}" required readonly>
                                    </div>
                                    <div class="col-12">
                                        <label for="clinic_address" class="form-label required-field">Address</label>
                                        <input type="text" class="form-control" id="clinic_address" name="clinic_address" 
                                            value="<?= !empty($clinicData['address']) ? htmlspecialchars($clinicData['address']) : '123 Main Street, City' ?>" 
                                            required readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="clinic_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="clinic_email" name="clinic_email"
                                            value="<?= !empty($clinicData['email']) ? htmlspecialchars($clinicData['email']) : 'contact@ourclinic.com' ?>"
                                            pattern="[^@\s]+@[^@\s]+\.[^@\s]+" readonly>
                                    </div>
                                </div>
                            </div>
                            <!-- Report Information -->
                            <h3 class="section-title"><i class="zmdi zmdi-assignment"></i> Report Information</h3>
                            <div class="patient-info-card">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="collection_date" class="form-label required-field">Collection Date</label>
                                        <input type="date" class="form-control" id="collection_date" name="collection_date" required>
                                        <div class="invalid-feedback">Please select the collection date</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="report_date" class="form-label">Report Date</label>
                                        <input type="date" class="form-control" id="report_date" name="report_date" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="existing_conditions" class="form-label">Existing Conditions</label>
                                        <input type="text" class="form-control" id="existing_conditions" name="existing_conditions">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="suspected_conditions" class="form-label">Suspected Conditions</label>
                                        <input type="text" class="form-control" id="suspected_conditions" name="suspected_conditions">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="general_impression" class="form-label">General Impression</label>
                                        <input type="text" class="form-control" id="general_impression" name="general_impression">
                                    </div>
                                    <div class="col-12">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Blood Test Results -->
                            <h3 class="section-title"><i class="zmdi zmdi-eyedropper"></i> Blood Test Results</h3>
                            
                            <div class="test-group">
                                <h5><i class="zmdi zmdi-assignment"></i> Complete Blood Count (CBC)</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="wbc" class="form-label">White Blood Cells (WBC) (/μL)</label>
                                        <input type="number" step="0.01" class="form-control" id="wbc" name="wbc">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="rbc" class="form-label">Red Blood Cells (RBC) (million/μL)</label>
                                        <input type="number" step="0.01" class="form-control" id="rbc" name="rbc">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="hemoglobin" class="form-label">Hemoglobin (Hb) (g/dL)</label>
                                        <input type="number" step="0.1" class="form-control" id="hemoglobin" name="hemoglobin">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="hematocrit" class="form-label">Hematocrit (Hct) (%)</label>
                                        <input type="number" step="0.1" class="form-control" id="hematocrit" name="hematocrit">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="platelets" class="form-label">Platelets (/μL)</label>
                                        <input type="number" step="0.01" class="form-control" id="platelets" name="platelets">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="test-group">
                                <h5><i class="zmdi zmdi-assignment"></i> Blood Chemistry</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="glucose" class="form-label">Glucose (mg/dL)</label>
                                        <input type="number" step="0.1" class="form-control" id="glucose" name="glucose">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="sodium" class="form-label">Sodium (mEq/L)</label>
                                        <input type="number" step="0.1" class="form-control" id="sodium" name="sodium">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="potassium" class="form-label">Potassium (mEq/L)</label>
                                        <input type="number" step="0.1" class="form-control" id="potassium" name="potassium">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="chloride" class="form-label">Chloride (mEq/L)</label>
                                        <input type="number" step="0.1" class="form-control" id="chloride" name="chloride">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="ast" class="form-label">AST (U/L)</label>
                                        <input type="number" step="0.1" class="form-control" id="ast" name="ast">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="alt" class="form-label">ALT (U/L)</label>
                                        <input type="number" step="0.1" class="form-control" id="alt" name="alt">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bun" class="form-label">BUN (mg/dL)</label>
                                        <input type="number" step="0.1" class="form-control" id="bun" name="bun">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="creatinine" class="form-label">Creatinine (mg/dL)</label>
                                        <input type="number" step="0.01" class="form-control" id="creatinine" name="creatinine">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="test-group">
                                <h5><i class="zmdi zmdi-assignment"></i> Lipid Profile</h5>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="total_cholesterol" class="form-label">Total Cholesterol (mg/dL)</label>
                                        <input type="number" step="0.1" class="form-control" id="total_cholesterol" name="total_cholesterol">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="hdl" class="form-label">HDL (mg/dL)</label>
                                        <input type="number" step="0.1" class="form-control" id="hdl" name="hdl">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="ldl" class="form-label">LDL (mg/dL)</label>
                                        <input type="number" step="0.1" class="form-control" id="ldl" name="ldl">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="triglycerides" class="form-label">Triglycerides (mg/dL)</label>
                                        <input type="number" step="0.1" class="form-control" id="triglycerides" name="triglycerides">
                                        <div class="invalid-feedback">Please enter a valid number</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Imaging Results -->
                            <h3 class="section-title"><i class="zmdi zmdi-assignment"></i> Imaging Results</h3>
                            <div class="patient-info-card">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="liver" class="form-label">Liver</label>
                                        <textarea class="form-control" id="liver" name="liver" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="gallbladder" class="form-label">Gallbladder</label>
                                        <textarea class="form-control" id="gallbladder" name="gallbladder" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="spleen" class="form-label">Spleen</label>
                                        <textarea class="form-control" id="spleen" name="spleen" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="kidneys" class="form-label">Kidneys</label>
                                        <textarea class="form-control" id="kidneys" name="kidneys" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="bladder" class="form-label">Bladder</label>
                                        <textarea class="form-control" id="bladder" name="bladder" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="stomach" class="form-label">Stomach</label>
                                        <textarea class="form-control" id="stomach" name="stomach" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="intestinal_loops" class="form-label">Intestinal Loops</label>
                                        <textarea class="form-control" id="intestinal_loops" name="intestinal_loops" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="adrenals" class="form-label">Adrenals</label>
                                        <textarea class="form-control" id="adrenals" name="adrenals" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="others" class="form-label">Others</label>
                                        <textarea class="form-control" id="others" name="others" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="text-end mt-4">
                                <button type="reset" class="btn btn-secondary me-2">
                                    <i class="zmdi zmdi-close-circle"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-submit" id="submitButton">
                                    <i class="zmdi zmdi-check-circle"></i> Submit Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
  </div>
  <?php include_once('includes/footer.php');?>
</main>
<?php include_once('includes/customizer.php');?>

<!-- Include all requested JS libraries -->
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Set current date as report date
    $('#report_date').val(new Date().toISOString().substr(0, 10));
    
    // Show patient data notification if available
    <?php if (!empty($patientData)): ?>
        Swal.fire({
            icon: 'info',
            title: 'Patient Data Loaded',
            text: 'Patient information has been pre-filled from the appointment record',
            timer: 3000,
            showConfirmButton: false
        });
        
        <?php if (!empty($patientData['patient_exists'])): ?>
            Swal.fire({
                icon: 'info',
                title: 'Existing Patient',
                text: 'This patient already exists in our database. The form will update their record.',
                timer: 4000
            });
        <?php endif; ?>
    <?php endif; ?>

    // Validate form on submit
    $('#labReportForm').submit(function(e) {
        e.preventDefault();
        
        // Reset validation
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
        
        // Validate required fields
        let isValid = true;
        const requiredFields = [
            'patient_full_name', 'patient_dob',
            'patient_gender', 'patient_phone', 'clinic_name',
            'clinic_phone', 'clinic_address', 'collection_date'
        ];
        
        requiredFields.forEach(fieldId => {
            const field = $('#' + fieldId);
            if (!field.val().trim()) {
                field.addClass('is-invalid');
                isValid = false;
            }
        });
        
        // Validate email formats if provided
        const emailFields = ['patient_email', 'clinic_email'];
        emailFields.forEach(fieldId => {
            const field = $('#' + fieldId);
            if (field.val().trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.val())) {
                field.addClass('is-invalid');
                isValid = false;
            }
        });
        
        // Validate phone formats
        const phoneFields = ['patient_phone', 'clinic_phone'];
        phoneFields.forEach(fieldId => {
            const field = $('#' + fieldId);
            if (field.val().trim() && !/^[\d\s\-\(\)]{8,20}$/.test(field.val())) {
                field.addClass('is-invalid');
                isValid = false;
            }
        });
        
        // Validate collection date not in future
        const collectionDate = new Date($('#collection_date').val());
        if (collectionDate > new Date()) {
            $('#collection_date').addClass('is-invalid');
            isValid = false;
        }
        
        // Validate numeric fields
        const numericFields = [
            'wbc', 'rbc', 'hemoglobin', 'hematocrit', 'platelets',
            'glucose', 'sodium', 'potassium', 'chloride', 'ast', 'alt',
            'bun', 'creatinine', 'total_cholesterol', 'hdl', 'ldl', 'triglycerides'
        ];
        
        numericFields.forEach(fieldId => {
            const field = $('#' + fieldId);
            if (field.val().trim() && isNaN(field.val())) {
                field.addClass('is-invalid');
                isValid = false;
            }
        });
        
        if (!isValid) {
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
            return;
        }
        
        // Disable submit button
        const submitButton = $('#submitButton');
        submitButton.html('<i class="zmdi zmdi-spinner zmdi-hc-spin"></i> Processing...').prop('disabled', true);
        
        // Submit form via AJAX
        $.ajax({
            url: 'save-report.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: `${response.message}<br><br>Report Number: <strong>${response.report_number}</strong>`,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Optionally redirect or reset form
                        window.location.href = 'view-reports.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while submitting the form. Please try again.',
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                submitButton.html('<i class="zmdi zmdi-check-circle"></i> Submit Report').prop('disabled', false);
            }
        });
    });
    
    // Real-time validation for fields
    $('#labReportForm input, #labReportForm select, #labReportForm textarea').on('blur', function() {
        const field = $(this);
        
        // Required field validation
        if (field.prop('required') && !field.val().trim()) {
            field.addClass('is-invalid');
            return;
        }
        
        // Email validation
        if (field.attr('type') === 'email' && field.val().trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.val())) {
            field.addClass('is-invalid');
            return;
        }
        
        // Phone validation
        if ((field.attr('id') === 'patient_phone' || field.attr('id') === 'clinic_phone') && 
            field.val().trim() && !/^[\d\s\-\(\)]{8,20}$/.test(field.val())) {
            field.addClass('is-invalid');
            return;
        }
        
        // Numeric validation
        const numericFields = [
            'wbc', 'rbc', 'hemoglobin', 'hematocrit', 'platelets',
            'glucose', 'sodium', 'potassium', 'chloride', 'ast', 'alt',
            'bun', 'creatinine', 'total_cholesterol', 'hdl', 'ldl', 'triglycerides'
        ];
        
        if (numericFields.includes(field.attr('id'))) {
            if (field.val().trim() && isNaN(field.val())) {
                field.addClass('is-invalid');
                return;
            }
        }
        
        // Date validation
        if (field.attr('id') === 'collection_date' && field.val()) {
            const inputDate = new Date(field.val());
            if (inputDate > new Date()) {
                field.addClass('is-invalid');
                return;
            }
        }
        
        // If all validations pass
        field.removeClass('is-invalid');
    });
});
</script>
</body>
</html>