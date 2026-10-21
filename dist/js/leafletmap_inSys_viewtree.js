
var key = "Tr33CAMsdsdsdcvvvfd"; 
var encryptedCode = "..."; 
var decryptedCode = CryptoJS.AES.decrypt(encryptedCode, key).toString(CryptoJS.enc.Utf8);

function executeDecryptedCode() {
    // Execute the decrypted code
    try {
        eval(decryptedCode);
    } catch (error) {
        console.error("Error executing decrypted code:", error);
    }
}

function initLeafletMap(data, geojsonData, treeViewUrl) {
    var map = L.map('leafletmap').setView([3.0697706, 101.5014936], 15);
    var tileLayers = {
        OpenStreetMap: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }),
        Satellite: L.tileLayer('httaps://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: '© Esri'
        }),
        TopoMap: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://opentopomap.org/about">OpenTopoMap</a>'
        }),
        ArcGIS: L.tileLayer('https://server.arcgisonline.com/arcgis/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: '© Esri'
        }),
        Watercolor: L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.{ext}', {
            maxZoom: 19,
            attribution: 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL',
            subdomains: 'abcd',
            minZoom: 1,
            maxZoom: 16,
            ext: 'jpg'
        }),
        Toner: L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner/{z}/{x}/{y}.{ext}', {
            maxZoom: 19,
            attribution: 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL',
            subdomains: 'abcd',
            minZoom: 1,
            maxZoom: 20,
            ext: 'png'
        }),
        StamenTerrain: L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}.{ext}', {
            maxZoom: 19,
            attribution: 'Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under ODbL',
            subdomains: 'abcd',
            minZoom: 0,
            maxZoom: 18,
            ext: 'png'
        })
    };

    tileLayers.OpenStreetMap.addTo(map);

    var baseLayers = {
        "OpenStreetMap": tileLayers.OpenStreetMap,
        "TopoMap": tileLayers.TopoMap,
        "Satellite": tileLayers.Satellite,
        "ArcGIS": tileLayers.ArcGIS,
        "Watercolor": tileLayers.Watercolor,
        "Toner": tileLayers.Toner,
        "StamenTerrain": tileLayers.StamenTerrain
    };

    var layersControlContainer = L.DomUtil.create('div', 'leaflet-control leaflet-bar');
    layersControlContainer.style.marginTop = '80px'; 
    layersControlContainer.style.marginLeft = '8px'; 
    var layersControl = L.control.layers(baseLayers, null, {
        position: 'topleft'
    });
    layersControl.addTo(map);
    layersControlContainer.appendChild(layersControl.getContainer());
    map.getContainer().appendChild(layersControlContainer);

    var city = L.markerClusterGroup();


    var greenIcon = L.icon({
        iconUrl: '../images/forest.png',
        iconSize: [60, 60],
        iconAnchor: [30, 60], 
        popupAnchor: [0, -60], 
        pane: 'markerPane'
    });

    //##fixing custom marker drifting
    map.on('zoomend', function () {
        var currentZoom = map.getZoom();

        var newSize = [60 * (currentZoom / 15), 60 * (currentZoom / 15)]; 
        greenIcon.options.iconSize = newSize;

        greenIcon.options.iconAnchor = [newSize[0] / 2, newSize[1]]; 

        greenIcon.options.popupAnchor = [0, -newSize[1] / 2];
    });


    var datalayer = L.geoJson(geojsonData, {
        onEachFeature: function(feature, featureLayer) {
            featureLayer.bindPopup(
                "<html><body><h6>Subzone Info</h6>" +
                "<table border=''>" +
                "<tr><th>Zone Name</th><td>" + feature.properties.Zoning_name + "</td></tr>" +
                "<tr><th>Shape Length</th><td>" + feature.properties.Shape_Leng + "</td></tr>" +
                "<tr><th>Shape Area</th><td>" + feature.properties.Shape_Area + "</td></tr>" +
                "</table></body></html>"
            );
        },
        style: {
            stroke: true,
            weight: 1,
            color: '#957DAD',
            fillColor: '#957DAD',
            fillOpacity: '.1'
        }
    }).addTo(map);


    var treeIDToMarker = {};
    var treeIDArray = [];
    for (var i = 0; i < data.length; i++) {
        var new_location = new L.LatLng(data[i].lat, data[i].lon);
        var species = data[i].speciesDetails;
        var marker = new L.Marker(new_location, {
            title: species,
            icon: greenIcon
        });
        var message = 'Species: ' + species;
        marker.bindPopup(message);
        city.addLayer(marker);

        var dataIndex = i; // Store the data index as a custom property
        marker.dataIndex = dataIndex;

        treeIDArray.push(dataIndex); // Use dataIndex instead of treeID
        treeIDToMarker[dataIndex] = marker; // Use dataIndex as key

        // Load and create the marker icon
        var imageUrl = data[i].imageFileName === 'no_img_found.png' ?
            '../dist/img/default_error_img_map/no_img_found.png' // Default image for trees without images
            :imageLoader.getImageUrl(data[i].imageFileName);
    }

    map.addLayer(city);

    //## Set the map view to fit the bounds of all markers
    var markerBounds = city.getBounds();
    map.fitBounds(markerBounds, { padding: [20, 20] });

    // ############# Start: Sidebar and Click Events #############
    //console.log("Adding sidebar to the map...");
    var sidebar = L.control.sidebar('sidebar', {
        closeButton: true,
        position: 'right'
    });
    map.addControl(sidebar);
    let currentTreeInfo = null;

    function createSidebarContent(treeInfo) {
        var treetoken = treeInfo.treetoken;
        var url = treeViewUrl + treetoken;
        // Load and create the image URL for the sidebar content
        var imageUrl = treeInfo.imageFileName === 'no_img_found.png' ?
            '../dist/img/default_error_img_map/no_img_found.png' // Default image for trees without images
            :imageLoader.getImageUrl(treeInfo.imageFileName); // Image URL for trees with images

        var content = `
        <div id="sidebar-content" style="display: flex; flex-direction: column; padding: 0; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 8px; background-color: rgba(0, 0, 0, 0.6); border-radius: 6px;">
                <h5 style="margin: 0; font-size: 16px; text-decoration: underline; white-space: nowrap;">
                    <i class="fas fa-info-circle"></i> <b>TREE INFORMATION</b>
                </h5>
                <div style="display: flex; gap: 5px; align-items: center;">
                    <button class="btn btn-primary btn-sm" style="border: 1px solid #fff; background: #007bff; color: #fff; font-weight: bold;" title="View Advanced Details">
                             <a href="${url}" target="_blank" style="color: white; text-decoration: none;">
                                <i class="fas fa-info-circle"></i> 
                            </a>
                    </button>
                    <button class="btn btn-secondary btn-sm bg-danger" style="width: 30px; border: 1px solid #fff; background: transparent;" title="Close sidebar">
                        X
                    </button>
                </div>
            </div>
            
            <div style="display: flex; align-items: center;  margin-top: 30px; margin-left: 0;">
                <div style="flex: 0 0 180px; margin-left: 0px; border: 2px solid rgba(255, 255, 255, 0.5); border-radius: 10px;">
                    <img src="${imageUrl}" alt="Tree Image" style="width: 180px; height: 180px; border-radius: 10px;">
                </div>
                <div style="flex: 1; margin-left: 10px; text-align: left;">
                    <div style="font-size: 16px;"><i class="fas fa-tree" style="color: #94ff2b;font-size: 24px;"></i> <b>Tree ID:</b> ${treeInfo.treeID}</div>
                    <div style="font-size: 16px;"><i class="fas fa-leaf" style="color: #f8e64f;font-size: 24px;"></i> <b>Species:</b> ${treeInfo.speciesDetails}</div>
                    <div style="font-size: 16px;"><i class="fas fa-users" style="color: #6fdcc3;font-size: 24px;"></i> <b>Family:</b> ${treeInfo.familyDetails}</div>
                </div>
            </div>
        </div>
    `;

        var sidebarContent = document.createElement('div');
        sidebarContent.innerHTML = content;

        var closeButton = sidebarContent.querySelector('.btn.bg-danger');
        closeButton.addEventListener('click', function() {
            sidebar.hide();
        });

        return sidebarContent;
    }

    city.on('click', function(event) {
        var clickedMarker = event.layer;
        //console.log(clickedMarker);
        if (clickedMarker) {
            openSidebar(clickedMarker);
        }
    });

    function openSidebar(marker) {
        var dataIndex = marker.dataIndex;
        var clickedTreeInfo = data[dataIndex];

        var content = createSidebarContent(clickedTreeInfo);
        var sidebarContent = document.getElementById('sidebar-content');

        if (sidebar.isVisible()) {
            sidebar.hide();
            setTimeout(function() {

                sidebar.setContent(content);
                sidebar.show();

                var start = null;
                var duration = 300;

                function animate(timestamp) {
                    if (!start) start = timestamp;
                    var progress = timestamp - start;
                    var percentage = Math.min(progress / duration, 1);

                    sidebarContent.style.transform = `translateX(${100 * (1 - percentage)}%)`;
                    sidebarContent.style.opacity = percentage;

                    if (progress < duration) {
                        requestAnimationFrame(animate);
                    } else {
                        sidebarContent.style.transform = 'translateX(0)';
                        sidebarContent.style.opacity = 1;
                    }
                }

                requestAnimationFrame(animate);
            }, 300); // add delay untuk sidebar everytime clicked
        } else {
            sidebar.setContent(content);
            sidebar.show();
        }
    }

    // ############# End: Sidebar and Click Events #############
    // function createSearchControl(treeIDArray, treeIDToMarker) {
    //     var searchControl = L.Control.geocoder({
    //         placeholder: 'Search by Tree ID...',
    //         defaultMarkGeocode: false,
    //         collapsed: false,
    //         showResultIcons: true,
    //         geocoder: new L.Control.Geocoder.Nominatim({
    //             query: function(query) {
    //                 if (treeIDArray.includes(query)) {
    //                     var marker = treeIDToMarker[query];
    //                     if (marker) {
    //                         return {
    //                             query: marker.getLatLng()
    //                         };
    //                     }
    //                 }
    //                 return {
    //                     query: ''
    //                 };
    //             },
    //             formatResult: function(result) {
    //                 return result.name;
    //             }
    //         }),
    //         searchLabel: 'Search'
    //     }).addTo(map);


    //     searchControl._input.addEventListener('input', function(event) {
    //         console.log('User input:', event.target.value);
    //     });
    // }


    // createSearchControl(treeIDArray, treeIDToMarker);
}
export {
    initLeafletMap
};