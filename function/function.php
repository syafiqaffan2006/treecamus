<?php 

function getToken($length)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    $codeAlphabet.= "-_.~";  // Special characters allowed in url
    $max = strlen($codeAlphabet);

    for ($i=0; $i < $length; $i++) 
    {
        $token .= $codeAlphabet[rand(0, $max-1)]; //random_int() -php7, rand() - php5
    }

    return $token;
}

function getTimestamp()
{
    $timestamp = date("Y-m-d H:i:s");

    return $timestamp;
}

function checktreeFamilyRecord($dbh, $id)
{
    $found = false;
    $data = ["id" => $id];
    $sql = "SELECT * FROM family WHERE familytoken = :id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $found = true;    
    }
    
    return $found;
}

function checkAllTreerecord($dbh, $id)
{
    $found = false;
    $data = ["id" => $id];
    $sql = "SELECT * FROM trees WHERE BINARY treetoken = :id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $found = true;    
    }
    
    return $found;
}

function checktreeSpeciesRecord($dbh, $id)
{
    $found = false;
    $data = ["id" => $id];
    $sql = "SELECT * FROM species WHERE speciestoken = :id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $found = true;    
    }
    
    return $found;
}

function getisDisplayFamily($dbh, $token)
{
    $data = ["token" =>  $token];
    $sql = "SELECT * FROM family WHERE familytoken= :token";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['is_display'];
    }
}

function gettagID($dbh, $token)
{
    $data = ["token" =>  $token];
    $sql = "SELECT tagID FROM trees WHERE treetoken= :token";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        if($d['tagID']==NULL){
            echo "No tag ID";
        }
        else{
            return $d['tagID'];
        }
    }
}

function getcensorID($dbh, $treeID)
{
    $data = ["treeID" =>  $treeID];
    $sql = "SELECT tagID FROM tags WHERE treesID= :treeID AND tagstatusID=1";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        
            return $d['tagID'];
        
    }
}

function getcensorIDAndStatus($dbh, $treeID)
{
    $data = ["treeID" =>  $treeID];
    $sql = "SELECT tg.tagID,tg.tagstatusID, tgs.tagstatustitle FROM  tags tg JOIN tagstatus tgs ON tg.tagstatusID=tgs.tagstatusID
            WHERE BINARY tg.treesID = :treeID";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $count = 0;
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['tagID']."</td>";
                echo "<td>".$d['tagstatustitle']."</td>";
            echo "</tr>";
            $count++;
        }
        
    }
}

function getTagIDRecord($dbh,$treeID) 
{
    $data = ["treeID" =>  $treeID];
    $sql = "SELECT tagID FROM tags WHERE treesID= :treeID";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        return $d['tagID'];
    }
}

function TagidRecord($dbh,$tagID)
{
    $found = false;
    $data = ["tagID" => $tagID];
    $sql = "SELECT tagID FROM trees WHERE tagID = :tagID";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $found = true;    
    }
    
    return $found;
}

function CensorIDStatusRecord($dbh,$treeID)
{
    $found = false;
    $data = ["treeID" => $treeID];
    $sql = "SELECT tagstatusID FROM tags WHERE treesID = :treeID AND tagstatusID=1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $found = true;    
    }
    
    return $found;
}

function CensoridRecord($dbh,$censorID)
{
    $found = false;
    $data = ["tagID" => $censorID];
    $sql = "SELECT tagID FROM tags WHERE tagID = :tagID";
   
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $found = true;    
    }
    
    return $found;
}

function getTagstoken($dbh)
{
    $sql = "SELECT tagstoken FROM tags";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

            return $d['tagstoken'];
    }
}

function getFamilyIdForSpecies($dbh, $token)
{
    $data = ["token" =>  $token];
    $sql = "SELECT familyID FROM species WHERE speciestoken = :token";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        return $d['familyID'];
    }
}

function getSelectedTreeName($dbh, $token)
{
    $familyID = getFamilyIdForSpecies($dbh, $token);
    $sql = "SELECT f.familyID, f.familytitle FROM family f";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            if($d['familyID'] == $familyID)
                echo "<option value='".$d['familyID']."' selected>".$d['familytitle']."</option>";  
            else
                echo "<option value='".$d['familyID']."'>".$d['familytitle']."</option>";
        }
    }
}

function getSpeciesLocalName($dbh, $token)
{
    $data = ["token" =>  $token];
    $sql = "SELECT specieslocalname FROM species WHERE speciestoken= :token";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['specieslocalname'];
    }
}

function getisDisplaySpecies($dbh, $token)
{
    $data = ["token" =>  $token];
    $sql = "SELECT * FROM species WHERE speciestoken= :token";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['is_display'];
    }
}

function getFamtreeTitle($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT familytitle FROM family
            WHERE BINARY familytoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['familytitle'];
    }
}

function getSpeciestreeTitle($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT speciestitle FROM species
            WHERE BINARY speciestoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['speciestitle'];
    }
}

function getFamilyName($dbh)
{
    $sql = "SELECT familyID, familytitle FROM family  WHERE is_display=1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<option value='".$d['familyID']."'>".$d['familytitle']."</option>";
        }
    }
} 

function getTreeName($dbh,$token)
{
    $data = ["treetoken" => $token];
    $sql = "SELECT COALESCE(treeUniqueCode, 'No Tree Unique Code') AS treeName
            FROM trees
            WHERE treetoken = :treetoken
            ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['treeName'];
    }
} 

function getSpeciesName($dbh)
{
    $sql = "SELECT speciesID, speciestitle FROM species WHERE is_display=1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<option value='".$d['speciesID']."'>".$d['speciestitle']."</option>";
        }
    }
} 

function getZoneName($dbh)
{
    $sql = "SELECT zoneID, zonetitle FROM `zone`";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<option value='".$d['zoneID']."'>".$d['zonetitle']."</option>";
        }
    }
} 

function getTreeStatusName($dbh)
{
    $sql = "SELECT treestatusID, treestatustitle FROM treestatus";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<option value='".$d['treestatusID']."'>".$d['treestatustitle']."</option>";
        }
    }
}

function getCampusName($dbh)
{
    $sql = "SELECT campusID, campustitle FROM campus";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<option value='".$d['campusID']."'>".$d['campusID']." - ".$d['campustitle']."</option>";
        }
    }
}

function getUsersID($dbh, $google_id)
{
    $data = ["google_id" => $google_id];
    $sql = "SELECT userID FROM users
            WHERE BINARY google_id = :google_id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        return $d['userID'];
        
    }
}

function getTreeID($dbh,$token)
{
    $data = ["token" => $token];
    $sql = "SELECT treeID FROM trees WHERE BINARY treetoken= :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        return $d['treeID'];
        
    }
}

