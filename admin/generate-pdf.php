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

// Fetch report data (same as in the original script)
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Extend TCPDF to add custom header/footer
class LabReportPDF extends TCPDF {
    // Page header
    public function Header() {
        // Logo
        $image_file = 'assets/images/logo.png';
        $this->Image($image_file, 10, 10, 30, 23, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        // Set font
        $this->SetFont('helvetica', 'B', 16);
        
        // Title
        $this->Cell(0, 15, 'Online Diagnostic Lab', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Line break
        $this->Ln(10);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'Laboratory Test Report', 0, 1, 'C');
        
        // Line separator
        $this->Line(10, 35, $this->getPageWidth()-10, 35);
    }
    
    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        
        // Confidential notice
        $this->Ln(5);
        $this->Cell(0, 10, 'This document contains confidential patient information', 0, false, 'C');
    }
}

// Create new PDF document
$pdf = new LabReportPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Online Diagnostic Lab');
$pdf->SetTitle('Lab Report #' . $report_id);
$pdf->SetSubject('Laboratory Test Results');
$pdf->SetKeywords('Lab, Report, Medical');

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Fetch report data (same as in the original script)
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
    return ($value >= $min && $value <= $max) ? 'normal' : 'abnormal';
}

// Helper function to add test rows with formatting
function addTestRow($test, $value, $range, $min, $max) {
    $status = isNormal($value, $min, $max);
    $formattedValue = $value !== null ? number_format($value, $value < 1 ? 2 : ($value < 10 ? 1 : 0)) : 'N/A';
    
    $color = '';
    if ($status === 'normal') {
        $color = 'color:#4CAF50;';
    } elseif ($status === 'abnormal') {
        $color = 'color:#F44336;';
    }
    
    return "<tr>
        <td width=\"40%\">$test</td>
        <td width=\"30%\" style=\"$color\">$formattedValue</td>
        <td width=\"30%\">$range</td>
    </tr>";
}

// Helper function to add imaging rows with formatting
function addImagingRow($organ, $observation) {
    if (empty($observation)) {
        $observation = 'Not examined';
        $status = '';
    } else {
        $status = stripos($observation, 'normal') !== false ? 'normal' : 'abnormal';
    }
    
    $color = '';
    if ($status === 'normal') {
        $color = 'color:#4CAF50;';
    } elseif ($status === 'abnormal') {
        $color = 'color:#F44336;';
    }
    
    return "<tr>
        <td width=\"30%\">$organ</td>
        <td width=\"70%\" style=\"$color\">$observation</td>
    </tr>";
}

// Set font for main content
$pdf->SetFont('helvetica', '', 10);

// Report header
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Report Number: ' . $report['report_number'], 0, 1, 'C');
$pdf->Ln(5);

// Patient information
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Patient Information', 0, 1);
$pdf->SetFont('helvetica', '', 10);

$patient_info = <<<EOD
Name: {$report['first_name']} {$report['last_name']}
Age: $age years
Gender: {$report['gender']}
Phone: {$report['phone']}
Email: {$report['email']}
Address: {$report['address']}
EOD;

$pdf->MultiCell(0, 10, $patient_info, 0, 'L');
$pdf->Ln(5);

// Doctor and clinic information
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Requesting Physician', 0, 1);
$pdf->SetFont('helvetica', '', 10);

$doctor_info = <<<EOD
Dr. {$report['doctor_name']}
Phone: {$report['doctor_phone']}
Email: {$report['doctor_email']}
EOD;

$pdf->MultiCell(0, 10, $doctor_info, 0, 'L');
$pdf->Ln(5);

// Clinic information
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Clinic Information', 0, 1);
$pdf->SetFont('helvetica', '', 10);

$clinic_info = <<<EOD
{$report['name']}
{$report['address']}
Phone: {$report['phone']}
Email: {$report['email']}
Report Date: {$report['report_date']}
EOD;

$pdf->MultiCell(0, 10, $clinic_info, 0, 'L');
$pdf->Ln(10);

