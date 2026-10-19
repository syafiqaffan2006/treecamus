<!DOCTYPE html>

<style>
.required {
  color: red;
} 
</style>

<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("../connection/connection.php");
include("../function/function.php");
include("../settings/settings.php");

if(isset($_SESSION['userislogged']) && $_SESSION['userislogged'] != 1){

   header("Location: ../index.php");
 }
 
 if(!isset($_SESSION['login_id']) && !isset($_SESSION['user_id']))
 {
   header("Location: ../index.php");
 }
 else
 {
      $google_id= $_SESSION['login_id']; 

      $data = ["id" =>  $google_id];
      $get_user = "SELECT * FROM users u JOIN useraccess ua ON u.userID=ua.userID JOIN roles r ON ua.roleID=r.roleID  WHERE u.google_id=:id";
      $stmt = $dbh->prepare($get_user);
      $stmt->execute($data);
      $rowCount = $stmt->rowCount();

      if($rowCount > 0){
         $user = $stmt->fetch(PDO::FETCH_ASSOC);
      }
      else{
         exit;
      }
 }


if(isset($_POST['save'])){

   $userID = getUsersID($dbh, $google_id);
   $treeName=$_POST['treeName'];
   $latitude=$_POST['latitude'];
   $longitude=$_POST['longitude'];
   $campus= $_POST['campus'];
   $zone = $_POST['zone'];//optional
   $family =  $_POST['family']; //optional
   $species =  $_POST['species']; //optional
   $treestatus=$_POST['status'];
   $tagID= $_POST['tagID']; //permanent tag id for a tree
   $censorID= $_POST['censorID']; //censor id that insert at tags
   $arborist=$_POST['arborist'];
   $treetoken=getToken(36);
   $imagetoken=getToken(32);
   $treeID=microtime(true).uniqid();
   $tagstoken=getToken(38);
 

  if(TagidRecord($dbh,$tagID)== true)
  {
   header("Location: addtree.php?warning"); 
  }
  else if(CensoridRecord($dbh,$censorID)==true){
   header("Location: addtree.php?warning2");
  }
  else{
   saveTree($dbh,$treeName,$treeID, $latitude, $longitude, $userID,$arborist, $campus,$zone,$family,$species,$treestatus, $treetoken, $tagID);

   if($_FILES['evidence']['name'][0] != "")
   {
       $i = 0;
       while($i < count($_FILES['evidence']['name']))
       {
           $temp = explode(".", $_FILES['evidence']['name'][$i]);
           $newfilename[$i] = microtime(true) . '.' . end($temp);
           move_uploaded_file($_FILES['evidence']['tmp_name'][$i], "../evidences/" . $newfilename[$i]);

           saveevidence($dbh, $newfilename[$i], $_FILES['evidence']['type'][$i], $treeID, $imagetoken);   
           $i++;
       }    
   }
  
   if($censorID!=NULL){
      savetag($dbh, $censorID, $treeID, $tagstoken);
   }

   header("Location: listtree.php?success"); 
  }

 }

