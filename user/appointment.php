<?php
// Start a session to manage user data across pages
session_start();

// Enable error reporting for debugging (disable in production)
error_reporting(0);
ini_set('display_errors', 1);

// Include the database connection file
include('includes/dbconnection.php');

// Check if the user is logged in by verifying the session variable 'odlmsuid'
if (!isset($_SESSION['odlmsuid']) || strlen($_SESSION['odlmsuid']) == 0) {
    // Redirect to logout.php if the user is not logged in
    header('location:logout.php');
    exit(); // Stop further execution
} else {
    // Check if the form is submitted
    if (isset($_POST['submit'])) {
        // Retrieve the user ID from the session
        $uid = $_SESSION['odlmsuid'];

        // Retrieve form data
        $pname = $_POST['pname']; // Patient name
        $gender = $_POST['gender']; // Patient gender
        $dob = $_POST['dob']; // Date of birth
        $mobnum = $_POST['mobnum']; // Mobile number
        $email = $_POST['email']; // Email ID
        $address = $_POST['address']; // Home address
        $aptdate = $_POST['aptdate']; // Appointment date
        $apttime = $_POST['apttime']; // Appointment time
        $aptnumber = mt_rand(100000000, 999999999); // Generate a random appointment number

        // Validate address
        if (empty(trim($address))) {
            echo '<script>alert("Please enter your home address");</script>';
            exit();
        }

        // Sanitize address
        $address = htmlspecialchars(trim($address), ENT_QUOTES, 'UTF-8');

        // Determine if this is for lab tests or doctor appointment
        $is_lab = isset($_POST['tids']) && count($_POST['tids']) > 0 ? 1 : 0;
        $slot_type = $is_lab ? 'lab' : 'doctor';

        // Check availability before booking
        $sql_check = "SELECT id, max_slots 
                     FROM tblavailability av
                     WHERE av.date = :aptdate 
                     AND :apttime BETWEEN av.start_time AND av.end_time
                     AND av.slot_type = :slot_type
                     AND av.status = 1
                     AND (SELECT COUNT(*) FROM tblappointment WHERE availability_id = av.id) < av.max_slots
                     LIMIT 1";
        $query = $dbh->prepare($sql_check);
        $query->bindParam(':aptdate', $aptdate, PDO::PARAM_STR);
        $query->bindParam(':apttime', $apttime, PDO::PARAM_STR);
        $query->bindParam(':slot_type', $slot_type, PDO::PARAM_STR);
        $query->execute();
        $slot = $query->fetch(PDO::FETCH_OBJ);

        if (!$slot) {
            echo '<script>alert("This time slot is no longer available. Please choose another time.");</script>';
            echo '<script>
                if ($("#aptdate").val()) {
                    $("#aptdate").trigger("change");
                }
            </script>';
            exit();
        }

        // Handle file upload for prescription
        $pres = $_FILES["pres"]["name"]; // Get the file name
        $extension = substr($pres, strlen($pres) - 4, strlen($pres)); // Extract file extension
        $allowed_extensions = array(".jpg", "jpeg", ".png", ".gif", ".pdf"); // Allowed file types

        // Check if a file is uploaded
        if (!empty($pres)) {
            // Validate file extension
            if (!in_array($extension, $allowed_extensions)) {
                echo "<script>alert('Prescription has Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
            } else {
                // Rename the file to avoid conflicts
                $pres = md5($pres) . time() . $extension;
                // Move the uploaded file to the "images" directory
                move_uploaded_file($_FILES["pres"]["tmp_name"], "images/" . $pres);

                // Insert appointment data into the database
                $sql = "INSERT INTO tblappointment (UserID, AppointmentNumber, PatientName, Gender, DOB, MobileNumber, Email, address, AppointmentDate, AppointmentTime, Prescription, availability_id) 
                        VALUES (:uid, :aptnumber, :pname, :gender, :dob, :mobnum, :email, :address, :aptdate, :apttime, :pres, :availability_id)";
                $query = $dbh->prepare($sql);
                // Bind parameters to prevent SQL injection
                $query->bindParam(':pname', $pname, PDO::PARAM_STR);
                $query->bindParam(':gender', $gender, PDO::PARAM_STR);
                $query->bindParam(':dob', $dob, PDO::PARAM_STR);
                $query->bindParam(':mobnum', $mobnum, PDO::PARAM_STR);
                $query->bindParam(':email', $email, PDO::PARAM_STR);
                $query->bindParam(':address', $address, PDO::PARAM_STR);
                $query->bindParam(':aptdate', $aptdate, PDO::PARAM_STR);
                $query->bindParam(':apttime', $apttime, PDO::PARAM_STR);
                $query->bindParam(':pres', $pres, PDO::PARAM_STR);
                $query->bindParam(':aptnumber', $aptnumber, PDO::PARAM_STR);
                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                $query->bindParam(':availability_id', $slot->id, PDO::PARAM_INT);

                // Execute the query
                $query->execute();

                // Get the last inserted ID
                $LastInsertId = $dbh->lastInsertId();
                if ($LastInsertId > 0) {
                    // Handle test requests if any
                    if (isset($_POST['tids'])) {
                        $tid = $_POST['tids'];
                        for ($i = 0; $i < count($tid); $i++) {
                            $tvid = $tid[$i];
                            // Insert test request data into the database
                            $sql = "INSERT INTO tbltestrequest (AppointmentNumber, TestID, MobileNumber) 
                                    VALUES (:aptnumber, :tvid, :mobnum)";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':mobnum', $mobnum, PDO::PARAM_STR);
                            $query->bindParam(':aptnumber', $aptnumber, PDO::PARAM_STR);
                            $query->bindParam(':tvid', $tvid, PDO::PARAM_STR);
                            $query->execute();
                        }
                    }

                    // Show success message with the appointment number
                    echo '<script>alert("Your Appointment has been taken successfully. Appointment number is " + "' . $aptnumber . '")</script>';
                } else {
                    // Show error message if something went wrong
                    echo '<script>alert("Something Went Wrong. Please try again")</script>';
                }
            }
        } else {
            // If no file is uploaded, insert data without the prescription field
            $sql = "INSERT INTO tblappointment (UserID, AppointmentNumber, PatientName, Gender, DOB, MobileNumber, Email, address, AppointmentDate, AppointmentTime, availability_id) 
                    VALUES (:uid, :aptnumber, :pname, :gender, :dob, :mobnum, :email, :address, :aptdate, :apttime, :availability_id)";
            $query = $dbh->prepare($sql);
            // Bind parameters
            $query->bindParam(':pname', $pname, PDO::PARAM_STR);
            $query->bindParam(':gender', $gender, PDO::PARAM_STR);
            $query->bindParam(':dob', $dob, PDO::PARAM_STR);
            $query->bindParam(':mobnum', $mobnum, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':address', $address, PDO::PARAM_STR);
            $query->bindParam(':aptdate', $aptdate, PDO::PARAM_STR);
            $query->bindParam(':apttime', $apttime, PDO::PARAM_STR);
            $query->bindParam(':aptnumber', $aptnumber, PDO::PARAM_STR);
            $query->bindParam(':uid', $uid, PDO::PARAM_STR);
            $query->bindParam(':availability_id', $slot->id, PDO::PARAM_INT);

            // Execute the query
            $query->execute();

            // Get the last inserted ID
            $LastInsertId = $dbh->lastInsertId();
            if ($LastInsertId > 0) {
                // Handle test requests if any
                if (isset($_POST['tids'])) {
                    $tid = $_POST['tids'];
                    for ($i = 0; $i < count($tid); $i++) {
                        $tvid = $tid[$i];
                        $sql = "INSERT INTO tbltestrequest (AppointmentNumber, TestID, MobileNumber) 
                                VALUES (:aptnumber, :tvid, :mobnum)";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':mobnum', $mobnum, PDO::PARAM_STR);
                        $query->bindParam(':aptnumber', $aptnumber, PDO::PARAM_STR);
                        $query->bindParam(':tvid', $tvid, PDO::PARAM_STR);
                        $query->execute();
                    }
                }

                // Show success message with the appointment number
                echo '<script>alert("Your Appointment has been taken successfully. Appointment number is " + "' . $aptnumber . '")</script>';
            } else {
                // Show error message if something went wrong
                echo '<script>alert("Something Went Wrong. Please try again")</script>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Set the title of the page -->
    <title>ODLMS || Appointment Form</title>

    <!-- Include CSS files for styling -->
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.css">
    <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
    <link rel="stylesheet" href="libs/bower/fullcalendar/dist/fullcalendar.min.css">
    <link rel="stylesheet" href="libs/bower/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">

    <!-- Include Breakpoints.js for responsive design -->
    <script src="libs/bower/breakpoints.js/dist/breakpoints.min.js"></script>
    <script>
        Breakpoints(); // Initialize Breakpoints
    </script>
</head>

<body class="menubar-left menubar-unfold menubar-light theme-primary">
    <!-- Include header and sidebar -->
    <?php include_once('includes/header.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>

    <!-- Main content area -->
    <main id="app-main" class="app-main">
        <div class="wrap">
            <section class="app-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h4 class="widget-title">Appointment</h4>
                            </header>
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <!-- Appointment form -->
                                <form method="post" enctype="multipart/form-data">
                                    <!-- Patient Name -->
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Patient Name</label>
                                        <input type="text" class="form-control" id="pname" name="pname" required="true">
                                    </div>
                                    <!-- Patient Gender -->
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Patient Gender</label>
                                        <label>
                                            <input type="radio" name="gender" id="gender" value="Female" checked="true">Female
                                        </label>
                                        <label>
                                            <input type="radio" name="gender" id="gender" value="Male">Male
                                        </label>
                                    </div>
                                    <!-- Date of Birth -->
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Date of Birth</label>
                                        <input type="date" class="form-control" id="dob" name="dob" required="true">
                                    </div>
                                    <!-- Mobile Number -->
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Mobile Number</label>
                                        <input type="text" class="form-control" id="mobnum" name="mobnum" maxlength="10" pattern="[0-9]+" required="true">
                                    </div>
                                    <!-- Email ID -->
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Email ID</label>
                                        <input type="email" class="form-control" id="email" name="email" required="true">
                                    </div>
                                    <!-- Home Address - NEW FIELD -->
                                    <div class="form-group">
                                        <label for="address">Home Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3" required="true"></textarea>
                                    </div>
                                    <!-- Appointment Date -->
                                    <div class="form-group">
                                        <label for="aptdate">Appointment Date</label>
                                        <select class="form-control" id="aptdate" name="aptdate" required>
                                            <option value="">Select Date</option>
                                            <?php
                                            // Get available dates
                                            $sql = "SELECT DISTINCT date FROM tblavailability 
                                                    WHERE status = 1 AND date >= CURDATE()
                                                    ORDER BY date";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            while ($row = $query->fetch(PDO::FETCH_OBJ)) {
                                                echo '<option value="'.htmlentities($row->date).'">'
                                                    .htmlentities(date('D, M d, Y', strtotime($row->date)))
                                                    .'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <!-- Appointment Time -->
                                    <div class="form-group">
                                        <label for="apttime">Available Time Slots</label>
                                        <select class="form-control" id="apttime" name="apttime" required disabled>
                                            <option value="">Select Date first</option>
                                        </select>
                                        <div id="slot-details" class="mt-2 small text-muted"></div>
                                    </div>
                                    <!-- Prescription Upload -->
                                    <div class="form-group">
                                        <label for="exampleInputFile">Prescription (if any)</label>
                                        <input type="file" id="pres" class="form-control" name="pres">
                                    </div>
                                    <!-- Test Selection -->
                                    <div class="form-group">
                                        <label for="exampleInputFile" style="color: red" required="true">Select Test</label>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Test Name</th>
                                                    <th>Test Price</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Fetch and display available lab tests
                                                $sql = "SELECT * FROM tbllabtest";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);

                                                $cnt = 1;
                                                if ($query->rowCount() > 0) {
                                                    foreach ($results as $row) {
                                                ?>
                                                        <tr>
                                                            <th scope="row"><?php echo htmlentities($cnt); ?></th>
                                                            <td><?php echo htmlentities($row->TestTitle); ?></td>
                                                            <td><?php echo htmlentities($row->Price); ?></td>
                                                            <td><input type="checkbox" name="tids[]" value="<?php echo htmlentities($row->ID); ?>" class="test-checkbox"></td>
                                                        </tr>
                                                <?php
                                                        $cnt = $cnt + 1;
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Submit Button -->
                                    <button type="submit" class="btn btn-primary btn-md" name="submit">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- Include footer -->
        <?php include_once('includes/footer.php'); ?>
    </main>

    <!-- Include customizer -->
    <?php include_once('includes/customizer.php'); ?>

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
    <script src="libs/bower/moment/moment.js"></script>
    <script src="libs/bower/fullcalendar/dist/fullcalendar.min.js"></script>
    <script src="assets/js/fullcalendar.js"></script>
    
    <!-- Enhanced Appointment Time Selection -->
    <script src="assets/js/appointment-time-selection.js"></script>
</body>
</html>