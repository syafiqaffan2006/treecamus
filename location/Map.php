<?php

require 'connection.php';
require 'function.php';


/* function showLoc($dbh)
{
    
    $sql = "SELECT latitude,longitude FROM `tb data` ";
    //echo $sql;
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();

    if($rowCount > 0){
        $i=0;
        $loc= array();
        while($d = $stmt->fetch(PDO::FETCH_ASSOC)){
            $loc[$i]= $d['latitude'].",".$d['longitude'];
            echo "<tr>";
            echo "<td>" .$loc[$i]. "</td>";
            echo "<td><button onclick='testFunction(".$d['latitude'].",".$d['longitude'].")' type='button' name='view' class='btn btn-info' title='View'><i class='fa fa-eye'></i></button></td>";
            echo "</tr>";
            $i++;

        }
        return $loc;
    }
}  */


?>

<!DOCTYPE html>

<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>MAP</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.2/dist/leaflet.css"
    integrity="sha256-sA+zWATbFveLLNqWO2gtiw3HL/lh1giY/Inf1BJ0z14="
    crossorigin=""/>

    <script src="https://unpkg.com/leaflet@1.9.2/dist/leaflet.js"
    integrity="sha256-o9N1jGDZrf5tS+Ft4gbIK7mYMipq9lqpVJ91xHSyKhg="
    crossorigin=""></script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
</head>

<body>

     <div id="map" style="width: 80%; height: 580px"></div>
    <!-- <script src="scripts.js"></script> -->
</body>
</html>

<script> 

var map = L.map('map').setView([3.072038, 101.491613], 15);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

var layerGroup = L.layerGroup().addTo(map);


    layerGroup.clearLayers();
    map.closePopup();

    <?php $sql = "SELECT latitude,longitude FROM `tb data`  ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount(); ?>

<?php if($rowCount > 0) { ?>
        <?php while($d = $stmt->fetch(PDO::FETCH_ASSOC)){ ?>
            var marker = L.marker([<?php echo  $d['latitude']?>,<?php echo $d['longitude'] ?>]).addTo(layerGroup); 
            /* marker.bindPopup("Spiderman").openPopup(); */
        <?php } ?>
<?php }  ?>

    /* marker.bindPopup("Spiderman").openPopup() */;

</script>