function getArboristIsAllowedName($dbh)
{
    $sql = "SELECT u.userID, u.name, ua.roleID FROM users u JOIN useraccess ua ON u.userID = ua.userID
            WHERE u.is_allow =1 AND ua.is_allow=1 AND ua.roleID=4";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<option value='".$d['userID']."'> ".$d['name']."</option>";
        }
    }
}

function getTreeCoordinate($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT latitude,longitude FROM trees
            WHERE BINARY treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return "".$d['latitude']." , ".$d['longitude']."";
    }
}

function getcampusNameSelected($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT c.campusID, c.campustitle FROM campus c JOIN trees ts ON
            ts.campusID=c.campusID WHERE BINARY ts.treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['campustitle'];
    }
}

function getcampusIDSelected($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT campusID FROM  trees
            WHERE BINARY treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['campusID'];
    }
}

function getzoneIDSelected($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT zoneID FROM  trees
            WHERE BINARY treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['zoneID'];
    }
}

function getfamilyIDSelected($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT familyID FROM  trees
            WHERE BINARY treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['familyID'];
    }
}

function getspeciesIDSelected($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT speciesID FROM  trees
            WHERE BINARY treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['speciesID'];
    }
}

function getTreestatusIDSelected($dbh, $token){

    $data = ["token" => $token];
    $sql = "SELECT treestatusID FROM  trees
            WHERE BINARY treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['treestatusID'];
    }

}

function getTagStatusIDSelected($dbh, $treeID){ 

    $data = ["treeID" => $treeID];
        $sql = "SELECT tagstatusID FROM tags 
                WHERE BINARY treesID = :treeID AND tagstatusID = 1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['tagstatusID'];
    }

}

function getTagStatusNameSelected($dbh, $treeID){ 

    $data = ["treeID" => $treeID];
    $sql = "SELECT tgs.tagstatusID, tgs.tagstatustitle FROM  tagstatus tgs JOIN tags tg ON tg.tagstatusID=tgs.tagstatusID
            WHERE BINARY tg.treesID = :treeID AND tg.tagstatusID=1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['tagstatustitle'];
    }

}

function getArboristIDSelected($dbh, $token){

    $data = ["token" => $token];
    $sql = "SELECT user_id_arborist FROM  trees
            WHERE BINARY treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['user_id_arborist'];
    }

}

function showlistCampusAndSelectedCampus($dbh, $campusID)
{
    $sql = "SELECT * FROM campus ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            
            if($d['campusID'] == $campusID)
            {
                echo "<option value='".$d['campusID']."' selected>".$d['campusID']." - ".$d['campustitle']."</option>";
            }
            else
            {
                echo "<option value='".$d['campusID']."'>".$d['campusID']." - ".$d['campustitle']."</option>";
            }          
            
        }
    }
}

function showlistZoneAndSelectedZone($dbh, $zoneID)
{
    $sql = "SELECT * FROM `zone` ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            
            if($d['zoneID'] == $zoneID)
            {
                echo "<option value='".$d['zoneID']."' selected> ".$d['zonetitle']."</option>";
            }
            else
            {
                echo "<option value='".$d['zoneID']."'>".$d['zonetitle']."</option>";
            }          
            
        }
    }
}

function showlistfamilyAndSelectedfamily($dbh, $familyID)
{
    $sql = "SELECT * FROM family ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            
            if($d['familyID'] == $familyID)
            {
                echo "<option value='".$d['familyID']."' selected> ".$d['familytitle']."</option>";
            }
            else
            {
                echo "<option value='".$d['familyID']."'>".$d['familytitle']."</option>";
            }          
            
        }
    }
}

function showlistspeciesAndSelectedspecies($dbh, $speciesID)
{
    $sql = "SELECT * FROM species ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            
            if($d['speciesID'] == $speciesID)
            {
                echo "<option value='".$d['speciesID']."' selected> ".$d['speciestitle']."</option>";
            }
            else
            {
                echo "<option value='".$d['speciesID']."'>".$d['speciestitle']."</option>";
            }          
            
        }
    }
}

function showlistTreeStatusAndSelectedTreeStatus($dbh,  $treestatusID)
{
    $sql = "SELECT * FROM treestatus ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            
            if($d['treestatusID'] == $treestatusID)
            {
                echo "<option value='".$d['treestatusID']."' selected> ".$d['treestatustitle']."</option>";
            }
            else
            {
                echo "<option value='".$d['treestatusID']."'>".$d['treestatustitle']."</option>";
            }          
            
        }
    }
}

function showlistTagstatusAndSelectedTagStatus($dbh,  $tagStatusID) 
{
    $sql = "SELECT * FROM tagstatus";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            
            if($d['tagstatusID'] ==  $tagStatusID)
            {
                echo "<option value='".$d['tagstatusID']."'selected>".$d['tagstatustitle']."</option>";
            }
            else
            {
                echo "<option value='".$d['tagstatusID']."'>".$d['tagstatustitle']."</option>";
            }          
            
        }
    }
}

function showlistArboristAndSelectedArborist($dbh, $arboristID)
{
    $sql = "SELECT us.roleID,u.userID,us.userID, u.name FROM useraccess us JOIN users u ON u.userID=us.userID
             WHERE us.roleID=4";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            
            if($d['userID'] == $arboristID)
            {
                echo "<option value='".$d['userID']."' selected> ".$d['name']."</option>";
            }
            else
            {
                echo "<option value='".$d['userID']."'>".$d['name']."</option>";
            }          
            
        }
    }
}

function getZoneNameSelected($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT z.zoneID, z.zonetitle FROM `zone` z JOIN trees ts ON
            ts.zoneID=z.zoneID WHERE BINARY ts.treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['zonetitle'];
    }
}

function getfamilyNameSelected($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT f.familyID, f.familytitle FROM family f JOIN trees ts ON
            ts.familyID=f.familyID WHERE BINARY ts.treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['familytitle'];
    }
}

function getSpeciesNameSelected($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT s.speciesID, s.speciestitle FROM species s JOIN trees ts ON
            ts.speciesID=s.speciesID WHERE BINARY ts.treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['speciestitle'];
    }
}

function getTreestatusSelected($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT trs.treestatusID, trs.treestatustitle FROM treestatus trs JOIN trees ts ON
            ts.treestatusID=trs.treestatusID WHERE BINARY ts.treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['treestatustitle'];
    }
}

function getArboristSelected($dbh, $token)  
{
    $data = ["token" => $token];
    $sql = "SELECT u.userID, u.name FROM users u JOIN trees ts ON
            ts.user_id_arborist = u.userID WHERE BINARY ts.treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();

    if ($rowCount > 0) {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (empty($d['name'])) {
            return "No Arborist";
        } else {
            return $d['name'];
        }
    }

    return "No Arborist"; 
}


function gettagStatusSelected($dbh, $treeID)
{
    $data = ["treeID" => $treeID];
    $sql = "SELECT ts.tagstatustitle FROM tags tg
            JOIN tagstatus ts ON tg.tagstatusID=ts.tagstatusID
            WHERE BINARY tg.treesID= :treeID";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['tagstatustitle'];
    }
}

