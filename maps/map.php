<?php
$current_page = 'maps';
include_once '../connection/connection.php';
include_once '../function/function.php';
include_once '../settings/settings.php';
include '../includes/header.php';
include '../includes/menu.php';

$sql = "SELECT tr.latitude, tr.longitude, sp.speciestitle,
         fm.familytitle, tr.treetoken,
         CASE
         WHEN tr.treeUniqueCode IS NULL THEN 'No Unique Code'
         ELSE tr.treeUniqueCode
         END as treeUniqueCode,
         COALESCE(im.imageFileName, 'no_img_found.png') as imageFileName
         FROM trees tr
         JOIN species sp ON tr.speciesID = sp.speciesID
         JOIN family fm ON fm.familyID = tr.familyID
         LEFT JOIN images im ON im.treeID = tr.treeID AND im.imagePrimary = 1";

$stmt = $dbh->prepare($sql);
$stmt->execute();
$rowCount = $stmt->rowCount();

if ($rowCount > 0) {
    $marker = array();
    while ($d = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $marker[] = array(
            "lat" => $d["latitude"], "lon" => $d["longitude"], "treeID" => $d["treeUniqueCode"],
            "speciesDetails" => $d["speciestitle"], "familyDetails" => $d["familytitle"],
            "imageFileName" => $d["imageFileName"], "treetoken" => $d["treetoken"]
        );
    }
} else {
    $marker = array();
}
$geojsonData = getPolygon($dbh);
$fullUrl = getViewPath();
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<link rel="stylesheet" href="/treecamus/dist/js/leaflet-sidebar/src/L.Control.Sidebar.css">

<header class="flex justify-between items-center mb-8 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Maps</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Live tree location map</p>
    </div>
    <div class="flex items-center gap-2 bg-white/70 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 px-4 py-2 rounded-full text-xs text-slate-500 dark:text-slate-400">
        <a href="/treecamus/dashboard" class="hover:text-emerald-500">Home</a><span>/</span><span class="text-slate-800 dark:text-white font-medium">Map</span>
    </div>
</header>

<div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden">
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-4 flex items-center justify-between">
        <h2 class="text-white font-bold text-lg flex items-center gap-2"><i class="fa-solid fa-map-location-dot"></i> Leaflet Map</h2>
        <span class="text-emerald-50 text-sm">Total Tree: <strong><?php echo statBoxTotalTree($dbh); ?></strong></span>
    </div>
    <div class="p-4">
        <div id="leafletmap" style="width: 100%; height: 560px; border-radius: 0.75rem; overflow: hidden;">
            <div id="sidebar" class="sidebarleaflet"></div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="/treecamus/dist/js/leaflet-sidebar/src/L.Control.Sidebar.js"></script>

<script>
    // Object definition providing getImageUrl to leafletmap_inSys.js
    window.imageLoader = window.imageLoader || {
        getImageUrl: function(imageName) {
            if (!imageName || imageName === 'no_img_found.png') {
                return '/treecamus/images/no_img_found.png';
            }
            if (imageName.startsWith('http') || imageName.startsWith('/')) {
                return imageName;
            }
            return '/treecamus/uploads/' + imageName;
        }
    };
</script>

<script type="module">
    import { initLeafletMap } from '/treecamus/dist/js/leafletmap_inSys.js';
    document.addEventListener("DOMContentLoaded", function () {
        var data = <?php echo json_encode($marker); ?>;
        var geojsonData = <?php echo $geojsonData ? $geojsonData : '{}'; ?>;
        var treeViewUrl = <?php echo json_encode($fullUrl); ?>;
        initLeafletMap(data, geojsonData, treeViewUrl);
    });
</script>

<?php include '../includes/footer.php'; ?>