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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODLMS - Navigation Bar</title>
    <!-- Include necessary CSS and JavaScript libraries -->
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav id="app-navbar" class="navbar navbar-inverse navbar-fixed-top primary">
        <!-- Navbar Header -->
        <div class="navbar-header">
            <!-- Button to toggle the sidebar on mobile devices -->
            <button type="button" id="menubar-toggle-btn" class="navbar-toggle visible-xs-inline-block navbar-toggle-left hamburger hamburger--collapse js-hamburger" aria-label="Toggle sidebar">
                <span class="sr-only">Toggle navigation</span>
                <span class="hamburger-box"><span class="hamburger-inner"></span></span>
            </button>

            <!-- Button to toggle the navbar collapse on mobile devices -->
            <button type="button" class="navbar-toggle navbar-toggle-right collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false" aria-label="Toggle navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="zmdi zmdi-hc-lg zmdi-more"></span>
            </button>

            <!-- Button to toggle the search bar on mobile devices -->
            <button type="button" class="navbar-toggle navbar-toggle-right collapsed" data-toggle="collapse" data-target="#navbar-search" aria-expanded="false" aria-label="Toggle search">
                <span class="sr-only">Toggle navigation</span>
                <span class="zmdi zmdi-hc-lg zmdi-search"></span>
            </button>

            <!-- Brand Logo and Name -->
            <a href="dashboard.php" class="navbar-brand">
                <span class="brand-icon"><i class="fa fa-gg"></i></span>
                <span class="brand-name">ODLMS</span>
            </a>
        </div><!-- .navbar-header -->

        <!-- Navbar Container -->
        <div class="navbar-container container-fluid">
            <!-- Navbar Collapse Section -->
            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side of the Navbar -->
                <ul class="nav navbar-toolbar navbar-toolbar-left navbar-left">
                    <!-- Button to fold/unfold the sidebar -->
                    <li class="hidden-float hidden-menubar-top">
                        <a href="javascript:void(0)" role="button" id="menubar-fold-btn" class="hamburger hamburger--arrowalt is-active js-hamburger" aria-label="Fold/unfold sidebar">
                            <span class="hamburger-box"><span class="hamburger-inner"></span></span>
                        </a>
                    </li>
                    <!-- Page Title -->
                    <li>
                        <h5 class="page-title hidden-menubar-top hidden-float">Dashboard</h5>
                    </li>
                </ul>

                <!-- Right Side of the Navbar -->
                <ul class="nav navbar-toolbar navbar-toolbar-right navbar-right">
                    <!-- Notifications Dropdown -->
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" aria-label="Notifications">
                            <i class="zmdi zmdi-hc-lg zmdi-notifications"></i>
                            <?php
                            // Fetch the total number of notifications
                            $uid = $_SESSION['odlmsuid'];
                            $sql = "SELECT COUNT(*) as total FROM tblappointment WHERE Status='Report Uploaded' && UserID=:uid";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                            $query->execute();
                            $result = $query->fetch(PDO::FETCH_OBJ);
                            if ($result->total > 0) {
                                echo '<span class="badge">' . $result->total . '</span>';
                            }
                            ?>
                        </a>

                        <!-- Notifications Dropdown Menu -->
                        <div class="media-group dropdown-menu animated flipInY">
                            <?php
                            // Fetch notifications for the logged-in user
                            $sql = "SELECT * FROM tblappointment WHERE Status='Report Uploaded' && UserID=:uid ORDER BY ReportUploadedDate DESC LIMIT 5";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                            if ($query->rowCount() > 0) {
                                foreach ($results as $row) {
                            ?>
                                    <!-- Notification Item -->
                                    <a href="view-medicalreport-detail.php?viewid=<?php echo htmlspecialchars($row->ID); ?>&&aptid=<?php echo htmlspecialchars($row->AppointmentNumber); ?>" class="media-group-item">
                                        <div class="media">
                                            <div class="media-left">
                                                <div class="avatar avatar-xs avatar-circle">
                                                    <img src="assets/images/images.png" alt="Notification Icon">
                                                    <i class="status status-online"></i>
                                                </div>
                                            </div>
                                            <div class="media-body">
                                                <h5 class="media-heading">Report Uploaded</h5>
                                                <small class="media-meta"><?php echo htmlspecialchars($row->AppointmentNumber); ?> at (<?php echo htmlspecialchars($row->ReportUploadedDate); ?>)</small>
                                            </div>
                                        </div>
                                    </a><!-- .media-group-item -->
                            <?php
                                }
                            } else {
                                echo '<div class="media-group-item">No new notifications.</div>';
                            }
                            ?>
                        </div>
                    </li>

                    <!-- Settings Dropdown -->
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" aria-label="Settings">
                            <i class="zmdi zmdi-hc-lg zmdi-settings"></i>
                        </a>
                        <!-- Settings Dropdown Menu -->
                        <ul class="dropdown-menu animated flipInY">
                            <li><a href="profile.php"><i class="zmdi m-r-md zmdi-hc-lg zmdi-account-box"></i>My Profile</a></li>
                            <li><a href="change-password.php"><i class="zmdi m-r-md zmdi-hc-lg zmdi-balance-wallet"></i>Change Password</a></li>
                            <li><a href="logout.php"><i class="zmdi m-r-md zmdi-hc-lg zmdi-sign-in"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div><!-- .navbar-container -->
    </nav>

    <!-- Include necessary JavaScript libraries -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>