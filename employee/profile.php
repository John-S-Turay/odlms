<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['odlmseid']==0)) {
  header('location:logout.php');
  } else{
    if(isset($_POST['submit']))
  {
    $eid=$_SESSION['odlmseid'];
    $name=$_POST['name'];
  $mobno=$_POST['mobilenumber'];
  $email=$_POST['email'];
  $address=$_POST['address'];
  $profilePhoto = null;
  
  // File upload handling
  $uploadDir = __DIR__ . '/employeeprofile/';

       // Verify/Create directory
       if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            die('<script>alert("Failed to create upload directory")</script>');
        }
    } elseif (!is_writable($uploadDir)) {
        die('<script>alert("Upload directory is not writable")</script>');
    }

    // Process file upload if a file was selected
    if (!empty($_FILES['profilephoto']['name'])) {
        $fileName = basename($_FILES['profilephoto']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExt, $allowedTypes)) {
            if ($_FILES['profilephoto']['size'] <= 2097152) { // 2MB limit
                $newFileName = 'emp_' . $eid . '_' . time() . '.' . $fileExt;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($_FILES['profilephoto']['tmp_name'], $targetPath)) {
                    $profilePhoto = $newFileName;
                    
                    // Delete old photo if exists
                    $oldPhotoQuery = $dbh->prepare("SELECT ProfilePhoto FROM tblemployee WHERE ID = :eid");
                    $oldPhotoQuery->bindParam(':eid', $eid, PDO::PARAM_STR);
                    $oldPhotoQuery->execute();
                    $oldPhoto = $oldPhotoQuery->fetchColumn();
                    
                    if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
                        unlink($uploadDir . $oldPhoto);
                    }
                } else {
                    $error = error_get_last();
                    error_log("File move error: " . print_r($error, true));
                    echo '<script>alert("Error saving file. Please try again.")</script>';
                }
            } else {
                echo '<script>alert("File size exceeds 2MB limit")</script>';
            }
        } else {
            echo '<script>alert("Only JPG, PNG, GIF files are allowed")</script>';
        }
    }
      // Build update query
      $sql = "UPDATE tblemployee SET Name = :name, MobileNumber = :mobilenumber, Email = :email, Address = :address";
        
      if ($profilePhoto !== null) {
          $sql .= ", ProfilePhoto = :profilephoto";
      }
      
      $sql .= " WHERE ID = :eid";
      
      $query = $dbh->prepare($sql);
      $query->bindParam(':name', $name, PDO::PARAM_STR);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->bindParam(':mobilenumber', $mobno, PDO::PARAM_STR);
      $query->bindParam(':address', $address, PDO::PARAM_STR);
      
      if ($profilePhoto !== null) {
          $query->bindParam(':profilephoto', $profilePhoto, PDO::PARAM_STR);
      }
      
      $query->bindParam(':eid', $eid, PDO::PARAM_STR);

      if ($query->execute()) {
          echo '<script>alert("Profile has been updated")</script>';
          // Refresh to show changes
          echo '<script>window.location.href = window.location.href;</script>';
      } else {
          $error = $query->errorInfo();
          echo '<script>alert("Error updating profile: ' . $error[2] . '")</script>';
      }

  }
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  
  <title>ODLMS - Employee Profile</title>
  
  <link rel="stylesheet" href="libs/bower/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="libs/bower/material-design-iconic-font/dist/css/material-design-iconic-font.css">
  <!-- build:css assets/css/app.min.css -->
  <link rel="stylesheet" href="libs/bower/animate.css/animate.min.css">
  <link rel="stylesheet" href="libs/bower/fullcalendar/dist/fullcalendar.min.css">
  <link rel="stylesheet" href="libs/bower/perfect-scrollbar/css/perfect-scrollbar.css">
  <link rel="stylesheet" href="assets/css/bootstrap.css">
  <link rel="stylesheet" href="assets/css/core.css">
  <link rel="stylesheet" href="assets/css/app.css">
  <!-- endbuild -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900,300">
  <style>
        .current-photo img {
            border-radius: 50%;
            border: 3px solid #eee;
            object-fit: cover;
            width: 150px;
            height: 150px;
        }
        .current-photo {
            text-align: center;
            margin-bottom: 15px;
        }
        #imagePreviewContainer {
            display: none;
            margin-bottom: 15px;
            text-align: center;
        }
        #imagePreview {
            border-radius: 50%;
            border: 3px solid #eee;
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
        }
        .custom-file-label::after {
            content: "Browse";
        }
    </style>
  <script src="libs/bower/breakpoints.js/dist/breakpoints.min.js"></script>
  <script>
    Breakpoints();
  </script>
</head>
  
<body class="menubar-left menubar-unfold menubar-light theme-primary">
<!--============= start main area -->

<?php include_once('includes/header.php');?>

<?php include_once('includes/sidebar.php');?>

<!-- APP MAIN ==========-->
<main id="app-main" class="app-main">
  <div class="wrap">
  <section class="app-content">
    <div class="row">
     
      <div class="col-md-12">
        <div class="widget">
          <header class="widget-header">
            <h3 class="widget-title">Employee Profile</h3>
          </header><!-- .widget-header -->
          <hr class="widget-separator">
          <div class="widget-body">
            <?php
