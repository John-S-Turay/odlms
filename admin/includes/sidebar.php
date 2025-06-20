<aside id="menubar" class="menubar light">
    <div class="app-user">
        <div class="media">
            <div class="media-left">
                <div class="avatar avatar-md avatar-circle">
                    <a href="javascript:void(0)">
                        <?php
                        $aid = $_SESSION['odlmsaid'];
                        $sql = "SELECT AdminName, Email, ProfilePhoto FROM tbladmin WHERE ID = :aid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':aid', $aid, PDO::PARAM_STR);
                        $query->execute();
                        $result = $query->fetch(PDO::FETCH_OBJ);
                        
                        if (!empty($result->ProfilePhoto) && file_exists('adminprofile/'.$result->ProfilePhoto)) {
                            echo '<img class="img-responsive" src="adminprofile/'.htmlspecialchars($result->ProfilePhoto).'" alt="Profile Photo"/>';
                        } else {
                            echo '<img class="img-responsive" src="assets/images/images.png" alt="Default Avatar"/>';
                        }
                        ?>
                    </a>
                </div><!-- .avatar -->
            </div>
            <div class="media-body">
                <div class="foldable">
                    <h5><a href="javascript:void(0)" class="username"><?php echo htmlspecialchars($result->AdminName); ?></a></h5>
                    <ul>
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropdown-toggle usertitle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <small><?php echo htmlspecialchars($result->Email); ?></small>
                                <span class="caret"></span>
                            </a>
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
                                        <span>logout</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div><!-- .media-body -->
        </div><!-- .media -->
    </div><!-- .app-user -->

  <div class="menubar-scroll">
    <div class="menubar-scroll-inner">
      <ul class="app-menu">
        <li class="has-submenu">
          <a href="dashboard.php">
            <i class="menu-icon zmdi zmdi-view-dashboard zmdi-hc-lg"></i>
            <span class="menu-text">Dashboard</span>
            
          </a>
       
        </li>
        
        <li class="has-submenu">
          <a href="javascript:void(0)" class="submenu-toggle">
            <i class="menu-icon zmdi zmdi-layers zmdi-hc-lg"></i>
            <span class="menu-text">Test</span>
            <i class="menu-caret zmdi zmdi-hc-sm zmdi-chevron-right"></i>
          </a>
          <ul class="submenu">
            <li><a href="add-test.php"><span class="menu-text">Add Test </span></a></li>
            <li><a href="manage-test.php"><span class="menu-text">Manage Tests</span></a></li>
          </ul>
        </li>
        <li class="has-submenu">
          <a href="javascript:void(0)" class="submenu-toggle">
            <i class="menu-icon zmdi zmdi-layers zmdi-hc-lg"></i>
            <span class="menu-text">Lab Emplooyee</span>
            <i class="menu-caret zmdi zmdi-hc-sm zmdi-chevron-right"></i>
          </a>
          <ul class="submenu">
            <li><a href="add-lab-emp.php"><span class="menu-text">Add Emplooyee</span></a></li>
            <li><a href="manage-lab-emp.php"><span class="menu-text">Manage Emplooyee</span></a></li>
          </ul>
        </li>

        <li class="has-submenu">
          <a href="javascript:void(0)" class="submenu-toggle">
            <i class="menu-icon zmdi zmdi-puzzle-piece zmdi-hc-lg"></i>
            <span class="menu-text">Appointments</span>
            <i class="menu-caret zmdi zmdi-hc-sm zmdi-chevron-right"></i>
          </a>
          <ul class="submenu">
            <li><a href="new-appointment.php"><span class="menu-text">New</span></a></li>
            <li><a href="approved-appointment.php"><span class="menu-text">Approved</span></a></li>
            <li><a href="rejected-appointment.php"><span class="menu-text">Rejected</span></a></li>
            <li><a href="usercancel-appointment.php"><span class="menu-text">User Cancelled</span></a></li>
            <li><a href="manage_availability.php"><span class="menu-text">Manage Availability</span></a></li>
          </ul>
        </li>

        <li class="has-submenu">
          <a href="javascript:void(0)" class="submenu-toggle">
            <i class="menu-icon zmdi zmdi-pages zmdi-hc-lg"></i>
            <span class="menu-text">Lab</span>
            <i class="menu-caret zmdi zmdi-hc-sm zmdi-chevron-right"></i>
          </a>
          <ul class="submenu">
            <li><a href="sample-received.php"><span class="menu-text">Sample Received</span></a></li>
            <li><a href="uploaded-reports.php"><span class="menu-text">Uploaded Reports </span></a></li>
            <li><a href="view-reports.php"><span class="menu-text">View Reports </span></a></li>
          </ul>
        </li>
<li>
          <a href="view-regusers.php">
            <i class="menu-icon zmdi zmdi-search zmdi-hc-lg"></i>
            <span class="menu-text">View Reg Users</span>
          </a>
        </li>
        <li>
            <a href="security_settings.php" style="display: flex; align-items: center; gap: 10px;">
              <i class="zmdi zmdi-shield-security" style="font-size: 18px;"></i>
              <span class="menu-text">Enable 2-Factor Authentication</span>
            </a>
        </li>
        <li>
          <a href="search.php">
            <i class="menu-icon zmdi zmdi-search zmdi-hc-lg"></i>
            <span class="menu-text">Search</span>
          </a>
        </li>
<li class="has-submenu">
          <a href="javascript:void(0)" class="submenu-toggle">
            <i class="menu-icon zmdi zmdi-pages zmdi-hc-lg"></i>
            <span class="menu-text">Report</span>
            <i class="menu-caret zmdi zmdi-hc-sm zmdi-chevron-right"></i>
          </a>
          <ul class="submenu">
            <li><a href="appointment-bwdates.php"><span class="menu-text">B/w date Appointment Reports</span></a></li>
            <li><a href="sales-report.php"><span class="menu-text">Sales Report </span></a></li>
            <li><a href="empwise-report.php"><span class="menu-text">Employeewise Report </span></a></li>
           
          </ul>
        </li>
      </ul><!-- .app-menu -->
    </div><!-- .menubar-scroll-inner -->
  </div><!-- .menubar-scroll -->
</aside>