<?php
session_start();
include('includes/dbconnection.php');

header('Content-Type: application/json');

$date = isset($_POST['date']) ? htmlspecialchars($_POST['date']) : '';
$is_lab = isset($_POST['is_lab']) ? (int)$_POST['is_lab'] : 0;

if (empty($date)) {
    echo json_encode(['error' => 'Date is required']);
    exit();
}

// Determine slot type based on whether tests are selected
$slot_type = $is_lab ? 'lab' : 'doctor';

// Get available time slots
$sql = "SELECT a.id, a.start_time, a.end_time, a.max_slots, 
       (SELECT COUNT(*) FROM tblappointment ap WHERE ap.availability_id = a.id) as booked
       FROM tblavailability a
       WHERE a.date = :date 
       AND a.slot_type = :slot_type
       AND a.status = 1
       HAVING booked < a.max_slots
       ORDER BY a.start_time";

$query = $dbh->prepare($sql);
$query->bindParam(':date', $date, PDO::PARAM_STR);
$query->bindParam(':slot_type', $slot_type, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// Get fully booked slots to show as unavailable
$sql_booked = "SELECT a.start_time, a.end_time, a.max_slots,
              (SELECT COUNT(*) FROM tblappointment ap WHERE ap.availability_id = a.id) as booked
              FROM tblavailability a
              WHERE a.date = :date 
              AND a.slot_type = :slot_type
              AND a.status = 1
              HAVING booked >= a.max_slots
              ORDER BY a.start_time";

$query = $dbh->prepare($sql_booked);
$query->bindParam(':date', $date, PDO::PARAM_STR);
$query->bindParam(':slot_type', $slot_type, PDO::PARAM_STR);
$query->execute();
$booked_slots = $query->fetchAll(PDO::FETCH_OBJ);

echo json_encode([
    'times' => $results,
    'booked_slots' => $booked_slots,
    'date' => $date,
    'slot_type' => $slot_type
]);
?>