function displayfiletypeicon($filetype)
{
    $icon = "";
    switch($filetype)
    {
        case "image/png":
        case "image/PNG":
        case "image/jpg":
        case "image/JPG":
        case "image/jpeg":
        case "image/JPEG":
            $icon = "far fa-file-image";
            break;
        case "application/pdf":
        case "application/PDF":
            $icon = "far fa-file-pdf";
            break;
        default:
            $icon = "far fa-file";
    }
    
    return $icon;
}

function getFile($dbh, $token)
{
    $data = ["token" => $token]; 
    $sql = "SELECT * FROM images im JOIN trees ts ON 
            im.treeID = ts.treeID WHERE BINARY ts.treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $count = 0;
        $dir = "../evidences/";
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['imageFileName']."</td>";
                echo "<td><i class='".displayfiletypeicon($d['imageFileType'])."'></i> ".$d['imageFileType']."</td>";
                echo "<td><button type='button' name='view' class='btn btn-info' title='View' onclick=\"window.open('".$dir.$d['imageFileName']."', '_blank');\"><i class='fa fa-eye'></i></button></td>";
            echo "</tr>";
            $count++;
        }
    }
}

function getImage($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT * FROM images im JOIN trees ts ON
            im.treeID = ts.treeID WHERE BINARY ts.treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if ($rowCount > 0) {
        $count = 0;
        $dir = "../evidences/";
        while ($d = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . ($count + 1) . "</td>";
            echo "<td>" . "Image " . ($count + 1) . "</td>";
            echo "<td><button type='button' name='view' class='btn btn-info view-image' title='View' data-image='" . $d['imageFileName'] . "'><i class='fa fa-eye'></i></button></td>";
            echo "</tr>";
            $count++;
        }
    }
}

function getPrimaryImageForTreeViewScreen($dbh, $treeID)
{
    $query = "SELECT imageID, imageFileName FROM images WHERE treeID = :treeID AND imagePrimary = 1";
    $stmt = $dbh->prepare($query);
    $data = ['treeID' => $treeID];
    $stmt->execute($data);

    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        $imageFileName = $image['imageFileName'];
        return '<img src="../evidences/' . $imageFileName . '" alt="Tree Image" class="img-fluid h-50">';
    } else {
    
        return '<img src="../dist/img/default_error_img_map/no_img_found.png' . '" alt="Tree Image" class="img-fluid h-50">';
    }
}

function getCreatedAt($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT created_at FROM trees WHERE BINARY treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if ($rowCount > 0) {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        $createdAt = date("F j, Y H:i:s", strtotime($d['created_at']));

        return $createdAt;
    }
}

function getlatitude($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT latitude FROM trees WHERE BINARY treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['latitude'];
    }
}

function getlongitude($dbh, $token)
{
    $data = ["token" => $token];
    $sql = "SELECT longitude FROM trees WHERE BINARY treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['longitude'];
    }
}

function getFileForEdit($dbh, $treeID)
{
    $data = ["treeID" => $treeID]; 
    $sql = "SELECT * FROM images im JOIN trees ts ON 
            im.treeID = ts.treeID WHERE BINARY im.treeID= :treeID";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $count = 0;
        $dir = "../evidences/";
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['imageFileName']."</td>";
                echo "<td><i class='".displayfiletypeicon($d['imageFileType'])."'></i> ".$d['imageFileType']."</td>";
                echo "<td><button type='button' name='view' class='btn btn-info' title='View' onclick=\"window.open('".$dir.$d['imageFileName']."', '_blank');\"><i class='fa fa-eye'></i></button></td>";
                echo "<td><button type='button' name='btndelete' id='btndelete' class='btn btn-danger confirm-delete' title='Delete' data-href='/treecamus/tree/edit/".$d['treetoken']."?fid=".$d['imageID']."' data-toggle='modal' data-target='#delete'><i class='far fa-trash-alt'></i></button></td>"; 
            echo "</tr>";
            $count++;
        }
    }
}

function getevidencefilename($dbh, $imageID)
{
    $data = ["imageID" => $imageID];
    $sql = "SELECT imageFileName FROM images WHERE imageID = :imageID";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['imageFileName'];
    }
}

function isThumbnailDeleted($dbh, $treeID, $deletedImageID)
{
    $data = ["treeID" => $treeID, "deletedImageID" => $deletedImageID];
    $sql = "SELECT imagePrimary FROM images WHERE BINARY treeID = :treeID AND imageID = :deletedImageID AND imagePrimary = 1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    return $stmt->rowCount() > 0;
}

function findNewThumbnailIndex($images, $deletedImageID)
{
    foreach ($images as $index => $image) {
        if ($image['imageID'] != $deletedImageID) {
            return $index; 
        }
    }
    return false;
}

function deleteSpecificFiles($dbh,$imageID){

    $path = "../evidences/";
    $data = ["imageID" => $imageID];
    $evidencefilename = getevidencefilename($dbh, $imageID);
    $sql = "DELETE FROM images
            WHERE imageID = :imageID";
    $stmt = $dbh->prepare($sql);

    if($stmt->execute($data)){
      unlink($path.$evidencefilename);
    }
}

function saveTreeFam($dbh,$familytitle, $is_display, $familytoken)
{
    $created_at = $dbh->query("SELECT NOW()")->fetchColumn();
    $data = [ "familytitle" => $familytitle, "is_display" => $is_display, "familytoken" => $familytoken, "created_at" => $created_at];
    $sql = "INSERT INTO family ( familytitle, is_display, familytoken, created_at)
    VALUES ( :familytitle, :is_display, :familytoken, :created_at)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
}

