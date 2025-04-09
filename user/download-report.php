<?php
// Start session and include database connection
session_start();
include('includes/dbconnection.php');

// Check if user is logged in (either as admin or regular user)
if (!isset($_SESSION['odlmsaid']) && !isset($_SESSION['odlmsuid'])) {
    header('location:logout.php');
    exit();
}

// Get report ID from URL
$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($reportId <= 0) {
    header("HTTP/1.0 400 Bad Request");
    die("Invalid report ID");
}

// Determine user type and ID
$isAdmin = isset($_SESSION['odlmsaid']);
$userId = $isAdmin ? $_SESSION['odlmsaid'] : $_SESSION['odlmsuid'];

// Query to fetch report with access control
$sql = "SELECT r.file_name, r.file_data, r.mime_type, r.file_size
        FROM tbl_reports r
        JOIN tblappointment a ON r.appointment_id = a.ID
        WHERE r.id = :reportId";

// Add access control based on user type
if (!$isAdmin) {
    $sql .= " AND a.UserID = :userId";
}

$query = $dbh->prepare($sql);
$query->bindParam(':reportId', $reportId, PDO::PARAM_INT);

if (!$isAdmin) {
    $query->bindParam(':userId', $userId, PDO::PARAM_INT);
}

$query->execute();
$report = $query->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    header("HTTP/1.0 404 Not Found");
    die("Report not found or access denied");
}

// Set headers for file download
header("Content-Type: " . $report['mime_type']);
header("Content-Length: " . $report['file_size']);
header("Content-Disposition: attachment; filename=\"" . $report['file_name'] . "\"");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");

// Output the file data
echo $report['file_data'];
exit();
?>