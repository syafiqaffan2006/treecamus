<!DOCTYPE html>
<?php 
session_start();
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

if(isset($_POST['search'])){
  $tagID= $_POST['tagID']; }


?>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php browsertitle(); ?></title>
  <link REL="SHORTCUT ICON" HREF="../images/ugc-logo.png.">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.2/dist/leaflet.css"
    integrity="sha256-sA+zWATbFveLLNqWO2gtiw3HL/lh1giY/Inf1BJ0z14="
    crossorigin=""/>

    <script src="https://unpkg.com/leaflet@1.9.2/dist/leaflet.js"
    integrity="sha256-o9N1jGDZrf5tS+Ft4gbIK7mYMipq9lqpVJ91xHSyKhg="
    crossorigin=""></script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Font Awesome -->
  <!--<link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">-->
  <!-- Ionicons -->
  <!--<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">-->
  <!-- Tempusdominus Bootstrap 4 
  <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">-->
  <!-- iCheck-->
  <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap 
  <link rel="stylesheet" href="../plugins/jqvmap/jqvmap.min.css"> -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.2/css/all.css">
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
    <a class="brand-link">
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
            <h1 class="m-0">Tree Visualization Maps</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a  href="../dashboard/dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Map</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->



    <!-- Main content -->
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete=off>
   <section class="content">
      <div class="container-fluid">

        </div>
        <!-- /.row -->
        <!-- Main row -->
        <div class="col-12">
          <div class="col-12 ">
           <div class="card">
             <div class="card-header bg-primary">
               <h3 class="card-title">Maps</h3> 
             </div>          

            <div class="card-body" >
               <div class="input-group mb-3">
                <input type="text" class="form-control" name="tagID" id="tagID" placeholder="Enter Tag ID to see the location of tree" aria-describedby="button-addon2" required>
                <button type="submit" name="search" class="btn btn-primary">Search</button>
               </div>
              <div id="map" style="width: 100%; height: 580px"></div>
            </div>
           </div>
          </div>
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
   </form>
    <!-- /.content -->
  </div>
  <!-- /.bootstrap failed delete modal -->
  <div class="modal fade" id="confirm-edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-warning">
            
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirm Delete</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>                    
                </div>
            
                <div class="modal-body">
                    <p>You are about to delete this report.</p>
                    <p>Do you want to proceed?</p>
                    <!--<p class="debug-url"></p>-->
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-danger btn-ok">Yes</a>
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
<!-- ChartJS 
<script src="../plugins/chart.js/Chart.min.js"></script> -->
<!-- Sparkline
<script src="../plugins/sparklines/sparkline.js"></script>  -->
<!-- JQVMap 
<script src="../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../plugins/jqvmap/maps/jquery.vmap.usa.js"></script> -->


<!-- Tempusdominus Bootstrap 4 
<script src="../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script> -->

<!-- overlayScrollbars -->
<script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.js"></script>

<!-- AdminLTE dashboard demo (This is only for demo purposes) 
<script src="../dist/js/pages/dashboard.js"></script> -->
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

var map = L.map('map').setView([3.0733, 101.5185], 12);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

var layerGroup = L.layerGroup().addTo(map);


    layerGroup.clearLayers();
    map.closePopup();
    
   
    <?php 
    $data=["tagID" => $tagID];
    $sql = "SELECT tr.latitude,tr.longitude,tr.treeName, sp.speciestitle, fm.familytitle, z.zonetitle FROM trees tr JOIN species sp ON tr.speciesID=sp.speciesID
            JOIN family fm ON fm.familyID=tr.familyID JOIN `zone` z ON z.zoneID=tr.zoneID WHERE tr.tagID= :tagID ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount(); ?>

<?php if($rowCount > 0) { ?>
        <?php while($d = $stmt->fetch(PDO::FETCH_ASSOC)){ ?>
            var marker = L.marker([<?php echo  $d['latitude']?>,<?php echo $d['longitude'] ?>]).addTo(layerGroup); 
             /* marker.bindPopup('').openPopup(); */

             let template=
             
             '<?php echo '<p><b>Tree Name:</b> '.$d['treeName'].'</p>'?>
              <?php echo '<p><b>Coordinate:</b> '.$d['latitude'].' , '.$d['longitude'].' </p>'?>
              <?php echo '<p><b>Family Name:</b> '.$d['familytitle'].'</p>'?>
              <?php echo '<p><b>Species Name:</b> '.$d['speciestitle'].'</p>'?>
              <?php echo '<p><b>Zone:</b> '.$d['zonetitle'].'</p>'?>'

             marker.bindPopup(template).openPopup();
        <?php } ?>
<?php }  ?>
</script>
</body>
</html>