function listAlltreeFam($dbh)
{
    $sql = "SELECT  *, IF(is_display=1,'YES','NO') AS is_display FROM family";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();

    if($rowCount > 0)
    {
        $count = 0;
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['familytitle']."</td>";
                echo "<td>".$d['is_display']."</td>";
                if($d['familytitle'] != 'TIADA'){
                echo "<td><button type='button' name='edit' class='btn btn-danger' title='Edit' onclick='window.location=\"/treecamus/tree/family/edit/".$d['familytoken']."\";'><i class='fas fa-pencil-alt'></i></button>
                      </td>";}
                else{
                    echo "<td><button type='button' name='edit' class='btn btn-secondary'><i class='fas fa-pencil-alt'></i></button>
                      </td>";}
            echo "</tr>";
            $count++;
        }
    }
}

function updatetreeFamily($dbh,$familytitle, $is_display, $token)
{
    $updated_at = getTimestamp();
    $data = [ "familytitle" => $familytitle, "is_display" => $is_display, "token" => $token, "updated_at" => $updated_at];
    $sql = "UPDATE family SET familytitle = :familytitle, is_display = :is_display, 
    updated_at = :updated_at 
    WHERE familytoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
}

function updatetagStatus($dbh, $tagID, $tagstatusID, $tagstoken )
{
    $data = [ "tagID" => $tagID,"tagstatusID"=>$tagstatusID, "tagstoken"=>$tagstoken ];
    $sql = "UPDATE tags SET tagID= :tagID,tagstatusID=:tagstatusID WHERE tagstoken= :tagstoken";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
} 

function updatetag($dbh, $censorIDstatus, $treeID)
{
    $data = ["censorIDstatus" => $censorIDstatus, "treeID" => $treeID];
    $sql = "UPDATE tags  SET tagstatusID=:censorIDstatus WHERE treesID= :treeID";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
}

function updatetag2($dbh, $treeID)
{
    $data = ["treeID" => $treeID];
    $sql = "UPDATE tags  SET tagstatusID=0 WHERE treesID= :treeID AND tagstatusID=1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
}

function getTreeFamily($dbh)
{
    $sql = "SELECT * FROM family ORDER BY familytitle ASC";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();

    if($rowCount > 0)
    {
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<option value='".$d['familyID']."'>".$d['familytitle']."</option>";
        }
    }
}

function saveTreeSpecies($dbh, $familyid, $speciestitle, $localname, $is_display, $speciestoken)
{
    $created_at = $dbh->query("SELECT NOW()")->fetchColumn();
    $data = [ "familyid" => $familyid, "speciestitle" => $speciestitle, "localname" => $localname, "is_display" => $is_display, "speciestoken" => $speciestoken, "created_at" => $created_at];
    $sql = "INSERT INTO species (familyID, speciestitle, specieslocalname, is_display, speciestoken, created_at)
    VALUES (:familyid, :speciestitle, :localname, :is_display, :speciestoken, :created_at)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
}

function listAlltreeSpecies($dbh)
{
    $sql = "SELECT  *, IF(s.is_display=1,'YES','NO') AS is_display FROM species s JOIN family f on f.familyID = s.familyID ORDER BY f.familytitle ASC, s.speciestitle ASC";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();

    if($rowCount > 0)
    {
        $count = 0;
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['familytitle']."</td>";
                echo "<td>".$d['speciestitle']."</td>";
                echo "<td>".$d['specieslocalname']."</td>";
                echo "<td>".$d['is_display']."</td>";
                if($d['speciestitle'] != 'TIADA'){
                echo "<td><button type='button' name='edit' class='btn btn-danger' title='Edit' onclick='window.location=\"/treecamus/tree/species/edit/".$d['speciestoken']."\";'><i class='fas fa-pencil-alt'></i></button>
                      </td>";}
                else{
                    echo "<td><button type='button' name='edit' class='btn btn-secondary'><i class='fas fa-pencil-alt'></i></button>
                    </td>";}
            echo "</tr>";
            $count++;
        }
    }
}

function updatetreeSpecies($dbh, $familyID, $speciestitle, $specieslocalname, $is_display, $token)
{
    $updated_at = getTimestamp();
    $data = ["familyID" => $familyID, "speciestitle" => $speciestitle, "specieslocalname" => $specieslocalname, "is_display" => $is_display, "token" => $token, "updated_at" => $updated_at];
    $sql = "UPDATE species SET familyID = :familyID, speciestitle = :speciestitle, specieslocalname = :specieslocalname, is_display = :is_display, 
    updated_at = :updated_at WHERE speciestoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
}

function saveTree($dbh,$treeUniqueName,$treeID, $latitude, $longitude, $userID, $arborist, $campus,$zone,$family,$species,$treestatus, $treetoken, $tagID)
{
    $created_at = $dbh->query("SELECT NOW()")->fetchColumn();
    $data = [ "treeUniqueCode" => $treeUniqueName, "treeID" => $treeID, "latitude" => $latitude, "longitude" => $longitude,"userID" => $userID, "arboristID" => $arborist, "campusID" => $campus, "zoneID" => $zone, "familyID" => $family, "speciesID" => $species, "treestatusID" => $treestatus,  "treetoken" => $treetoken, "tagID" => $tagID,"created_at" => $created_at];
    $sql = "INSERT INTO trees ( treeUniqueCode, treeID, latitude, longitude, userID, user_id_arborist, campusID, zoneID, familyID, speciesID, treestatusID, treetoken, tagID, created_at)
    VALUES ( :treeUniqueCode, :treeID, :latitude, :longitude, :userID, :arboristID, :campusID, :zoneID, :familyID, :speciesID, :treestatusID, :treetoken, :tagID, :created_at)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
}

function getImagesForTree($dbh, $treeID)
{
    $query = "SELECT imageID, imageFileName, imagePrimary FROM images WHERE treeID = :treeID";
    $stmt = $dbh->prepare($query);
    $data = ['treeID' => $treeID];
    $stmt->execute($data);

    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $images;
}

function savetag($dbh, $censorID, $treeID, $tagstoken)
{
    $data = [ "censorID" => $censorID, "treeID" => $treeID,"tagstoken"=>$tagstoken];
    $sql = "INSERT INTO tags ( tagID, treesID, tagstoken) VALUES ( :censorID, :treeID,  :tagstoken)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
} 

function savetag2($dbh, $censorID, $treeID, $censorIDstatus, $tagstoken)
{
    $data = [ "censorID" => $censorID, "treeID" => $treeID, "censorIDstatus" => $censorIDstatus, "tagstoken"=>$tagstoken];
    $sql = "INSERT INTO tags ( tagID, treesID, tagstatusID, tagstoken) VALUES ( :censorID, :treeID, :censorIDstatus,  :tagstoken)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
} 

function saveevidence($dbh, $imageFileName, $imageFileType, $treeID, $imagetoken, $imagePrimary) 
{
    $data = [ "imageFileName" => $imageFileName, "imageFileType" => $imageFileType, "treeID" => $treeID, "imagetoken" => $imagetoken, "imagePrimary" => $imagePrimary];
    $sql = "INSERT INTO images (imageFileName, imageFileType, treeID, imagetoken, imagePrimary)
            VALUES (:imageFileName, :imageFileType, :treeID, :imagetoken, :imagePrimary)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
}

function updateImagePrimary($dbh, $treeID, $imageFileName)
{
    $query = "UPDATE images SET imagePrimary = 0 WHERE treeID = :treeID";
    $stmt = $dbh->prepare($query);
    $data = ['treeID' => $treeID];
    $stmt->execute($data);

    $query = "UPDATE images SET imagePrimary = 1 WHERE treeID = :treeID AND imageFileName = :imageFileName";
    $stmt = $dbh->prepare($query);
    $data = ['treeID' => $treeID, 'imageFileName' => $imageFileName];
    $stmt->execute($data);
}

function listAlltrees($dbh)
{
     $sql = "SELECT tr.latitude, tr.longitude, tr.created_at, sp.speciestitle, COALESCE(sp.specieslocalname, 'Not Applicable') AS specieslocalname, z.zonetitle,
     fm.familytitle, tr.treetoken,
     CASE
         WHEN im.imageFileName IS NULL THEN 'No Image(s)'
         ELSE 'Has Image(s)'
     END AS imageIsFound
     FROM trees tr
     JOIN species sp ON tr.speciesID = sp.speciesID
     JOIN family fm ON fm.familyID = tr.familyID
     LEFT JOIN images im ON im.treeID = tr.treeID AND im.imagePrimary = 1
     LEFT JOIN `zone` z ON z.zoneID= tr.zoneID";

    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();

    if($rowCount > 0)
    {
        $count = 0;
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['familytitle']."</td>";
                echo "<td>".$d['speciestitle']."</td>";
                echo "<td>".$d['latitude'].",".$d['longitude']."</td>";
                echo "<td>".$d['zonetitle']."</td>";
                echo "<td>".$d['imageIsFound']."</td>";
                echo "<td>".$d['created_at']."</td>";
                echo "<td><button type='button' name='view' class='btn btn-info' title='View' onclick='window.location=\"/treecamus/tree/view/".$d['treetoken']."\";'><i class='fa fa-eye'></i></button>
                          <button type='button' name='edit' class='btn btn-danger' title='Edit' onclick='window.location=\"/treecamus/tree/edit/".$d['treetoken']."\";'><i class='fas fa-pencil-alt'></i></button>
                      </td>";
            echo "</tr>";
            $count++;
        }
    }
}

