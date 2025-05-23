<nav id="app-navbar" class="navbar navbar-inverse navbar-fixed-top primary">
    <!-- Navbar Header -->
    <div class="navbar-header">
        <!-- Toggle button for mobile view (left) -->
        <button type="button" id="menubar-toggle-btn" class="navbar-toggle visible-xs-inline-block navbar-toggle-left hamburger hamburger--collapse js-hamburger">
            <span class="sr-only">Toggle navigation</span>
            <span class="hamburger-box"><span class="hamburger-inner"></span></span>
        </button>

        <!-- Toggle button for navbar collapse (right) -->
        <button type="button" class="navbar-toggle navbar-toggle-right collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="zmdi zmdi-hc-lg zmdi-more"></span>
        </button>

        <!-- Toggle button for search (right) -->
        <button type="button" class="navbar-toggle navbar-toggle-right collapsed" data-toggle="collapse" data-target="#navbar-search" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="zmdi zmdi-hc-lg zmdi-search"></span>
        </button>

        <!-- Brand Logo -->
        <a href="dashboard.php" class="navbar-brand">
            <span class="brand-icon"><i class="fa fa-gg"></i></span>
            <span class="brand-name">ODLMS</span>
        </a>
    </div><!-- .navbar-header -->

    <!-- Navbar Container -->
    <div class="navbar-container container-fluid">
        <!-- Navbar Collapse -->
        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <!-- Left Side Toolbar -->
            <ul class="nav navbar-toolbar navbar-toolbar-left navbar-left">
                <!-- Fold Button for Menubar -->
                <li class="hidden-float hidden-menubar-top">
                    <a href="javascript:void(0)" role="button" id="menubar-fold-btn" class="hamburger hamburger--arrowalt is-active js-hamburger">
                        <span class="hamburger-box"><span class="hamburger-inner"></span></span>
                    </a>
                </li>
                <!-- Page Title -->
                <li>
                    <h5 class="page-title hidden-menubar-top hidden-float">Dashboard</h5>
                </li>
            </ul>

            <!-- Right Side Toolbar -->
            <ul class="nav navbar-toolbar navbar-toolbar-right navbar-right">
                <!-- Notifications Dropdown (New Appointments) -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="zmdi zmdi-hc-lg zmdi-notifications"></i>
                        <?php
                        // Fetch new appointments with null status
                        $sql = "SELECT * FROM tblappointment WHERE Status IS NULL";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        $totalAppointments = $query->rowCount();
                        ?>
                        <?php if ($totalAppointments > 0): ?>
                            <span class="notification-badge"><?php echo $totalAppointments; ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Notification Dropdown Menu -->
                    <div class="media-group dropdown-menu animated flipInY">
                        <?php foreach ($results as $row): ?>
                            <a href="view-appointment-detail.php?editid=<?php echo htmlspecialchars($row->ID); ?>&aptid=<?php echo htmlspecialchars($row->AppointmentNumber); ?>" class="media-group-item">
                                <div class="media">
                                    <div class="media-left">
                                        <div class="avatar avatar-xs avatar-circle">
                                            <img src="assets/images/images.png" alt="">
                                            <i class="status status-online"></i>
                                        </div>
                                    </div>
                                    <div class="media-body">
                                        <h5 class="media-heading">New Appointment</h5>
                                        <small class="media-meta"><?php echo htmlspecialchars($row->AppointmentNumber); ?> at (<?php echo htmlspecialchars($row->PostDate); ?>)</small>
                                    </div>
                                </div>
                            </a><!-- .media-group-item -->
                        <?php endforeach; ?>
                    </div>
                </li>

                <!-- Notifications Dropdown (Sample Received) -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="zmdi zmdi-hc-lg zmdi-notifications"></i>
                        <?php
                        // Fetch appointments with status 'Delivered to Lab'
                        $sql = "SELECT * FROM tblappointment WHERE Status = 'Delivered to Lab'";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        $totalSamples = $query->rowCount();
                        ?>
                        <?php if ($totalSamples > 0): ?>
                            <span class="notification-badge"><?php echo $totalSamples; ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Notification Dropdown Menu -->
                    <div class="media-group dropdown-menu animated flipInY">
                        <?php foreach ($results as $row): ?>
                            <a href="view-samplereceived-detail.php?editid=<?php echo htmlspecialchars($row->ID); ?>&aptid=<?php echo htmlspecialchars($row->AppointmentNumber); ?>" class="media-group-item">
                                <div class="media">
                                    <div class="media-left">
                                        <div class="avatar avatar-xs avatar-circle">
                                            <img src="assets/images/images.png" alt="">
                                            <i class="status status-online"></i>
                                        </div>
                                    </div>
                                    <div class="media-body">
                                        <h5 class="media-heading">Sample Received</h5>
                                        <small class="media-meta"><?php echo htmlspecialchars($row->AppointmentNumber); ?> at (<?php echo htmlspecialchars($row->PostDate); ?>)</small>
                                    </div>
                                </div>
                            </a><!-- .media-group-item -->
                        <?php endforeach; ?>
                    </div>
                </li>

                <!-- Settings Dropdown -->
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
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
        </div><!-- .navbar-collapse -->
    </div><!-- .navbar-container -->
</nav>