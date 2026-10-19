<?php

require 'connection.php';
require 'function.php';

if(isset($_POST['submit']))
{

    $coordinate=explode(',', $_POST['coordinate']);
    $latitude= $coordinate[0];
    $longitude= $coordinate[1];

    saveLoc($dbh, $latitude, $longitude);
    
    header("Location: Map.php?success"); 

}
?> 

<form name="form1" id='form1' method="post" action="">
Coordinate<input type="text" id="coordinate" name="coordinate"/>
<button type="submit" name="submit" >Save</button>
</form>
<p>Click the button to get your coordinates.</p>

<button onclick="getLocation()">Try It</button>

<p id="demo"></p>

<script>
var x = document.getElementById("demo");

function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition, showError);
  } else { 
    x.innerHTML = "Geolocation is not supported by this browser.";
  }
}

function showPosition(position) {
  x.innerHTML = "Latitude: " + position.coords.latitude + 
  "<br>Longitude: " + position.coords.longitude + " Timestamp: " + 
  position.timestamp + " Altitude: " + position.coords.altitude;

  document.getElementById("coordinate").value = position.coords.latitude + 
  ", " + position.coords.longitude
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

</body>
</html>