// Blood test results
if ($blood_tests) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Complete Blood Count (CBC)', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    
    // CBC table
    $html = '<table border="1" cellpadding="4">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th width="40%"><b>Test</b></th>
                <th width="30%"><b>Result</b></th>
                <th width="30%"><b>Reference Range</b></th>
            </tr>
        </thead>
        <tbody>';
    
    // Add CBC rows
    $html .= addTestRow('White Blood Cells (WBC)', $blood_tests['wbc'], '4,500 - 11,000 /μL', 4500, 11000);
    $html .= addTestRow('Red Blood Cells (RBC)', $blood_tests['rbc'], '4.0 - 5.4 million /μL', 4.0, 5.4);
    $html .= addTestRow('Hemoglobin (Hb)', $blood_tests['hemoglobin'], '12.0 - 15.5 g/dL', 12.0, 15.5);
    $html .= addTestRow('Hematocrit (Hct)', $blood_tests['hematocrit'], '36 - 46%', 36, 46);
    $html .= addTestRow('Platelets', $blood_tests['platelets'], '150,000 - 450,000 /μL', 150000, 450000);
    
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(10);
    
    // Blood Chemistry Panel
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Blood Chemistry Panel', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    
    $html = '<table border="1" cellpadding="4">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th width="40%"><b>Test</b></th>
                <th width="30%"><b>Result</b></th>
                <th width="30%"><b>Reference Range</b></th>
            </tr>
        </thead>
        <tbody>';
    
    // Add Chemistry rows
    $html .= addTestRow('Glucose', $blood_tests['glucose'], '70 - 99 mg/dL', 70, 99);
    $html .= addTestRow('Sodium', $blood_tests['sodium'], '135 - 145 mEq/L', 135, 145);
    $html .= addTestRow('Potassium', $blood_tests['potassium'], '3.5 - 5.1 mEq/L', 3.5, 5.1);
    $html .= addTestRow('Chloride', $blood_tests['chloride'], '98 - 107 mEq/L', 98, 107);
    $html .= addTestRow('AST', $blood_tests['ast'], '10 - 35 U/L', 10, 35);
    $html .= addTestRow('ALT', $blood_tests['alt'], '7 - 35 U/L', 7, 35);
    $html .= addTestRow('BUN', $blood_tests['bun'], '7 - 20 mg/dL', 7, 20);
    $html .= addTestRow('Creatinine', $blood_tests['creatinine'], '0.6 - 1.1 mg/dL', 0.6, 1.1);
    
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(10);
    
    // Lipid Profile
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Lipid Profile', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    
    $html = '<table border="1" cellpadding="4">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th width="40%"><b>Test</b></th>
                <th width="30%"><b>Result</b></th>
                <th width="30%"><b>Reference Range</b></th>
            </tr>
        </thead>
        <tbody>';
    
    // Add Lipid rows
    $html .= addTestRow('Total Cholesterol', $blood_tests['total_cholesterol'], '< 200 mg/dL', 0, 200);
    $html .= addTestRow('HDL', $blood_tests['hdl'], '> 40 mg/dL', 40, 999);
    $html .= addTestRow('LDL', $blood_tests['ldl'], '< 130 mg/dL', 0, 130);
    $html .= addTestRow('Triglycerides', $blood_tests['triglycerides'], '< 150 mg/dL', 0, 150);
    
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(10);
}

// Imaging results
if ($imaging) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Imaging Results', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    
    $html = '<table border="1" cellpadding="4">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th width="30%"><b>Organ</b></th>
                <th width="70%"><b>Observations</b></th>
            </tr>
        </thead>
        <tbody>';
    
    // Add imaging rows
    $html .= addImagingRow('Liver', $imaging['liver']);
    $html .= addImagingRow('Gallbladder', $imaging['gallbladder']);
    $html .= addImagingRow('Spleen', $imaging['spleen']);
    $html .= addImagingRow('Kidneys', $imaging['kidneys']);
    $html .= addImagingRow('Bladder', $imaging['bladder']);
    $html .= addImagingRow('Stomach', $imaging['stomach']);
    $html .= addImagingRow('Intestinal Loops', $imaging['intestinal_loops']);
    $html .= addImagingRow('Adrenals', $imaging['adrenals']);
    $html .= addImagingRow('Others', $imaging['others']);
    
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(10);
}

// General Impression
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'General Impression', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 10, $report['general_impression'] ?? 'All test results are within normal physiological ranges.', 0, 'L');
$pdf->Ln(10);

// Important Note
$pdf->SetFont('helvetica', 'I', 10);
$pdf->MultiCell(0, 10, 'Important Note: The isolated analysis of this exam has no diagnostic value unless evaluated in conjunction with clinical, epidemiological data, and other complementary exams.', 0, 'L');

// Output the PDF
$pdf->Output('lab_report_'.$report_id.'.pdf', 'I');