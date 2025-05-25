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
    <title>ODLMS - Sidebar</title>
    <!-- Include necessary CSS and JavaScript libraries -->
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/core.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css">
    <style>
        /* Avatar styling */
        .avatar-md {
            width: 48px;
            height: 48px;
        }
        .avatar-circle {
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #eee;
        }
        .avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        /* Hover effects */
        .avatar-circle:hover {
            border-color: #4CAF50;
            transition: border-color 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside id="menubar" class="menubar light">
        <!-- User Profile Section -->
        <div class="app-user">
            <div class="media">
                <div class="media-left">
                    <div class="avatar avatar-md avatar-circle">
                        <?php
                        // Fetch user profile photo
                        $uid = $_SESSION['odlmsuid'];
                        $sql = "SELECT ProfilePhoto FROM tbluser WHERE ID = :uid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                        $query->execute();
                        $profilePhoto = $query->fetchColumn();
                        
                        if (!empty($profilePhoto) && file_exists('userprofile/'.$profilePhoto)) {
                            echo '<img class="img-responsive" src="userprofile/'.htmlspecialchars($profilePhoto).'" alt="User Avatar">';
                        } else {
                            echo '<img class="img-responsive" src="assets/images/default-avatar.jpg" alt="Default Avatar">';
                        }
                        ?>
                    </div><!-- .avatar -->
                </div>
                <div class="media-body">
                    <div class="foldable">
                        <?php
                        // Fetch user details from the database
                        $uid = $_SESSION['odlmsuid'];
                        $sql = "SELECT FullName, Email FROM tbluser WHERE ID = :uid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);

                        if ($query->rowCount() > 0) {
                            foreach ($results as $row) {
                        ?>
                                <h5><a href="javascript:void(0)" class="username"><?php echo htmlspecialchars($row->FullName); ?></a></h5>
                                <ul>
                                    <li class="dropdown">
                                        <a href="javascript:void(0)" class="dropdown-toggle usertitle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="User Menu">
                                            <small><?php echo htmlspecialchars($row->Email); ?></small>
                                            <span class="caret"></span>
                                        </a>
                                        <!-- User Dropdown Menu -->
                                        <ul class="dropdown-menu animated flipInY">
                                            <li>
                                                <a class="text-color" href="dashboard.php">
                                                    <span class="m-r-xs"><i class="fa fa-home"></i></span>
                                                    <span>Home</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="text-color" href="profile.php">
                                                    <span class="m-r-xs"><i class="fa fa-user"></i></span>
                                                    <span>Profile</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="text-color" href="change-password.php">
                                                    <span class="m-r-xs"><i class="fa fa-gear"></i></span>
                                                    <span>Settings</span>
                                                </a>
                                            </li>
                                            <li role="separator" class="divider"></li>
                                            <li>
                                                <a class="text-color" href="logout.php">
                                                    <span class="m-r-xs"><i class="fa fa-power-off"></i></span>
                                                    <span>Logout</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                        <?php
                            }
                        } else {
                            echo '<h5><a href="javascript:void(0)" class="username">User Not Found</a></h5>';
                        }
                        ?>
                    </div>
                </div><!-- .media-body -->
            </div><!-- .media -->
        </div><!-- .app-user -->

        <!-- Sidebar Navigation Links -->
        <div class="menubar-scroll">
            <div class="menubar-scroll-inner">
                <ul class="app-menu">
                    <!-- Dashboard -->
                    <li class="has-submenu">
                        <a href="dashboard.php">
                            <i class="menu-icon zmdi zmdi-view-dashboard zmdi-hc-lg"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>

                    <!-- View Test Detail -->
                    <li class="has-submenu">
                        <a href="view-testdetail.php">
                            <i class="menu-icon zmdi zmdi-layers zmdi-hc-lg"></i>
                            <span class="menu-text">View Test Detail</span>
                        </a>
                    </li>

                    <!-- Book Appointment -->
                    <li class="has-submenu">
                        <a href="appointment.php">
                            <i class="menu-icon zmdi zmdi-puzzle-piece zmdi-hc-lg"></i>
                            <span class="menu-text">Book Appointment</span>
                        </a>
                    </li>

                    <!-- Appointment History -->
                    <li class="has-submenu">
                        <a href="appointment-history.php">
                            <i class="menu-icon zmdi zmdi-inbox zmdi-hc-lg"></i>
                            <span class="menu-text">Appointment History</span>
                        </a>
                    </li>

                    <!-- View Medical Report -->
                    <li class="has-submenu">
                        <a href="view-medical-report.php">
                            <i class="menu-icon zmdi zmdi-inbox zmdi-hc-lg"></i>
                            <span class="menu-text">View Medical Report</span>
                        </a>
                    </li>

                     <!-- Enable 2fa -->
                    <li>
                        <a href="security_settings.php" style="display: flex; align-items: center; gap: 10px;">
                            <i class="zmdi zmdi-shield-security" style="font-size: 18px;"></i>
                            <span class="menu-text">Enable 2-Factor Authentication</span>
                        </a>
                    </li>

                    <!-- Search -->
                    <li>
                        <a href="search.php">
                            <i class="menu-icon zmdi zmdi-search zmdi-hc-lg"></i>
                            <span class="menu-text">Search</span>
                        </a>
                    </li>
                </ul><!-- .app-menu -->
            </div><!-- .menubar-scroll-inner -->
        </div><!-- .menubar-scroll -->
    </aside>

    <!-- Include necessary JavaScript libraries -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>