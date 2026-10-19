<?php 

    require_once('../function/function.php');
    require_once('../connection/connection.php');
    // Insert Tree Measurement Function
    $treeid = $_POST['tid'];
    $ovrHeight = $_POST['OHeight'];
    $circumference = $_POST['CFerence'];
    $crwnWidth = $_POST['CWidth'];
    $measurementtoken = getToken(32);
    $created_at = $dbh->query("SELECT NOW()")->fetchColumn();
    $data = [
        ':treeid' => $treeid,
        ':ovrHeight' => $ovrHeight,
        ':circumference' => $circumference,
        ':crwnWidth' => $crwnWidth,
        ':measurementtoken' => $measurementtoken,
        ':created_at' => $created_at
    ];

    $sql = "INSERT INTO treemeasurement (treeID,overalheight,circumference,crownwidth,measurementtoken,created_at)
            VALUES(:treeid, :ovrHeight, :circumference, :crwnWidth, :measurementtoken, :created_at)";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
?>