?>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?php browsertitle(); ?></title>
      <link REL="SHORTCUT ICON" HREF="../images/ugc-logo.png.">
      <script src="camera.js" type="module"></script>
      <!-- Google Font: Source Sans Pro -->
      <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
      <!-- Font Awesome -->
      <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
      <!-- Select2 -->
      <link rel="stylesheet" href="../plugins/select2/css/select2.min.css">
      <!-- Ionicons -->
      <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
      <!-- daterange picker -->
      <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker.css">
      <!-- Tempusdominus Bootstrap 4 
         <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">-->
      <!-- iCheck 
         <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">-->
      <!-- JQVMap 
         <link rel="stylesheet" href="../plugins/jqvmap/jqvmap.min.css">-->
      <!-- summernote -->
      <link rel="stylesheet" href="../plugins/summernote/summernote-bs4.min.css">
      <!-- Theme style -->
      <link rel="stylesheet" href="../dist/css/adminlte.min.css">
      <!-- overlayScrollbars -->
      <link rel="stylesheet" href="../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
      <!-- DataTables -->
      <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
      <link rel="stylesheet" href="../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
      <link rel="stylesheet" href="../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
   </head>
   <body class="hold-transition sidebar-mini layout-fixed">
      <div class="wrapper">
         <!-- Preloader -->
         <!-- <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="../dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
         </div> -->
         <!-- Navbar -->
         <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
               <li class="nav-item">
                  <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
               </li>
               <li class="nav-item d-none d-sm-inline-block">
                  <a href="dashboard.php" class="nav-link">Home</a>
               </li>
            </ul>
            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
               <!-- Navbar Search -->
               <!-- <li class="nav-item">
                  <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                    <i class="fas fa-search"></i>
                  </a>
                  <div class="navbar-search-block">
                    <form class="form-inline">
                      <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                          <button class="btn btn-navbar" type="submit">
                            <i class="fas fa-search"></i>
                          </button>
                          <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                            <i class="fas fa-times"></i>
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                  </li> -->
               <!-- Messages Dropdown Menu -->
               <!-- <li class="nav-item dropdown">
                  <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-comments"></i>
                    <span class="badge badge-danger navbar-badge">3</span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="#" class="dropdown-item"> -->
               <!-- Message Start -->
               <!--    <div class="media">
                  <img src="dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                  <div class="media-body">
                    <h3 class="dropdown-item-title">
                      Brad Diesel
                      <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                    </h3>
                    <p class="text-sm">Call me whenever you can...</p>
                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                  </div>
                  </div> -->
               <!-- Message End -->
               <!-- </a>
                  <div class="dropdown-divider"></div>
                  <a href="#" class="dropdown-item"> -->
               <!-- Message Start -->
               <!--  <div class="media">
                  <img src="dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                  <div class="media-body">
                    <h3 class="dropdown-item-title">
                      John Pierce
                      <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                    </h3>
                    <p class="text-sm">I got your message bro</p>
                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                  </div>
                  </div> -->
               <!-- Message End -->
               <!-- </a>
                  <div class="dropdown-divider"></div>
                  <a href="#" class="dropdown-item"> -->
               <!-- Message Start -->
               <!--  <div class="media">
                  <img src="dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                  <div class="media-body">
                    <h3 class="dropdown-item-title">
                      Nora Silvester
                      <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                    </h3>
                    <p class="text-sm">The subject goes here</p>
                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                  </div>
                  </div> -->
               <!-- Message End -->
               <!--  </a>
                  <div class="dropdown-divider"></div>
                  <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                  </div>
                  </li> -->
               <!-- Notifications Dropdown Menu -->
               <li class="nav-item">
                  <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                  <i class="fas fa-expand-arrows-alt"></i>
                  </a>
               </li>
               <li class="nav-item dropdown">
                  <a class="nav-link" data-toggle="dropdown" href="#">    
                  <span><?php if(isset($_SESSION['userfullname'])) { echo $_SESSION['userfullname'];} ?></span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                     <div class="dropdown-divider"></div>
                     <a href="../logout.php" class="dropdown-item dropdown-footer">Sign Out</a>
                  </div>
               </li>
            </ul>
         </nav>
         <!-- /.navbar -->
         <!-- Main Sidebar Container -->
         <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="../dashboard/dashboard.php" class="brand-link">
            <img src="../images/logo UGC.png" alt="AdminLTE Logo" class="brand-image img-responsive elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">TREE CaMUs</span>
            </a>
             <!-- Sidebar -->
               <div class="sidebar">
                  <!-- Sidebar user panel (optional) -->
                  <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                  <div class="image">
                     <img src="<?php if(isset( $_SESSION['userislogged'])) { echo $user['profile_image'];} ?>" >
                  </div>
                  <div class="info">
                     <a href="#" class="d-block"><?php echo $user['name']; ?></a>
                     <a href="#" class="d-block font-weight-light"><?php echo $user['roletitle']; ?></a>
                  </div>
               </div>
               <!-- SidebarSearch Form -->
               <div class="form-inline">
                  <div class="input-group" data-widget="sidebar-search">
                     <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                     <div class="input-group-append">
                        <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                        </button>
                     </div>
                  </div>
               </div>
               <!-- Sidebar Menu -->
               <nav class="mt-2">
                  <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                     <?php include("../links/links.php"); ?>
                  </ul>
               </nav>
               <!-- /.sidebar-menu --> 
            </div>
            <!-- /.sidebar -->
         </aside>
         <!-- Content Wrapper. Contains page content -->
         <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
               <div class="container-fluid">
                  <div class="row mb-2">
                     <div class="col-sm-6">
                        <h1 class="m-0">Add Tree</h1>
                     </div>
                     <!-- /.col -->
                     <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active"><a href="../dashboard/dashboard.php">Home</a></li>
                        <li class="breadcrumb-item">Add Tree</li>
                        </ol>
                     </div>
                     <!-- /.col -->
                  </div>
                  <!-- /.row -->
               </div>
               <!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->
            <!-- Main content -->
            <section class="content">
               <div class="container-fluid">
               </div>
               <!-- /.row -->
               <!-- Main row -->
               <div class="col-12">
                  <div class="col-14">
                        <div class="card card-outline card-primary">
                           <div class="card-header text-center p-0 pt-1">
                              <!-- <img src="./images/logo UGC.png" alt="UGC" width="270" height="100"></a> -->
                              <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                 <li class="nav-item">
                                    <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill" href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home" aria-selected="true">Add Tree Using GPS</a>
                                 </li>
                            
                              </ul>
                           </div>
                           <div class="card-body">
                              <div class="tab-content" id="custom-tabs-one-tabContent">
                                 <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel" aria-labelledby="custom-tabs-one-home-tab">
                                    <b>
                                       <p class="login-box-msg" style="background-color: #74EBD5;
                                          background-image: linear-gradient(90deg, #74EBD5 0%, #9FACE6 100%); color:#fff; font-size:25px; text-shadow: 0 0 3px #000000; border-radius:5px;
                                          ">Add New Tree</p>
                                    </b>
                                    <p class="login-box-msg">Please Fill In Information Below</p>
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"  enctype="multipart/form-data" autocomplete=off>
                                       <div class="row">
                                       <div class="col-12">
                                             <div class="form-group">
                                                <label>Tree Name<span class="required">*</span></label>
                                                <input type="text" class="form-control" name="treeName" required>
                                             </div>
                                          </div>
                                          <div class="col-12 col-sm-3">
                                             <div class="form-group">
                                                <label>Latitude<span class="required">*</span></label>
                                                <input type="text" id="latitude" name="latitude" class="form-control"  required >
                                             </div>
                                          </div>
                                          <div class="col-12 col-sm-3 ">
                                             <div class="form-group">
                                                <label>Longitude<span class="required">*</span></label>
                                                <input type="text" id="longitude" name="longitude" class="form-control" required >
                                             </div>
                                          </div>
                                          <div class="col-12  col-sm-3">
                                             <div class="form-group">
                                                <label>Get Coordinate</label>
                                                <button type="button" class="btn btn-primary btn-block" onclick="getLocation()"> Get Coordinate</button> 
                                             </div>
                                          </div>

                                          <div class="col-12  col-sm-3">
                                             <div class="form-group">
                                                <label>Clear Coordinate</label>
                                                <button type="button" class="btn btn-primary btn-block btn-warning" onclick="clearCoordinate()">Clear Coordinate</button>
                                             </div>
                                          </div>
                                          
                                          <div class="col-12">
                                             <div class="form-group">
                                                <label for="campus">campus<span class="required">*</span></label>
                                                <select class="select2" name="campus"  data-placeholder="Select a campus" style="width: 100%;" required>
                                                  <option></option>   
                                                  <?php getCampusName($dbh); ?>
                                                </select>
                                             </div>
                                          </div> 
                                          <div class="col-12 col-sm-6">
                                             <div class="form-group">
                                                <label>Zone</label>
                                                <select class="form-control" name="zone">
                                                <option value="" selected disabled hidden>Select a Zone </option>
                                                <?php getZoneName($dbh); ?>
                                                </select>
                                             </div>
                                          </div>
                                          <div class="col-12 col-sm-6">
                                             <div class="form-group">
                                                <label>Family</label>
                                                <select class="form-control" name="family">
                                                <option value="" selected disabled hidden>Select a family </option>
                                                <?php getFamilyName($dbh); ?>
                                                </select>
                                             </div>
                                          </div>
                                          <div class="col-12 col-sm-6">
                                             <div class="form-group">
                                                <label>Species</label>
                                                <select class="form-control" name="species">
                                                <option value="" selected disabled hidden>Select a species </option>
                                                <?php getSpeciesName($dbh); ?>
                                                </select>
                                             </div>
                                          </div>
                                          <div class="col-12 col-sm-6">
                                             <div class="form-group">
                                                <label> Tree Status<span class="required">*</span></label>
                                                <select class="form-control" name="status" required>
                                                <option value="" selected disabled hidden>Select a status</option>
                                                <?php getTreeStatusName($dbh); ?>
                                                </select>
                                             </div>
                                          </div>
                                           <div class="col-12 col-sm-6">
                                             <div class="form-group">
                                                <label>Arborist<span class="required">*</span></label>
                                                <select class="form-control" name="arborist" required>
                                                <option value="" selected disabled hidden>Select an arborist</option>
                                                 <?php getArboristIsAllowedName($dbh); ?>
                                                </select>
                                             </div>
                                          </div> 

                                          <div class="col-12 col-sm-6">
                                             <div class="form-group">
                                                <label>Tag ID<span class="required">*</span></label>
                                                <input type="text" class="form-control" name="tagID" required>
                                             </div>
                                          </div> 

                                          <div class="col-12 col-sm-6">
                                             <div class="form-group">
                                                <label>Sensor ID</label>
                                                <input type="text" class="form-control" name="censorID">
                                             </div>
                                          </div> 

                                          <div class="col-12 col-sm-6">
                                             <div class="form-group">
                                                <label for="signatureInput">File input<em> (PNG, JPEG)</em></label>
                                                <div class="input-group">
                                                   <div class="custom-file">
                                                      <input type="file" name="evidence[]" class="custom-file-input form control" id="evidence" multiple data-show-upload="true" data-show-caption="true" />
                                                      <label class="custom-file-label" for="evidence" >Choose file</label>
                                                   </div>
                                                </div>
                                             </div>
                                          </div>

                                          
                                          <div class="col-12 col-sm-3">
                                             <div class="form-group">
                                             <label for="camera-list" >Select camera:</label>
                                             <select id="camera-list" class="form-control"></select>
                                             </div>
                                          </div>
                                          
                                          <div class="col-12 col-sm-3">
                                             <div class="form-group" >
                                             <label>Start Camera</label>
                                             <button id="start-button" class="form-control btn btn-primary btn-block btn-primary">Start Camera</button>
                                             </div>
                                          </div>
                                          <div class="col-12 col-sm-3">
                                             <div class="form-group" >
                                             <label>Stop Camera</label>
                                             <button id="stop-button" class="form-control btn btn-primary btn-block btn-danger" >Stop Camera</button>
                                             </div>
                                          </div>
                                          <div class="col-12  col-sm-3">
                                             <div class="form-group" >
                                             <label>Capture Image</label>
                                             <button id="capture-button" class="form-control btn btn-primary btn-block btn-success">Capture Image</button>
                                             </div>
                                          </div>

                                       <div class="col-12 ">
                                          <div class="form-group">

                                          <div id="collapseOne" class="collapse show" data-parent="#accordion">
                                                   <div class="card-body">
                                                      <div class="example">
                                                         <div class="tab-content rounded-bottom">
                                                         <div class="tab-pane p-3 active preview table-responsive" role="tabpanel" id="preview-91">
                                                             
                                                               <table id="example" class="table table-bordered">
                                                                  <thead style="background: #ffd700;">
                                                                     <tr>
                                                                     <th scope="col">Camera</th>
                                                                     <th scope="col">Camera Snap</th>
                                                                  
                                                                     </tr>
                                                                  </thead>
                                                                  <tbody>
                                                                     <tr>
                                                                     <td scope="col"><video id="camera-preview" autoplay></video></td>
                                                                     <td scope="col"><canvas id="image-canvas"></canvas></td>
                                                                  
                                                                     </tr>
                                                                  </tbody>
                                                               </table>
                                                         </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                </div>
                                                </div>  

                                          <div class="card-footer">
                                          <button type="submit" name="save" class="btn btn-info" id="btnSave">Save</button>
                                          <button type="button" class="btn btn-secondary" id="btnCancel" onclick='window.location="listtree.php";'>Cancel</button>
                                        </div>
                                                                  
                                       </div>
                                    </form>
                                 </div>
                 
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- /.row (main row) -->
         </div>
         <!-- /.container-fluid -->
         </section>
         <!-- /.content -->
      </div>

      <!--bootstrap file upload alert modal-->
  <div class="modal fade" id="modal-warning">
    <div class="modal-dialog">
      <div class="modal-content bg-warning">
        <div class="modal-header">
          <h4 class="modal-title">Warning</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p>Invalid file type!.</p>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>

<!--bootstrap file upload alert modal-->
<div class="modal fade" id="modal-warning1">
    <div class="modal-dialog">
      <div class="modal-content bg-warning">
        <div class="modal-header">
          <h4 class="modal-title">Warning</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p>The tag ID already exist. Please insert the new tag ID!.</p>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="modal-warning2">
    <div class="modal-dialog">
      <div class="modal-content bg-warning">
        <div class="modal-header">
          <h4 class="modal-title">Warning</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p>The censor ID already exist. Please insert the new censor ID!.</p>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>

      <!-- /.content-wrapper -->
      <footer class="main-footer">
         <strong><?php copyright(); ?></strong>
         <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 3.2.0
         </div>
      </footer>
      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
         <!-- Control sidebar content goes here -->
      </aside>
      <!-- /.control-sidebar -->
      </div>
      <!-- ./wrapper -->
      <!-- jQuery -->
      <script src="../plugins/jquery/jquery.min.js"></script>
      <!-- jQuery UI 1.11.4 -->
      <script src="../plugins/jquery-ui/jquery-ui.min.js"></script>
      <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
      <script>
         $.widget.bridge('uibutton', $.ui.button)
      </script>
      <!-- Bootstrap 4 -->
      <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
      <!-- Select2 -->
      <script src="../plugins/select2/js/select2.full.min.js"></script>
      <!-- bs-custom-file-input -->
      <script src="../plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
      <!-- date-range-picker -->
      <script src="../plugins/daterangepicker/daterangepicker.js"></script>
      <script src="../plugins/moment/moment.min.js"></script>
      <!-- ChartJS
         <script src="../plugins/chart.js/Chart.min.js"></script> -->
      <!-- Sparkline 
         <script src="../plugins/sparklines/sparkline.js"></script>-->
      <!-- JQVMap
         <script src="../plugins/jqvmap/jquery.vmap.min.js"></script>
         <script src="../plugins/jqvmap/maps/jquery.vmap.usa.js"></script> -->
      <!-- Tempusdominus Bootstrap 4 
         <script src="../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>-->
      <!-- overlayScrollbars -->
      <script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
      <!-- AdminLTE App -->
      <script src="../dist/js/adminlte.js"></script>
      <!-- AdminLTE dashboard demo (This is only for demo purposes) 
         <script src="../dist/js/pages/dashboard.js"></script>-->
      <!-- Summernote -->
      <script src="../plugins/summernote/summernote-bs4.min.js"></script>
      <!-- DataTables  & Plugins -->
      <script src="../plugins/datatables/jquery.dataTables.min.js"></script>
      <script src="../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
      <script src="../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
      <script src="../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
      <script src="../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
      <script src="../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
      <script src="../plugins/jszip/jszip.min.js"></script>
      <script src="../plugins/pdfmake/pdfmake.min.js"></script>
      <script src="../plugins/pdfmake/vfs_fonts.js"></script>
      <script src="../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
      <script src="../plugins/datatables-buttons/js/buttons.print.min.js"></script>
      <script src="../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
      <!-- Page specific script -->

<script>
var x = document.getElementById("demo");

function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition, showError);
  } else { 
    x.innerHTML = "Geolocation is not supported by this browser.";
  }
}

