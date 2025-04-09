<?php
session_start();
include('includes/dbconnection.php');

// Check authentication
if (strlen($_SESSION['odlmsaid']) == 0 && strlen($_SESSION['uid']) == 0) {
    header('location:logout.php');
    exit;
}

$reportId = $_GET['id'] ?? 0;

// Verify access rights
$sql = "SELECT r.file_name, r.file_data, r.mime_type, r.file_size
        FROM tbl_reports r
        JOIN tblappointment a ON r.appointment_id = a.ID
        WHERE r.id = :reportid AND 
              (a.UserID = :userid OR :isadmin = 1)";
        
$query = $dbh->prepare($sql);
$query->bindValue(':reportid', $reportId, PDO::PARAM_INT);
$query->bindValue(':userid', $_SESSION['uid'] ?? 0, PDO::PARAM_INT);
$query->bindValue(':isadmin', isset($_SESSION['odlmsaid']) ? 1 : 0, PDO::PARAM_INT);
$query->execute();
$report = $query->fetch(PDO::FETCH_ASSOC);

if ($report) {
    header("Content-Type: ".$report['mime_type']);
    header("Content-Length: ".$report['file_size']);
    header("Content-Disposition: attachment; filename=\"".$report['file_name'].'"');
    echo $report['file_data'];
} else {
    header("HTTP/1.0 404 Not Found");
    echo "Report not found or access denied";
}
exit;
?>