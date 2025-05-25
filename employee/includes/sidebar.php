<aside id="menubar" class="menubar light">
  <div class="app-user">
    <div class="media">
      <div class="media-left">
        <div class="avatar avatar-md avatar-circle">
          <?php
          $eid = $_SESSION['odlmseid'];
          // Fetch profile photo from database
          $sql = "SELECT ProfilePhoto, Name, Email FROM tblemployee WHERE ID = :eid";
          $query = $dbh->prepare($sql);
          $query->bindParam(':eid', $eid, PDO::PARAM_STR);
          $query->execute();
          $result = $query->fetch(PDO::FETCH_OBJ);
          
          if (!empty($result->ProfilePhoto) && file_exists('employeeprofile/'.$result->ProfilePhoto)) {
              echo '<img class="img-responsive" src="employeeprofile/'.htmlspecialchars($result->ProfilePhoto).'" alt="Profile Picture">';
          } else {
              echo '<img class="img-responsive" src="assets/images/default-avatar.jpg" alt="Default Avatar">';
          }
          ?>
        </div><!-- .avatar -->
      </div>
      <div class="media-body">
        <div class="foldable">
          <?php
          $email = $result->Email;   
          $fname = $result->Name;
          ?>
          <h5><a href="javascript:void(0)" class="username"><?php echo htmlspecialchars($fname); ?></a></h5>
          <ul>
            <li class="dropdown">
              <a href="javascript:void(0)" class="dropdown-toggle usertitle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <small><?php echo htmlspecialchars($email); ?></small>
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
          <a href="view-testdetail.php">
            <i class="menu-icon zmdi zmdi-layers zmdi-hc-lg"></i>
            <span class="menu-text">View Test Detail</span>
           </a>
        </li>

       <li class="has-submenu">
          <a href="javascript:void(0)" class="submenu-toggle">
            <i class="menu-icon zmdi zmdi-pages zmdi-hc-lg"></i>
            <span class="menu-text">Assign Appointment</span>
            <i class="menu-caret zmdi zmdi-hc-sm zmdi-chevron-right"></i>
          </a>
          <ul class="submenu">
            <li><a href="assigned-approved-appointment.php"><span class="menu-text">New Appointment</span></a></li>
            <li><a href="ontheway-appointment.php"><span class="menu-text">On the Way Appointment</span></a></li>
            <li><a href="sample-collected.php"><span class="menu-text">Sample Collected</span></a></li>
            <li><a href="sample-sent-to-lab.php"><span class="menu-text">Sent to Lab</span></a></li>
            <li><a href="total-appointment.php"><span class="menu-text">Total Appointment</span></a></li>
          </ul>
        </li>
        <li>
          <a href="search.php">
            <i class="menu-icon zmdi zmdi-search zmdi-hc-lg"></i>
            <span class="menu-text">Search</span>
          </a>
        </li>
        <li>
          <a href="appointment-collection-reports.php">
            <i class="menu-icon zmdi zmdi-layers zmdi-hc-lg"></i>
            <span class="menu-text">Report</span>
          </a>
        </li>
        <!-- Enable 2fa -->
        <li>
            <a href="security_settings.php" style="display: flex; align-items: center; gap: 10px;">
              <i class="zmdi zmdi-shield-security" style="font-size: 18px;"></i>
              <span class="menu-text">Enable 2-Factor Authentication</span>
            </a>
        </li>
      </ul><!-- .app-menu -->
    </div><!-- .menubar-scroll-inner -->
  </div><!-- .menubar-scroll -->
</aside>

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
    transition: all 0.3s ease;
}
.avatar-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.avatar-circle:hover {
    border-color: #4CAF50;
    transform: scale(1.05);
}
</style>