$eid=$_SESSION['odlmseid'];
$sql="SELECT * from  tblemployee where ID=:eid";
$query = $dbh -> prepare($sql);
$query->bindParam(':eid',$eid,PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>
            <form class="form-horizontal" method="post" enctype="multipart/form-data">

                  <!-- Profile Photo Field -->
                  <div class="form-group">
                      <label class="col-sm-3 control-label">Profile Photo:</label>
                      <div class="col-sm-9">
                          <?php if (!empty($row->ProfilePhoto)): ?>
                              <div class="current-photo">
                                  <img src="employeeprofile/<?php echo htmlspecialchars($row->ProfilePhoto); ?>" 
                                        alt="Current Profile Photo" 
                                        id="currentProfilePhoto">
                              </div>
                          <?php else: ?>
                              <div class="current-photo">
                                  <img src="assets/images/default-avatar.jpg" 
                                        alt="Default Profile Photo" 
                                        id="currentProfilePhoto">
                              </div>
                          <?php endif; ?>
                          
                          <div id="imagePreviewContainer">
                              <img id="imagePreview" src="#" alt="Preview">
                          </div>
                          
                          <div class="custom-file">
                              <input type="file" class="custom-file-input" id="profilePhotoInput" name="profilephoto" accept="image/*">
                              <label class="custom-file-label" for="profilePhotoInput">Choose new profile photo</label>
                          </div>
                          <small class="text-muted">Allowed formats: JPG, PNG, GIF (Max 2MB)</small>
                      </div>
                  </div>
              <div class="form-group">
                <label for="exampleTextInput1" class="col-sm-3 control-label">Employee ID:</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="exampleTextInput1" name="" value="<?php  echo $row->EmpID;?>" readonly='true'>
                </div>
              </div>
              <div class="form-group">
                <label for="exampleTextInput1" class="col-sm-3 control-label">Name:</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="exampleTextInput1" name="name" value="<?php  echo $row->Name;?>" required='true'>
                </div>
              </div>
              <div class="form-group">
                <label for="email2" class="col-sm-3 control-label">Address:</label>
                <div class="col-sm-9">
                  <textarea type="text" class="form-control" id="address" name="address" value="<?php  echo $row->UserName;?>" required="true"><?php  echo $row->Address;?></textarea>
                </div>
              </div>
              <div class="form-group">
                <label for="email2" class="col-sm-3 control-label">Email:</label>
                <div class="col-sm-9">
                  <input type="email" class="form-control" id="email" name="email" value="<?php  echo $row->Email;?>" required='true'>
                </div>
              </div>
               <div class="form-group">
                <label for="email2" class="col-sm-3 control-label">Contact Number:</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="email2" name="mobilenumber" value="<?php  echo $row->MobileNumber;?>" required='true' maxlength='10'>
                </div>
              </div>
              <div class="form-group">
                <label for="email2" class="col-sm-3 control-label">Joining Date:</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="email2" name="" value="<?php  echo $row->JoiningDate;?>" readonly="true">
                </div>
              </div>
             <?php $cnt=$cnt+1;}} ?>
              <div class="row">
                <div class="col-sm-9 col-sm-offset-3">
                  <button type="submit" class="btn btn-success" name="submit">Update</button>
                </div>
              </div>
            </form>
          </div><!-- .widget-body -->
        </div><!-- .widget -->
      </div><!-- END column -->

    </div><!-- .row -->
  </section><!-- #dash-content -->
</div><!-- .wrap -->
  <!-- APP FOOTER -->
  <?php include_once('includes/footer.php');?>
  <!-- /#app-footer -->
</main>
<!--========== END app main -->

<?php include_once('includes/customizer.php');?>
  
  <!-- SIDE PANEL -->
 

  <!-- build:js assets/js/core.min.js -->
  <script src="libs/bower/jquery/dist/jquery.js"></script>
  <script src="libs/bower/jquery-ui/jquery-ui.min.js"></script>
  <script src="libs/bower/jQuery-Storage-API/jquery.storageapi.min.js"></script>
  <script src="libs/bower/bootstrap-sass/assets/javascripts/bootstrap.js"></script>
  <script src="libs/bower/jquery-slimscroll/jquery.slimscroll.js"></script>
  <script src="libs/bower/perfect-scrollbar/js/perfect-scrollbar.jquery.js"></script>
  <script src="libs/bower/PACE/pace.min.js"></script>
  <!-- endbuild -->

  <!-- build:js assets/js/app.min.js -->
  <script src="assets/js/library.js"></script>
  <script src="assets/js/plugins.js"></script>
  <script src="assets/js/app.js"></script>
  <!-- endbuild -->
  <script src="libs/bower/moment/moment.js"></script>
  <script src="libs/bower/fullcalendar/dist/fullcalendar.min.js"></script>
  <script src="assets/js/fullcalendar.js"></script>
  
  <script>
    // Image preview functionality
    document.getElementById('profilePhotoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewContainer = document.getElementById('imagePreviewContainer');
        const previewImage = document.getElementById('imagePreview');
        const currentPhoto = document.getElementById('currentProfilePhoto');
        
        if (file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG, PNG, GIF)');
                e.target.value = '';
                return;
            }
            
            // Validate file size (2MB)
            if (file.size > 2097152) {
                alert('File size exceeds 2MB limit');
                e.target.value = '';
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.style.display = 'block';
                
                if (currentPhoto) {
                    currentPhoto.style.display = 'none';
                }
            }
            
            reader.readAsDataURL(file);
            
            // Update the file input label
            const fileName = file.name;
            const nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        } else {
            previewContainer.style.display = 'none';
            if (currentPhoto) {
                currentPhoto.style.display = 'block';
            }
            e.target.nextElementSibling.innerText = 'Choose new profile photo';
        }
    });
    </script>
</body>
</html>
<?php }  ?>