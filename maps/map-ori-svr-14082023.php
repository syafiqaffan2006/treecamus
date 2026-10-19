<!DOCTYPE html>
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

### map: php to leaflet through json ###
$sql = "SELECT tr.latitude,tr.longitude,tr.treeName, sp.speciestitle, fm.familytitle FROM trees tr JOIN species sp ON tr.speciesID=sp.speciesID
JOIN family fm ON fm.familyID=tr.familyID";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$rowCount = $stmt->rowCount(); 

if($rowCount > 0)
{
   $marker = array();

   while($d = $stmt->fetch(PDO::FETCH_ASSOC))
   {
      $marker[] = array("lat"=>$d["latitude"], "lon"=>$d["longitude"], "Details"=>$d['speciestitle']);
   }
   //echo json_encode($marker);
}
else
  $marker = array();
### end ###

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
   <section class="content">
      <div class="container-fluid">
        <!-- Main row -->
        <div class="col-12">
          <div class="col-12 ">
           <div class="card">
             <div class="card-header bg-primary">
               <h3 class="card-title">Google Map</h3> 
             </div>          

            <div class="card-body" >
              <div id="googlemap" style="width: 100%; height: 580px"></div>
            </div>
           </div>
          </div>
        </div>
        <!-- /.row (main row) -->
         <!-- Main row -->
         <div class="col-12">
          <div class="col-12 ">
           <div class="card">
             <div class="card-header bg-primary">
               <h3 class="card-title">Leaflet Map</h3> 
             </div>          

            <div class="card-body" >
              <div id="leafletmap" style="width: 100%; height: 580px"></div>
            </div>
           </div>
          </div>
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>

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

<!-- overlayScrollbars -->
<script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.js"></script>
</body>
</html>
<!-- load leaflet libraries -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script type="text/javascript">
// load google map
// source: https://stackoverflow.com/questions/5210904/google-map-infowindow-closure
function initMap() 
{
  var data2 = <?php echo json_encode($marker); ?>;
  var mapDiv = document.getElementById('googlemap');

  var map = new google.maps.Map(mapDiv, {
    center: { lat: 3.0697706, lng: 101.5014936 },
    zoom: 15
  });

  map.data.loadGeoJson(
    "uitm.geojson"
  );

  map.data.setStyle({
    fillColor: '#957DAD',
    fillOpacity: .1,
    strokeColor: '#957DAD',
    strokeWeight: 0.9
  });

  var markerImage = new google.maps.MarkerImage(
    '../images/forest.png',
    new google.maps.Size(30,30), //size
    null, //origin
    null, //anchor
    new google.maps.Size(30,30) //scale
  );

  for(var i = 0; i < data2.length; i++)
  {
    (function() {
        var marker = new google.maps.Marker({
          position: new google.maps.LatLng(data2[i].lat, data2[i].lon),
          map: map,
          icon: markerImage
        });
        var treename = '<div><p><b>Species:</b></p></div>';
        var infowindow = new google.maps.InfoWindow({
          content: treename + data2[i].Details
        });
        marker.addListener('mouseover', function() {
          infowindow.open(map, marker);
        });
        marker.addListener('mouseout', function() {
          infowindow.close(map, marker);
        });
    })();
  }
}
</script>
<!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyChA3MDnj9Y0jm_iOWxHnN_AIzFCBavWWc&callback=initMap"></script> -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDgpfZ5MjM0QyNvO5aYqjTkz-wB352Ij70&callback=initMap"></script>
<script> 
// Leaflet Map
var map = L.map('leafletmap').setView([3.0733, 101.5185], 12);

var LeafIcon = L.Icon.extend({
    options: {
      //shadowUrl: '../images/palm-tree.png',
      iconSize:     [60, 60],
      shadowSize:   [50, 64],
      iconAnchor:   [22, 94],
      shadowAnchor: [4, 62],
      popupAnchor:  [-3, -76]
    }
});

var greenIcon = new LeafIcon({
    iconUrl: '../images/forest.png',
    //shadowUrl: 'http://leafletjs.com/examples/custom-icons/leaf-shadow.png'
});

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

var city = L.markerClusterGroup();
var data = <?php echo json_encode($marker); ?>;

$.getJSON("uitm.geojson",function(data){
  // add GeoJSON layer to the map once the file is loaded
  var datalayer = L.geoJson(data ,{
    onEachFeature: function(feature, featureLayer) {
      featureLayer.bindPopup("<html><body><h6>Subzone Info</h6><table border=''><tr><th>Zone Name</th><td>"+feature.properties.Zoning_nam+"</td></tr><tr><th>Shape Length</th><td>"+feature.properties.Shape_Leng+"</td></tr><tr><th>Shape Area</th><td>"+feature.properties.Shape_Area+"</td></tr></table></body></htm>");
    },
    stroke: true,
    weight: 1,
    color: '#957DAD',
    fillColor: '#957DAD',
    fillOpacity: '.1'
  }).addTo(map);
  map.fitBounds(datalayer.getBounds());
});

for (var i = 0; i < data.length; i++) 
{
   var new_location = new L.LatLng(data[i].lat, data[i].lon);
   var place = data[i].Details;
   var marker = new L.Marker(new_location, {
      title: place,
      icon: greenIcon
   });
   var message = 'Species: '+place;
   marker.bindPopup(message);
   city.addLayer(marker);
}
map.addLayer(city);
</script>
<script>
  // $(function () {
  //   $("#example1").DataTable({
  //     "responsive": true, 
  //     "lengthChange": false, 
  //     "autoWidth": false,
  //     "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
  //   }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

  //   $("#example2").DataTable({
  //     "responsive": true, 
  //     "lengthChange": false, 
  //     "autoWidth": false,
  //     "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
  //   }).buttons().container().appendTo('#example2_wrapper .col-md-6:eq(0)');

  // });
</script>