function listArboristtrees($dbh,$arboristID)
{
     $data = ["arboristID" => $arboristID];

     $sql = "SELECT COALESCE(tr.treeUniqueCode, 'No Tree Name') AS treeName,
                    tr.latitude, 
                    tr.longitude, 
                    tr.created_at, 
                    sp.speciestitle, 
                    sp.specieslocalname, 
                    z.zonetitle, 
                    tr.user_id_arborist,
                    fm.familytitle, 
                    tr.treetoken,
                    CASE
                        WHEN im.imageFileName IS NULL THEN 'No Image'
                        ELSE 'Has Image'
                    END AS imageIsFound
     FROM trees tr
     JOIN species sp ON tr.speciesID = sp.speciesID
     JOIN family fm ON fm.familyID = tr.familyID
     LEFT JOIN images im ON im.treeID = tr.treeID AND im.imagePrimary = 1
     LEFT JOIN `zone` z ON z.zoneID = tr.zoneID
     WHERE tr.user_id_arborist = :arboristID"; 
     
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount =$stmt->rowCount();

    if($rowCount > 0)     {$count = 0;
        while($d =$stmt->fetch(PDO::FETCH_ASSOC))
        {

            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['familytitle']."</td>";
                echo "<td>".$d['speciestitle']."</td>";
                echo "<td>".$d['latitude'].",".$d['longitude']."</td>";
                echo "<td>".$d['zonetitle']."</td>";
                echo "<td>".$d['imageIsFound']."</td>";
                echo "<td>".$d['created_at']."</td>";
                echo "<td><button type='button' name='view' class='btn btn-info' title='View' onclick='window.location=\"/treecamus/tree/view/".$d['treetoken']."\";'><i class='fa fa-eye'></i></button>
                      </td>";
            echo "</tr>";
            $count++;
        }
    }
}

function listAlltreesdashboard($dbh)
{
    $sql = "SELECT  * FROM trees ts JOIN `zone` z ON z.zoneID= ts.zoneID";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();

    if($rowCount > 0)
    {
        $count = 0;
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['treeName']."</td>";
                echo "<td>".$d['latitude'].",".$d['longitude']."</td>";
                echo "<td>".$d['zonetitle']."</td>";
                echo "<td><button type='button' name='view' class='btn btn-info' title='View' onclick='window.location=\"/treecamus/tree/view/".$d['treetoken']."\";'><i class='fa fa-eye'></i></button>
                      </td>";
            echo "</tr>";
            $count++;
        }
    }
}

function listTreesHasbeenCareByArborist($dbh, $arboristID)
{
    $data = ["arboristID" => $arboristID]; 
    $sql = "SELECT ts.treeName,ts.latitude,ts.longitude,z.zonetitle, ts.treetoken FROM trees ts JOIN `zone` z ON z.zoneID= ts.zoneID WHERE ts.user_id_arborist= :arboristID";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount = $stmt->rowCount();

    if($rowCount > 0)
    {
        $count = 0;
        while($d = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['treeName']."</td>";
                echo "<td>".$d['latitude'].",".$d['longitude']."</td>";
                echo "<td>".$d['zonetitle']."</td>";
                echo "<td><button type='button' name='view' class='btn btn-info' title='View' onclick='window.location=\"/treecamus/tree/view/".$d['treetoken']."\";'><i class='fa fa-eye'></i></button>
                      </td>";
            echo "</tr>";
            $count++;
        }
    }
}

function updateTreeDetails($dbh,$treeName, $latitude, $longitude, $campus, $zone, $family,$species,$treestatus, $arborist, $tagID, $token)
{
    $updated_at = getTimestamp();
    $data = ["treeName" => $treeName, "latitude" => $latitude, 
            "longitude" => $longitude, "campus"=> $campus, "zoneID"=> $zone, "family"=>$family, "species"=>$species, "treestatus"=>$treestatus, "arborist"=>$arborist, "tagID"=> $tagID, "updated_at" => $updated_at, "token"=>$token];
    $sql = "UPDATE trees SET treeName = :treeName, latitude = :latitude, longitude= :longitude, campusID= :campus, zoneID = :zoneID,
            familyID = :family, speciesID= :species,treestatusID= :treestatus, user_id_arborist= :arborist, tagID= :tagID, updated_at = :updated_at
            WHERE treetoken = :token";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
}

//////////USER FUNCTION//////////
function listUsers($dbh)
{
    $sql = "SELECT u.userID, r.roletitle, u.name, u.email ,IF(ua.is_allow = 1,'YES','NO') AS is_allow, u.userstoken
            FROM treecamusdb.users u
            LEFT JOIN treecamusdb.useraccess ua
            ON u.userID = ua.userID
            LEFT JOIN treecamusdb.roles r
            ON ua.roleID = r.roleID;";
            
            $statement = $dbh->prepare($sql);
            $statement->execute();
            $rowUsers = $statement->rowCount();
            
            if ($rowUsers > 0) {
                $count = 0;
                while ($u = $statement->fetch(PDO::FETCH_ASSOC)) {
                  echo "<tr>";
                    echo "<td>".($count + 1)."</td>";
                    echo "<td>".$u['name']."</td>";
                    echo "<td>".$u['email']."</td>";
                    echo "<td>".$u['roletitle']."</td>";
                    echo "<td>".$u['is_allow']."</td>";
                        echo "<td>
                            <button type='button' name='view' class='btn btn-info' title='View' onclick='window.location=\"/treecamus/users/view/".$u['userstoken']."\";'><i class='fa fa-eye'></i></button>
                            <button type='button' name='edit' class='btn btn-info' title='Edit' onclick='window.location=\"/treecamus/users/edit/".$u['userstoken']."\";'><i class='fas fa-pencil-alt'></i></button>                                        
                          </td>";
                  echo "</tr>";
                  $count++;
                }
            }
}

