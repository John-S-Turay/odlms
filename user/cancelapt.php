<?php
// Start a session to manage user data across pages
session_start();

// Include the database connection file
include_once('includes/dbconnection.php');

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Get the appointment number from the URL
    $aptno = $_GET['aptno'];

    // Set the status and get the remark from the form
    $ressta = "Appointment Cancelled";
    $remark = $_POST['restremark'];
    $canclbyuser = 1;

    // Insert tracking details into the database
    $sql = "INSERT INTO tbltracking (AppointmentNumeber, Remark, Status, OrderCanclledByUser) VALUES (:aptno, :remark, :ressta, :canclbyuser)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':aptno', $aptno, PDO::PARAM_STR);
    $query->bindParam(':remark', $remark, PDO::PARAM_STR);
    $query->bindParam(':ressta', $ressta, PDO::PARAM_STR);
    $query->bindParam(':canclbyuser', $canclbyuser, PDO::PARAM_STR);
    $query->execute();

    // Get the last inserted ID
    $LastInsertId = $dbh->lastInsertId();
    if ($LastInsertId > 0) {
        // Update the appointment status in the database
        $sql1 = "UPDATE tblappointment SET Status = :ressta WHERE AppointmentNumber = :aptno";
        $query1 = $dbh->prepare($sql1);
        $query1->bindParam(':aptno', $aptno, PDO::PARAM_STR);
        $query1->bindParam(':ressta', $ressta, PDO::PARAM_STR);
        $query1->execute();

        // Show success message
        echo '<script>alert("Your Appointment has been cancelled successfully.")</script>';
    } else {
        // Show error message
        echo '<script>alert("Something Went Wrong. Please try again")</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Appointment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div style="margin-left:50px;">
        <?php
        // Fetch appointment details
        $aptno = $_GET['aptno'];
        $sql3 = "SELECT AppointmentNumber, Status FROM tblappointment WHERE AppointmentNumber = :aptno";
        $query2 = $dbh->prepare($sql3);
        $query2->bindParam(':aptno', $aptno, PDO::PARAM_STR);
        $query2->execute();
        $results = $query2->fetchAll(PDO::FETCH_OBJ);

        if ($query2->rowCount() > 0) {
            foreach ($results as $row) {
                $status = $row->Status;
        ?>
                <table>
                    <tr align="center">
                        <th colspan="2">Cancel Appointment #<?php echo htmlspecialchars($aptno); ?></th>
                    </tr>
                    <tr>
                        <th>Appointment Number</th>
                        <th>Current Status</th>
                    </tr>
                    <tr>
                        <td><?php echo htmlspecialchars($row->AppointmentNumber); ?></td>
                        <td>
                            <?php
                            if (empty($status)) {
                                echo "Waiting for confirmation";
                            } else {
                                echo htmlspecialchars($status);
                            }
                            ?>
                        </td>
                    </tr>
                </table>

                <?php if (empty($status)) { ?>
                    <!-- Form to cancel appointment -->
                    <form method="post">
                        <table>
                            <tr>
                                <th>Reason for Cancellation</th>
                                <td>
                                    <textarea name="restremark" placeholder="Enter reason for cancellation" rows="12" cols="50" required></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center">
                                    <button type="submit" name="submit">Cancel Appointment</button>
                                </td>
                            </tr>
                        </table>
                    </form>
                <?php } else { ?>
                    <!-- Display message based on status -->
                    <?php if ($status == 'Appointment Cancelled') { ?>
                        <p class="error">Appointment Cancelled</p>
                    <?php } else { ?>
                        <p class="error">You can't cancel this. The appointment is already confirmed or completed.</p>
                    <?php } ?>
                <?php } ?>
        <?php
            }
        } else {
            echo '<p class="error">Appointment not found.</p>';
        }
        ?>
    </div>
</body>
</html>