function clearCoordinate() {
   document.getElementById("latitude").value='';
   document.getElementById("longitude").value='';
}

function showPosition(position) {
  // x.innerHTML = "Latitude: " + position.coords.latitude + 
  // "<br>Longitude: " + position.coords.longitude + " Timestamp: " + 
  // position.timestamp + " Altitude: " + position.coords.altitude;

  // document.getElementById("coordinate").value = position.coords.latitude + 
  // ", " + position.coords.longitude

  document.getElementById("latitude").value = position.coords.latitude;

  document.getElementById("longitude").value = position.coords.longitude;
}

function showError(error) {
  switch(error.code) {
    case error.PERMISSION_DENIED:
      x.innerHTML = "User denied the request for Geolocation."
      break;
    case error.POSITION_UNAVAILABLE:
      x.innerHTML = "Location information is unavailable."
      break;
    case error.TIMEOUT:
      x.innerHTML = "The request to get user location timed out."
      break;
    case error.UNKNOWN_ERROR:
      x.innerHTML = "An unknown error occurred."
      break;
  }
}
</script>


<script>
$(function () {
    //Initialize Select2 Elements
    $('.select2').select2();});
</script>

<script>
$(function () {
  bsCustomFileInput.init();
});
</script>

<script>
//source:https://stackoverflow.com/questions/4234589/validation-of-file-extension-before-uploading-file/4237161
$(function () {
    $('input[type=file]').change(function () {
        var val = $(this).val().toLowerCase(),
            regex = new RegExp("(.*?)\.(pdf|png|PNG|jpg|jpeg|JPG|JPEG)$");

        if (!(regex.test(val))) {
            $(this).val('');
            //alert('Please select correct file format');
            $("#modal-warning").modal("show");
        }
    });
});  
</script>
  
<?php if (isset($_GET['warning'])){ ?>
    <script type="text/javascript">
    $(document).ready(function(){
        $("#modal-warning1").modal("show");
    });
    </script>
<?php } ?>

<?php if (isset($_GET['warning2'])){ ?>
    <script type="text/javascript">
    $(document).ready(function(){
        $("#modal-warning2").modal("show");
    });
    </script>
<?php } ?>
</body>
</html>