<?php
session_start();
include('includes/dbconnection.php');

// Check admin authentication
if (strlen($_SESSION['odlmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Validate prescription ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid request');
}

$prescription_id = (int)$_GET['id'];

// Fetch prescription data
$sql = "SELECT file_name, mime_type, file_data 
        FROM tbl_prescriptions 
        WHERE id = :id";
$query = $dbh->prepare($sql);
$query->bindParam(':id', $prescription_id, PDO::PARAM_INT);
$query->execute();
$prescription = $query->fetch(PDO::FETCH_OBJ);

if (!$prescription) {
    header('HTTP/1.1 404 Not Found');
    exit('Prescription not found');
}

// Set headers for file download
header('Content-Type: ' . $prescription->mime_type);
header('Content-Disposition: attachment; filename="' . basename($prescription->file_name) . '"');
header('Content-Length: ' . strlen($prescription->file_data));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Output the file data
echo $prescription->file_data;
exit();
?>