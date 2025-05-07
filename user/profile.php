<?php
// Start a session to manage user data across pages
session_start();

// Include the database connection file
include('includes/dbconnection.php');

// Check if the user is logged in by verifying the session variable 'odlmsuid'
if (!isset($_SESSION['odlmsuid']) || strlen($_SESSION['odlmsuid']) == 0) {
    // Redirect to logout.php if the user is not logged in
    header('location:logout.php');
    exit(); // Stop further execution
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Get the user ID from the session
    $uid = $_SESSION['odlmsuid'];

    // Get form data
    $AName = $_POST['name'];
    $mobno = $_POST['mobilenumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Update the user's profile in the database
    $sql = "UPDATE tbluser SET FullName = :name, MobileNumber = :mobilenumber, Email = :email, Address = :address  WHERE ID = :uid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':name', $AName, PDO::PARAM_STR);
    $query->bindParam(':mobilenumber', $mobno, PDO::PARAM_STR);
    $query->bindParam(':address', $address, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':uid', $uid, PDO::PARAM_STR);

    if ($query->execute()) {
        // Show success message
        echo '<script>alert("Profile has been updated.");</script>';
    } else {
        // Show error message if something went wrong
        echo '<script>alert("Something went wrong. Please try again.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - User Profile</title>
    <!-- Include necessary CSS and JavaScript libraries -->
    <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.css">
    <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
    <link rel="stylesheet" href="libs/bower/fullcalendar/dist/fullcalendar.min.css">
    <link rel="stylesheet" href="libs/bower/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">
    <script src="libs/bower/breakpoints.js/dist/breakpoints.min.js"></script>
    <script>
        Breakpoints(); // Initialize Breakpoints
    </script>
</head>
<body class="menubar-left menubar-unfold menubar-light theme-primary">
    <!-- Include header and sidebar -->
    <?php include_once('includes/header.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>

    <!-- Main Content -->
    <main id="app-main" class="app-main">
        <div class="wrap">
            <section class="app-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget">
                            <header class="widget-header">
                                <h3 class="widget-title">User Profile</h3>
                            </header><!-- .widget-header -->
                            <hr class="widget-separator">
                            <div class="widget-body">
                                <?php
                                // Fetch the logged-in user's profile data
                                $uid = $_SESSION['odlmsuid'];
                                $sql = "SELECT * FROM tbluser WHERE ID = :uid";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);

                                if ($query->rowCount() > 0) {
                                    foreach ($results as $row) {
                                ?>
                                        <!-- Profile Update Form -->
                                        <form class="form-horizontal" method="post">
                                            <!-- Full Name Field -->
                                            <div class="form-group">
                                                <label for="name" class="col-sm-3 control-label">Name:</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($row->FullName); ?>" required>
                                                </div>
                                            </div>

                                            <!-- Email Field -->
                                            <div class="form-group">
                                                <label for="email" class="col-sm-3 control-label">Email:</label>
                                                <div class="col-sm-9">
                                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($row->Email); ?>" readonly>
                                                </div>
                                            </div>

                                            <!-- Mobile Number Field -->
                                            <div class="form-group">
                                                <label for="mobilenumber" class="col-sm-3 control-label">Contact Number:</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" id="mobilenumber" name="mobilenumber" value="<?php echo htmlspecialchars($row->MobileNumber); ?>" maxlength="10" readonly>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="address" class="col-sm-3 control-label">Address:</label>
                                                <div class="col-sm-9">
                                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($row->Address ?? ''); ?></textarea>
                                                </div>
                                            </div>

                                            <!-- Registration Date Field -->
                                            <div class="form-group">
                                                <label for="regdate" class="col-sm-3 control-label">Registration Date:</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" id="regdate" value="<?php echo htmlspecialchars($row->RegDate); ?>" readonly>
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="row">
                                                <div class="col-sm-9 col-sm-offset-3">
                                                    <button type="submit" class="btn btn-success" name="submit">Update</button>
                                                </div>
                                            </div>
                                        </form>
                                <?php
                                    }
                                } else {
                                    echo '<p class="text-center">User not found.</p>';
                                }
                                ?>
                            </div><!-- .widget-body -->
                        </div><!-- .widget -->
                    </div><!-- END column -->
                </div><!-- .row -->
            </section><!-- .app-content -->
        </div><!-- .wrap -->

        <!-- Include footer -->
        <?php include_once('includes/footer.php'); ?>
    </main><!-- .app-main -->

    <!-- Include customizer -->
    <?php include_once('includes/customizer.php'); ?>

    <!-- Include necessary JavaScript libraries -->
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