function checkUserRecord($dbh, $token)
{
    $found = false;
    $data = ["userstoken" => $token];
    $sql = "SELECT * FROM treecamusdb.users u WHERE userstoken=:userstoken LIMIT 1";
    $statement = $dbh->prepare($sql);
    $statement->execute($data);
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        $found = true;    
    }
    
    return $found;
}

function getprofileimage($dbh, $token)
{
    $data = ["userstoken" => $token];
    $sql = "SELECT profile_image FROM users WHERE userstoken = :userstoken";
    $statement = $dbh->prepare($sql);
    $statement->execute($data);
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        $u = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $u['profile_image'];
    }   
}

function getUserID($dbh, $token)
{
    $data = ["userstoken" => $token];
    $sql = "SELECT userID FROM users WHERE userstoken = :userstoken";
    $statement = $dbh->prepare($sql);
    $statement->execute($data);
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        $u = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $u['userID'];
    }   
}

function getname($dbh, $token)
{
    $data = ["userstoken" => $token];
    $sql = "SELECT name FROM users WHERE userstoken = :userstoken";
    $statement = $dbh->prepare($sql);
    $statement->execute($data);
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        $u = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $u['name'];
    }   
}

function getemail($dbh, $token)
{
    $data = ["userstoken" => $token];
    $sql = "SELECT email FROM users WHERE userstoken = :userstoken";
    $statement = $dbh->prepare($sql);
    $statement->execute($data);
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        $u = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $u['email'];
    }   
}

function getroletitle($dbh, $token)
{
    $data = ["userstoken" => $token]; 
    $sql = "SELECT r.roletitle FROM treecamusdb.useraccess ua 
            JOIN treecamusdb.users u ON ua.userID = u.userID
            JOIN treecamusdb.roles r ON ua.roleID = r.roleID
            WHERE u.userstoken = :userstoken";
    $statement = $dbh->prepare($sql);
    $statement->execute($data);
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        $u = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $u['roletitle'];
    }
}

function getisallow($dbh, $token)
{
    $data = ["userstoken" => $token]; 
    $sql = "SELECT IF(ua.is_allow = 1,'YES, THIS USER HAS BEEN ALLOW TO ACCESS THE SYSTEM','NO, THIS USER DID NOT ALLOW TO ACCESS THE SYSTEM') AS is_allow
            FROM treecamusdb.users u
            JOIN treecamusdb.useraccess ua ON ua.userID = u.userID
            WHERE u.userstoken = :userstoken";
    $statement = $dbh->prepare($sql);
    $statement->execute($data);
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        $u = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $u['is_allow'];
    }
}

function checkUserAccessRecord($dbh, $token)
{
    $found = false;
    $data = ["userstoken" => $token];
    $sql = "SELECT * FROM treecamusdb.useraccess ua
            JOIN treecamusdb.users u ON u.userID = ua.userID
            WHERE u.userstoken=:userstoken";
    $statement = $dbh->prepare($sql);
    $statement->execute($data);
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        $found = true;    
    }
    
    return $found;
}

function getRoleList($dbh) {
    $sql = "SELECT * FROM treecamusdb.roles r ORDER BY roleID ASC";
    $statement = $dbh->prepare($sql);
    $statement->execute();
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        while($u = $statement->fetch(PDO::FETCH_ASSOC))
        {        
            echo "<option value='".$u['roleID']."'>".$u['roletitle']."</option>";
        }
    }
}

function getAccessIsAllowed($dbh, $token)
{
    $data = ["userstoken" => $token]; 
    $sql = "SELECT ua.is_allow FROM treecamusdb.users u
            JOIN treecamusdb.useraccess ua
            WHERE u.userstoken = :userstoken";
    $statement = $dbh->prepare($sql);
    $statement->execute($data);
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        $u = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $u['is_allow'];
    }
}

function getroleid($dbh, $token)
{
    $data = ["userstoken" => $token]; 
    $sql = "SELECT * FROM treecamusdb.useraccess ua
            JOIN treecamusdb.users u ON ua.userID = u.userID
            WHERE u.userstoken = :userstoken";
    $statement = $dbh->prepare($sql);
    $statement->execute($data);
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        $u = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $u['roleID'];
    }
}

function getSelectedRoleList($dbh, $role)
{
    $sql = "SELECT * FROM roles";
    $statement = $dbh->prepare($sql);
    $statement->execute();
    $rowUsers = $statement->rowCount();
    if($rowUsers > 0)
    {
        while($u = $statement->fetch(PDO::FETCH_ASSOC))
        {
            
            if($u['roleID'] == $role)
            {
                echo "<option value='".$u['roleID']."' selected>".$u['roletitle']."</option>";
            }
            else
            {
                echo "<option value='".$u['roleID']."'>".$u['roletitle']."</option>";
            }          
 
        }
    }
}

function AddUserAccess($dbh, $userID, $role,$assignedby, $is_allow ,$uatoken)
{
    $created_at = $dbh->query("SELECT NOW()")->fetchColumn();
    $data = [
        ':userID' => $userID,
        ':role' => $role,
        ':assignedby' => $assignedby,
        ':created_at' => $created_at,
        ':isallowed' => $is_allow,
        ':uatoken' => $uatoken,
    ];
    $sql = "INSERT INTO treecamusdb.useraccess (userID, roleID, assignedby, created_at, is_allow ,uatoken)
            VALUES (:userID, :role , :assignedby, :created_at, :isallowed ,:uatoken)";
    $statement = $dbh->prepare($sql);
    $query_execute = $statement->execute($data);

}

function UpdateUserAccess($dbh, $userID, $role, $assignedby, $is_allow)
{
    $updated_at = getTimestamp();
    $data = ["userID" => $userID, "role" => $role, "assignedby" => $assignedby,
            "isallowed" => $is_allow, "updated_at" => $updated_at];
    $sql = "UPDATE useraccess SET roleID = :role, assignedby= :assignedby, is_allow = :isallowed, updated_at = :updated_at
            WHERE userID = :userID";
    $statement = $dbh->prepare($sql);
    $query_execute = $statement->execute($data);
}

