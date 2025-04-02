<?php
// Start a session to manage user sessions
session_start();

// Disable error reporting for production (enable during development)
error_reporting(0);

// Include the database connection file
include('includes/dbconnection.php');

// Check if the user is logged in by verifying the session variable
if (strlen($_SESSION['odlmsaid']) == 0) {
    // Redirect to logout.php if the session is invalid
    header('location:logout.php');
    exit(); // Stop further execution
} else {
    // Handle form submission for adding new slots
    if (isset($_POST['add_slots'])) {
        // Retrieve and sanitize form data
        $date = htmlspecialchars($_POST['date']);
        $slot_type = htmlspecialchars($_POST['slot_type']);
        $max_slots = filter_var($_POST['max_slots'], FILTER_SANITIZE_NUMBER_INT);
        $admin_id = $_SESSION['odlmsaid'];
        
        // Get the time slots array
        $time_slots = $_POST['time_slots'];
        $success_count = 0;

        // Validate inputs
        if (empty($date) || empty($time_slots) || empty($max_slots)) {
            echo '<script>alert("Date, time slots and maximum slots are required.");</script>';
        } elseif ($max_slots <= 0) {
            echo '<script>alert("Maximum slots must be greater than 0.");</script>';
        } else {
            // Process each time slot
            foreach ($time_slots as $time) {
                if (!empty($time)) {
                    // Convert time to proper format (HH:MM:SS)
                    $time_parts = date_parse($time);
                    $formatted_time = sprintf("%02d:%02d:00", $time_parts['hour'], $time_parts['minute']);
                    
                    // Insert each time slot as a separate availability slot
                    $sql = "INSERT INTO tblavailability (admin_id, date, start_time, end_time, slot_type, max_slots) 
                            VALUES (:admin_id, :date, :start_time, :end_time, :slot_type, :max_slots)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
                    $query->bindParam(':date', $date, PDO::PARAM_STR);
                    $query->bindParam(':start_time', $formatted_time, PDO::PARAM_STR);
                    $query->bindParam(':end_time', $formatted_time, PDO::PARAM_STR); // Same as start time for single time slots
                    $query->bindParam(':slot_type', $slot_type, PDO::PARAM_STR);
                    $query->bindParam(':max_slots', $max_slots, PDO::PARAM_INT);

                    if ($query->execute()) {
                        $success_count++;
                    }
                }
            }
            
            if ($success_count > 0) {
                echo '<script>alert("Successfully added ' . $success_count . ' time slots.");</script>';
                echo "<script>window.location.href ='manage_availability.php';</script>";
            } else {
                echo '<script>alert("Failed to add time slots. Please try again.");</script>';
            }
        }
    }

    // Handle slot status toggle
    if (isset($_GET['toggle_status'])) {
        $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        $sql = "UPDATE tblavailability SET status = 1-status WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        if ($query->execute()) {
            header('Location: manage_availability.php');
            exit();
        }
    }

    // Handle slot deletion
    if (isset($_GET['delete_slot'])) {
        $id = filter_var($_GET['delete_slot'], FILTER_SANITIZE_NUMBER_INT);
        $sql = "DELETE FROM tblavailability WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        if ($query->execute()) {
            echo '<script>alert("Time slot deleted successfully.");</script>';
            echo "<script>window.location.href ='manage_availability.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - Manage Availability</title>
    <!-- Include CSS files -->
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
        .time-slot-inputs {
            margin-bottom: 15px;
        }
        .time-slot-box {
            display: flex;
            margin-bottom: 10px;
        }
        .time-slot-box input {
            margin-right: 10px;
        }
        .add-time-btn, .remove-time-btn {
            margin-left: 10px;
        }
        .slot-display {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 5px;
            padding: 5px 10px;
            background: #f5f5f5;
            border-radius: 4px;
        }
    </style>
    <!-- Include JavaScript for Breakpoints -->
    <script src="libs/bower/breakpoints.js/dist/breakpoints.min.js"></script>
    <script>
        Breakpoints();
    </script>
</head>
<body class="menubar-left menubar-unfold menubar-light theme-primary">
    <!-- Include header -->
    <?php include_once('includes/header.php'); ?>

    <!-- Include sidebar -->
    <?php include_once('includes/sidebar.php'); ?>

    <!-- Main content -->
    <main id="app-main" class="app-main">
        <div class="wrap">
            <section class="app-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h3 class="widget-title">Manage Availability Slots</h3>
                            </header>
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <!-- Add Slot Form -->
                                <form class="form-horizontal" method="post" id="addSlotsForm">
                                    <!-- Date -->
                                    <div class="form-group">
                                        <label for="slotDate" class="col-sm-3 control-label">Date:</label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control" id="slotDate" name="date" 
                                                   min="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    
                                    <!-- Slot Type -->
                                    <div class="form-group">
                                        <label for="slotType" class="col-sm-3 control-label">Slot Type:</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="slotType" name="slot_type" required>
                                                
                                                <option value="lab">Lab Test</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Max Slots -->
                                    <div class="form-group">
                                        <label for="maxSlots" class="col-sm-3 control-label">Maximum Slots per Time:</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" id="maxSlots" 
                                                   name="max_slots" min="1" value="1" required>
                                        </div>
                                    </div>
                                    
                                    <!-- Time Slots -->
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Time Slots:</label>
                                        <div class="col-sm-9 time-slot-inputs">
                                            <div id="timeSlotsContainer">
                                                <div class="time-slot-box">
                                                    <input type="time" class="form-control" name="time_slots[]" required>
                                                    <button type="button" class="btn btn-primary btn-sm add-time-btn" onclick="addTimeSlot()">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-muted">Add all available time slots for this date</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Submit button -->
                                    <div class="row">
                                        <div class="col-sm-9 col-sm-offset-3">
                                            <button type="submit" class="btn btn-success" name="add_slots">Add Time Slots</button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Existing Slots Table -->
                                <h4 class="mt-4">Existing Availability Slots</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered mt-3">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Type</th>
                                                <th>Capacity</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Fetch all availability slots grouped by date
                                            $sql = "SELECT a.*, 
                                                   (SELECT COUNT(*) FROM tblappointment ap WHERE ap.availability_id = a.id) as booked
                                                   FROM tblavailability a
                                                   ORDER BY a.date DESC, a.start_time";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            
                                            if ($query->rowCount() > 0) {
                                                $current_date = null;
                                                foreach($results as $row) {
                                                    $status_class = $row->status ? 'success' : 'danger';
                                                    $status_text = $row->status ? 'Active' : 'Inactive';
                                                    $toggle_text = $row->status ? 'Deactivate' : 'Activate';
                                                    ?>
                                                    <tr>
                                                        <td><?= htmlentities(date('D, M d, Y', strtotime($row->date))) ?></td>
                                                        <td><?= htmlentities(date('h:i A', strtotime($row->start_time))) ?></td>
                                                        <td><?= ucfirst(htmlentities($row->slot_type)) ?></td>
                                                        <td><?= htmlentities($row->booked) ?>/<?= htmlentities($row->max_slots) ?></td>
                                                        <td><span class="label label-<?= $status_class ?>"><?= $status_text ?></span></td>
                                                        <td>
                                                            <a href="manage_availability.php?toggle_status=1&id=<?= $row->id ?>" 
                                                               class="btn btn-sm btn-<?= $status_class ?>">
                                                                <?= $toggle_text ?>
                                                            </a>
                                                            <a href="manage_availability.php?delete_slot=<?= $row->id ?>" 
                                                               class="btn btn-sm btn-danger" 
                                                               onclick="return confirm('Are you sure you want to delete this time slot?')">
                                                                <i class="fa fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="6" class="text-center">No availability slots found</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- Include footer -->
        <?php include_once('includes/footer.php'); ?>
    </main>

    <!-- Include JavaScript files -->
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
    <script src="assets/js/timeSlot.js"></script>
</body>
</html>