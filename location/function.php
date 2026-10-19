<?php

function saveLoc($dbh,  $latitude, $longitude)
{
    $data = ["latitude"=> $latitude, "longitude"=> $longitude];
    $sql = "INSERT INTO `tb data`(latitude, longitude) VALUES ( :latitude, :longitude)";
    //echo $sql;
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
}

/* function getMapID($dbh){

   
    $sql = "SELECT MapID FROM `tb data` ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();

    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['MapID'];
    }

}  */


?>