function UpdateUsersIsAllowed($dbh, $is_allow, $userstoken) 
{ 
    $updated_at = getTimestamp(); 
    $data = [ 
        ':isallowed' => $is_allow, 
        ':userstoken' => $userstoken, 
        ':updated_at' => $updated_at, 
    ]; 
    $sql = "UPDATE treecamusdb.users SET is_allow = :isallowed, updated_at = :updated_at 
            WHERE userstoken = :userstoken"; 
    $statement = $dbh->prepare($sql); 
    $query_execute = $statement->execute($data); 

    if($query_execute){ 
        header('Location: /treecamus/users/list?success'); 
        exit(0); 
    }else { 
        header('Location: /treecamus/users/edit?failed'); 
        exit(0); 
    } 
} 
//////////END OF USER FUNCTION//////////

function totalTreePlanted($dbh)
{
    $sql = "SELECT COUNT(*) AS Totaltree FROM trees";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['Totaltree'];
    }
}

function totalArborist($dbh)
{
    $sql = "SELECT COUNT(*) AS TotalArborist FROM useraccess WHERE roleID=4";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['TotalArborist'];
    }
}

function totalCampus($dbh)
{
    $sql = "SELECT COUNT(*) AS Totalcampus FROM campus";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['Totalcampus'];
    }
}

function totalZone($dbh)
{
    $sql = "SELECT COUNT(*) AS Totalzone FROM `zone` WHERE NOT zoneID=5;";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if($rowCount > 0)
    {
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['Totalzone'];
    }
}
############ chart box ############
function statBoxTotalTree($dbh)
{
    $sql = "SELECT COUNT(*) AS totaltree FROM trees";
    $stmt =$dbh->prepare($sql);$stmt->execute();
    $rowCount =$stmt->rowCount();
    if($rowCount > 0)
    {
        $d =$stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['totaltree'];
    }
}
function statBoxTotalFamily($dbh)
{
    $sql = "SELECT COUNT(*) AS totalfamily FROM family WHERE familyID != 1"; //remove Not Applicable
    $stmt =$dbh->prepare($sql);$stmt->execute();
    $rowCount =$stmt->rowCount();
    if($rowCount > 0)
    {
        $d =$stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['totalfamily'];
    }
}
function statBoxTotalSpecies($dbh)
{
    $sql = "SELECT COUNT(*) AS totalspecies FROM species WHERE speciesID != 1";
    $stmt =$dbh->prepare($sql);$stmt->execute();
    $rowCount =$stmt->rowCount();
    if($rowCount > 0)
    {
        $d =$stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['totalspecies'];
    }
}
function statBoxTotalZone($dbh)
{
    $sql = "SELECT COUNT(DISTINCT zoneID) AS totalzone FROM trees WHERE zoneID IS NOT NULL";
    $stmt =$dbh->prepare($sql);$stmt->execute();
    $rowCount =$stmt->rowCount();
    if($rowCount > 0)
    {
        $d =$stmt->fetch(PDO::FETCH_ASSOC);
        
        return $d['totalzone'];
    }
}
###################################
############ listing ############
function getFamilyTitleList($dbh)
{
    $count = 0;
    $sql = "SELECT f.familytitle, COUNT(t.treeID) AS noOfTrees FROM family f JOIN trees t ON f.familyID = t.familyID WHERE f.familyID != 1 GROUP BY f.familytitle ORDER BY f.familytitle ASC"; 
    $stmt =$dbh->prepare($sql);$stmt->execute();
    $rowCount =$stmt->rowCount();
    if($rowCount > 0)
    {
        while($d =$stmt->fetch(PDO::FETCH_ASSOC)) 
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['familytitle']."</td>";
                echo "<td>".$d['noOfTrees']."</td>"; 
            echo "</tr>";
            $count++;
        }
    }     
}
function getPrimaryImageForTree($dbh,$treeID)
{
    $query = "SELECT imageID, imageFileName FROM images WHERE treeID = :treeID AND imagePrimary = 1";
    $stmt = $dbh->prepare($query);
    $data = ['treeID' =>$treeID];
    $stmt->execute($data);

    $image =$stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        $imageFileName =$image['imageFileName'];
        return '<img src="evidences/' . $imageFileName . '" alt="Tree Image" class="img-fluid h-50">';
    } else {
        return '<img src="dist/img/default_error_img_map/no_img_found.png' . '" alt="Tree Image" class="img-fluid h-50">';
    }
}

function getSpeciesTitleList($dbh)
{
    $count = 0;
    $sql = "SELECT f.familytitle, s.speciestitle, s.specieslocalname, COUNT(t.treeID) AS noOfTrees FROM family f JOIN trees t ON f.familyID = t.familyID JOIN species s ON t.speciesID = s.speciesID WHERE t.speciesID != 1 GROUP BY f.familytitle, s.speciestitle, s.specieslocalname ORDER BY f.familytitle ASC, s.speciestitle ASC"; 
    $stmt =$dbh->prepare($sql);$stmt->execute();
    $rowCount =$stmt->rowCount();
    if($rowCount > 0)
    {
        while($d =$stmt->fetch(PDO::FETCH_ASSOC)) 
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['familytitle']."</td>"; 
                echo "<td>".$d['speciestitle']."</td>";
                echo "<td>".stripslashes($d['specieslocalname'])."</td>";
                echo "<td>".$d['noOfTrees']."</td>";
            echo "</tr>";
            $count++;
        }
    }     
}

function listAllTreesPublic($dbh)
{
    $sql = "SELECT tr.latitude, tr.longitude, sp.speciestitle, COALESCE(sp.specieslocalname, 'Not Applicable') AS specieslocalname,
    fm.familytitle, tr.treetoken,
    CASE
        WHEN im.imageFileName IS NULL THEN 'No Image(s)'
        ELSE 'Has Image(s)'
    END AS imageIsFound
    FROM trees tr
    JOIN species sp ON tr.speciesID = sp.speciesID
    JOIN family fm ON fm.familyID = tr.familyID
    LEFT JOIN images im ON im.treeID = tr.treeID AND im.imagePrimary = 1";

    $stmt =$dbh->prepare($sql);$stmt->execute();
    $rowCount =$stmt->rowCount();

    if($rowCount > 0)     {$count = 0;
        while($d =$stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['familytitle']."</td>";
                echo "<td>".$d['speciestitle']."</td>";
                echo "<td>".$d['specieslocalname']."</td>";
                echo "<td>".$d['latitude'] .", ".$d['longitude']."</td>";
                echo "<td>".$d['imageIsFound']."</td>";
                echo "<td>
                        <button type='button' name='view' class='btn btn-info' title='View' onclick='window.location=\"/treecamus/explore-trees/".$d['treetoken']."\";'><i class='fa fa-eye'></i></button>
                    </td>";
            echo "</tr>";
            $count++;
        }
    }
}

function listTreeMeasurement($dbh,$treeID)
{
    $data = ["treeID" => $treeID];$sql = "SELECT * FROM treemeasurement ts
            JOIN trees t ON t.treeID = ts.treeID
            WHERE BINARY ts.treeID = :treeID ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $rowCount =$stmt->rowCount();

    if($rowCount > 0)     {$count = 0;
        while($d =$stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo "<tr>";
                echo "<td>".($count + 1)."</td>";
                echo "<td>".$d['overalheight']."</td>";
                echo "<td>".$d['circumference']."</td>";
                echo "<td>".$d['crownwidth']."</td>";
                echo "<td>".$d['created_at']."</td>";
            echo "</tr>";
            $count++;
        }
    }
}
#################################SS
############ mapping ############
function getPolygon($dbh)
{
    $sql = "SELECT p.polygonid, p.polygonname, p.polygonlength, p.polygonarea,
                 c.longitude, c.latitude, c.depth
          FROM polygon p
          JOIN polygoncoordinates c ON p.polygonid = c.polygonid";
    $stmt =$dbh->prepare($sql);$stmt->execute();
    $rowCount =$stmt->rowCount();
    if ($rowCount > 0) {
        $features = [];$currentPolygonId = null;
        $currentFeature = null;

        while ($row =$stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['polygonid'] !==$currentPolygonId) {
                if ($currentFeature !== null) {
                    $features[] =$currentFeature;
                }
                
                $currentPolygonId = $row['polygonid'];$currentFeature = [
                    "type" => "Feature",
                    "properties" => [
                        "Shape_Leng" => $row['polygonlength'],
                        "Shape_Area" => $row['polygonarea'],
                        "Zoning_name" => $row['polygonname']
                    ],
                    "geometry" => [
                        "type" => "Polygon",
                        "coordinates" => [[]]  
                    ]
                ];
            }

            $currentFeature['geometry']['coordinates'][0][] = [
                floatval($row['longitude']),
                floatval($row['latitude']),
                floatval($row['depth'])
            ];
        }

        if ($currentFeature !== null) {
            $features[] =$currentFeature;
        }

        $geojson = [
            "type" => "FeatureCollection",
            "name" => "UITM_Boundary",
            "crs" => ["type" => "name", "properties" => ["name" => "urn:ogc:def:crs:OGC:1.3:CRS84"]],
            "features" => $features
        ];

        return json_encode($geojson);
    }

    return null; 
}
#################################

function getViewPathForIndexScreen()
{
    $baseUrl = '/treecamus/explore-trees/';
    $fullUrl =$baseUrl;
    return $fullUrl;
}

function getViewPathForPublicAccessScreen()
{
    $baseUrl = '/treecamus/explore-trees/';
    $fullUrl =$baseUrl;
    return $fullUrl;
}

function getViewPath()
{
    $baseUrl = '/treecamus/tree/view/';
    $fullUrl =$baseUrl;
    return $fullUrl;
}

function getViewPathForViewTreeScreen()
{
    $baseUrl = '/treecamus/tree/view/';
    $fullUrl =$baseUrl;
    return $fullUrl;
}

function compressAndUploadImage($sourcePath,$destinationPath, $fileExtension,$quality = 90) {

    switch ($fileExtension)
     {
        case 'jpg':
        case 'jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'png':
            $image = imagecreatefrompng($sourcePath);
            break;
        case 'gif':
            $image = imagecreatefromgif($sourcePath);
            break;
        default:
       
            return false;
    }
    switch ($fileExtension) 
    {
        case 'jpg':
        case 'jpeg':
            imagejpeg($image, $destinationPath,$quality);
            break;
        case 'png':
            imagepng($image,$destinationPath);
            break;
        case 'gif':
            imagegif($image,$destinationPath);
            break;
    }

    imagedestroy($image);

    return true;
}

function getFamilyStatisticsWithPercentage($dbh)
{
    $query = "SELECT f.familytitle, COUNT(*) as count,
              (COUNT(*) / (SELECT COUNT(*) FROM trees)) * 100 as percentage
              FROM family f
              INNER JOIN trees t ON f.familyID = t.familyID
              GROUP BY f.familytitle";
    $stmt =$dbh->prepare($query);$stmt->execute();

    $data =$stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedData = [];

    foreach ($data as$row) {
     
        if (isset($row['familytitle'],$row['count'])) {
            $label =$row['familytitle'];
            $count =$row['count'];

            $formattedData[] = [
                'name' => $label,
                'y' => $count 
            ];
        } 
    }

    return $formattedData;
}
function getSpeciesStatisticsWithPercentage($dbh)
{
    $query = "SELECT s.speciestitle, COUNT(*) as count,
              (COUNT(*) / (SELECT COUNT(*) FROM trees)) * 100 as percentage
              FROM species s
              INNER JOIN trees t ON s.speciesID = t.speciesID
              GROUP BY s.speciestitle";
              
    $stmt =$dbh->prepare($query);$stmt->execute();

    $data =$stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedData = [];

    foreach ($data as$row) {
       
        if (isset($row['speciestitle'],$row['count'])) {
            $label =$row['speciestitle'];
            $count =$row['count'];

            $formattedData[] = [
                'name' => $label,
                'y' => $count,
            ];
        } 
    }

    return $formattedData;
}
function getTreeData($dbh)
{
    try {
    
        $sql = "SELECT DATE(DATE_FORMAT(tr.created_at, '%Y-%m-%d')) as day,
                COUNT(*) as tree_count,
                GROUP_CONCAT(COALESCE(tr.treeUniqueCode, 'N/A')) as treeUniqueCode,
                GROUP_CONCAT(fm.familyTitle) as families,
                GROUP_CONCAT(sp.speciesTitle) as species
                FROM trees tr
                JOIN species sp ON tr.speciesID = sp.speciesID
                JOIN family fm ON fm.familyID = tr.familyID
                WHERE tr.created_at >= DATE(NOW()) - INTERVAL 30 DAY
                GROUP BY day
                ORDER BY day DESC";

        $stmt =$dbh->prepare($sql);$stmt->execute();
        $rowCount =$stmt->rowCount();

        $trees = array();
        while ($row =$stmt->fetch(PDO::FETCH_ASSOC)) {

            $treeIDs = $row["treeUniqueCode"] ? explode(',', $row["treeUniqueCode"]) : [];
            $families = $row["families"] ? explode(',', $row["families"]) : [];
            $species = $row["species"] ? explode(',', $row["species"]) : [];

            $treesPlanted = array();
            
    
            for ($i = 0; $i < count($treeIDs); $i++) {$treesPlanted[] = array(
                    "tree_unique_code" => trim($treeIDs[$i]), 
                    "family" => trim($families[$i]),
                    "species" => trim($species[$i]),
                );
            }

            $trees[] = array(
                "day" => $row["day"],
                "tree_count" => $row["tree_count"],
                "trees_planted" => $treesPlanted,
            );
        }

        return $trees;

    } catch (PDOException $e) {
      
        return array("error" => "Error, Please Try Again : " . $e->getMessage());
    }
}

?>