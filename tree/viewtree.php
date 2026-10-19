<?php
$current_page = 'alltree';
include_once '../connection/connection.php';
include_once '../function/function.php';
include_once '../settings/settings.php';

if (!isset($_GET['id']) || checkAllTreerecord($dbh, trim($_GET['id'])) != true) {
    header("Location: /treecamus/tree");
    exit();
}

$token = trim($_GET['id']);
$treeID = getTreeID($dbh, $token);

/* ---- Health Class & Growing Class: table/column names ---- */
$HC = ['link' => 'trees_healthclasses',  'pk' => 'tree_health_id',  'linkCol' => 'healthclassid',
       'lookup' => 'healthclasses',  'lookupId' => 'healthclassid',  'lookupTitle' => 'healthclasstitle'];
$GC = ['link' => 'trees_growingclasses', 'pk' => 'tree_growing_id', 'linkCol' => 'growingclassid',
       'lookup' => 'growingclasses', 'lookupId' => 'growingclassid', 'lookupTitle' => 'growingclasstitle'];

/* ---- AJAX: add a Health Class / Growing Class record ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'saveTreeClasses') {
    header('Content-Type: application/json');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $healthID = ($_POST['healthclass']  ?? '') !== '' ? (int)$_POST['healthclass']  : null;
    $growID   = ($_POST['growingclass'] ?? '') !== '' ? (int)$_POST['growingclass'] : null;

    if ($healthID === null && $growID === null) {
        echo json_encode(['success' => false, 'message' => 'Please select at least one class.']);
        exit();
    }

    $now = $dbh->query("SELECT NOW()")->fetchColumn();

    $addClass = function ($cfg, $val) use ($dbh, $treeID, $now) {
        if ($val === null) return true;

        $chk = $dbh->prepare("SELECT COUNT(*) FROM {$cfg['lookup']} WHERE {$cfg['lookupId']} = :v");
        $chk->execute([':v' => $val]);
        if ($chk->fetchColumn() == 0) return false;

        $dbh->prepare("INSERT INTO {$cfg['link']} (treeID, {$cfg['linkCol']}, created_at, updated_at, deleted_at) VALUES (:t, :v, :c, :u, NULL)")
            ->execute([':t' => $treeID, ':v' => $val, ':c' => $now, ':u' => $now]);
        return true;
    };

    try {
        $dbh->beginTransaction();
        $ok = $addClass($HC, $healthID) && $addClass($GC, $growID);
        if (!$ok) { $dbh->rollBack(); echo json_encode(['success' => false, 'message' => 'Invalid class selected.']); exit(); }
        $dbh->commit();
        echo json_encode(['success' => true, 'message' => 'You have successfully added the tree class record.']);
    } catch (Exception $e) {
        if ($dbh->inTransaction()) $dbh->rollBack();
        echo json_encode(['success' => false, 'message' => 'Save failed: ' . $e->getMessage()]);
    }
    exit();
}

include '../includes/header.php';
include '../includes/menu.php';

if (isset($_POST['imageFileName'])) {
    $imageFileName = $_POST['imageFileName'];
    echo json_encode($response);
}

$data = ["treeToken" => $token];
$sql = "SELECT tr.latitude, tr.longitude, sp.speciestitle, fm.familytitle, tr.treetoken,
         CASE WHEN tr.treeUniqueCode IS NULL THEN 'No Unique Code' ELSE tr.treeUniqueCode END as treeUniqueCode,
         COALESCE(im.imageFileName, 'no_img_found.png') as imageFileName
       FROM trees tr
       JOIN species sp ON tr.speciesID = sp.speciesID
       JOIN family fm ON fm.familyID = tr.familyID
       LEFT JOIN images im ON im.treeID = tr.treeID AND im.imagePrimary = 1
       WHERE tr.treetoken = :treeToken";
$stmt = $dbh->prepare($sql);
$stmt->execute($data);
$rowCount = $stmt->rowCount();

if ($rowCount > 0) {
    $marker = array();
    while ($d = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $marker[] = array(
            "lat" => $d["latitude"], "lon" => $d["longitude"], "treeID" => $d["treeUniqueCode"],
            "speciesDetails" => $d["speciestitle"], "familyDetails" => $d["familytitle"],
            "imageFileName" => $d["imageFileName"], "treetoken" => $d["treetoken"],
        );
    }
} else {
    $marker = array();
}
$geojsonData = getPolygon($dbh);
$fullUrl = getViewPathForViewTreeScreen();

/* ---- Health Class & Growing Class dropdowns and history ---- */
$healthOptions  = $dbh->query("SELECT {$HC['lookupId']} AS id, {$HC['lookupTitle']} AS title FROM {$HC['lookup']} ORDER BY {$HC['lookupId']}")->fetchAll(PDO::FETCH_ASSOC);
$growingOptions = $dbh->query("SELECT {$GC['lookupId']} AS id, {$GC['lookupTitle']} AS title FROM {$GC['lookup']} ORDER BY {$GC['lookupId']}")->fetchAll(PDO::FETCH_ASSOC);

$fetchClassRows = function ($cfg) use ($dbh, $treeID) {
    $q = $dbh->prepare("SELECT l.created_at, COALESCE(lk.{$cfg['lookupTitle']}, CONCAT('ID ', l.{$cfg['linkCol']})) AS title
                        FROM {$cfg['link']} l
                        LEFT JOIN {$cfg['lookup']} lk ON lk.{$cfg['lookupId']} = l.{$cfg['linkCol']}
                        WHERE l.treeID = :t AND l.deleted_at IS NULL
                        ORDER BY l.created_at DESC, l.{$cfg['pk']} DESC");
    $q->execute([':t' => $treeID]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
};
$healthRows  = $fetchClassRows($HC);
$growingRows = $fetchClassRows($GC);

$currentHealthTitle  = $healthRows  ? $healthRows[0]['title']  : false;
$currentGrowingTitle = $growingRows ? $growingRows[0]['title'] : false;

$classHistoryMap = [];
foreach ($healthRows as $r) {
    $k = $r['created_at'];
    if (!isset($classHistoryMap[$k])) $classHistoryMap[$k] = ['created_at' => $k, 'health' => null, 'growing' => null];
    if ($classHistoryMap[$k]['health'] === null) $classHistoryMap[$k]['health'] = $r['title'];
}
foreach ($growingRows as $r) {
    $k = $r['created_at'];
    if (!isset($classHistoryMap[$k])) $classHistoryMap[$k] = ['created_at' => $k, 'health' => null, 'growing' => null];
    if ($classHistoryMap[$k]['growing'] === null) $classHistoryMap[$k]['growing'] = $r['title'];
}
krsort($classHistoryMap);
$classHistory = array_values($classHistoryMap);

$mq = $dbh->prepare("SELECT overalheight, circumference, crownwidth, created_at
                     FROM treemeasurement
                     WHERE treeID = :t AND deleted_at IS NULL
                     ORDER BY created_at DESC, measurementid DESC");
$mq->execute([':t' => $treeID]);
$measurementHistory = $mq->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<link rel="stylesheet" href="/treecamus/dist/js/leaflet-sidebar/src/L.Control.Sidebar.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/treecamus/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<style>
  .modal-zoomable { cursor: zoom-in; transition: transform 0.2s; }
  .modal-zoomable.zoomed { transform: scale(1.5); }
</style>

<header class="flex justify-between items-center mb-8 flex-wrap gap-4">
    <div><h1 class="text-2xl font-bold tracking-tight">View Tree</h1></div>
    <div class="flex items-center gap-2 bg-white/70 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 px-4 py-2 rounded-full text-xs text-slate-500 dark:text-slate-400">
        <a href="/treecamus/dashboard" class="hover:text-emerald-500">Home</a><span>/</span>
        <a href="/treecamus/tree" class="hover:text-emerald-500">List Of Tree</a><span>/</span>
        <span class="text-slate-800 dark:text-white font-medium">View Tree</span>
    </div>
</header>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

    <div class="space-y-4">
        <div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-4 py-3"><h3 class="text-white font-semibold text-sm flex items-center gap-2"><i class="fa-solid fa-tree"></i> Image Thumbnail</h3></div>
            <?php
            // Primary image with absolute paths (the helper's relative "../evidences/" breaks under clean URLs)
            $thumbStmt = $dbh->prepare("SELECT imageFileName FROM images WHERE treeID = :treeID AND imagePrimary = 1 LIMIT 1");
            $thumbStmt->execute(['treeID' => $treeID]);
            $thumbFile = $thumbStmt->fetchColumn();
            $thumbSrc = $thumbFile
                ? '/treecamus/evidences/' . rawurlencode($thumbFile)
                : '/treecamus/dist/img/default_error_img_map/no_img_found.png';
            ?>
            <img src="<?php echo htmlspecialchars($thumbSrc); ?>" alt="Tree Image" class="w-full h-56 object-cover" onerror="this.onerror=null;this.src='/treecamus/dist/img/default_error_img_map/no_img_found.png';">
        </div>

        <div class="bg-purple-600/90 rounded-2xl p-4 text-white">
            <div class="text-xs text-purple-200 flex items-center gap-1"><i class="fa-regular fa-calendar"></i> Created At</div>
            <div class="font-semibold"><?php echo getCreatedAt($dbh, $token); ?></div>
            <div class="text-xs text-purple-200 mt-1"><?php echo getcampusNameSelected($dbh, $token); ?></div>
        </div>

        <div class="bg-emerald-600/90 rounded-2xl p-4 text-white">
            <div class="text-xs text-emerald-100 flex items-center gap-1"><i class="fa-solid fa-tag"></i> Tree Unique Code</div>
            <div class="font-semibold"><?php echo getTreeName($dbh, $token); ?></div>
            <div class="text-xs text-emerald-100 mt-1"><?php echo getTreeCoordinate($dbh, $token); ?></div>
        </div>
    </div>

    <div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-cyan-600 to-cyan-500 px-4 py-3 flex items-center justify-between">
            <h3 class="text-white font-semibold text-sm flex items-center gap-2"><i class="fa-solid fa-map-location-dot"></i> Leaflet Map</h3>
            <span class="text-white text-xs">Total Tree: <strong><?php echo statBoxTotalTree($dbh); ?></strong></span>
        </div>
        <div class="p-3">
            <div id="leafletmap" style="width: 100%; height: 480px; border-radius: 0.75rem; overflow: hidden;">
                <div id="sidebar" class="sidebarleaflet"></div>
            </div>
        </div>
    </div>

    <div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-cyan-600 to-cyan-500 px-4 py-3"><h3 class="text-white font-semibold text-sm flex items-center gap-2"><i class="fa-solid fa-circle-info"></i> Tree Details</h3></div>
        <form role="form" name="viewtree" method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data" class="p-4 space-y-3">
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Tree Unique Code</label>
                <input type="text" value="<?php echo getTreeName($dbh, $token); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Coordinate</label>
                    <input type="text" value="<?php echo getTreeCoordinate($dbh, $token); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Campus</label>
                    <input type="text" value="<?php echo getcampusNameSelected($dbh, $token); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Zone</label>
                    <input type="text" value="<?php echo getZoneNameSelected($dbh, $token); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Family</label>
                    <input type="text" value="<?php echo getfamilyNameSelected($dbh, $token); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Species</label>
                    <input type="text" value="<?php echo getSpeciesNameSelected($dbh, $token); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Tree Status</label>
                    <input type="text" value="<?php echo getTreestatusSelected($dbh, $token); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">PIC</label>
                    <input type="text" value="<?php echo getArboristSelected($dbh, $token); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Tag ID</label>
                    <input type="text" value="<?php echo gettagID($dbh, $token); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
            </div>
        </form>
    </div>
</div>

<!-- Full Width Tree Measurement Section -->
<div class="mb-6 bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-lg">
    <div class="bg-slate-100 dark:bg-white/5 px-6 py-4 flex items-center justify-between border-b border-slate-200 dark:border-white/10">
        <h3 class="font-semibold text-base text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-ruler-combined text-emerald-500"></i> Tree Measurement History
        </h3>
        <button type="button" data-toggle="modal" data-target="#measurement-modal" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-white text-xs px-4 py-2 rounded-xl font-medium shadow-md transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Measurement
        </button>
    </div>
    <div class="p-6">
        <table id="listTreeMeasurement" class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-white/10">
                    <th class="py-3 px-2">#</th>
                    <th class="py-3 px-2">Height (feet)</th>
                    <th class="py-3 px-2">Circumference</th>
                    <th class="py-3 px-2">Crown (feet)</th>
                    <th class="py-3 px-2">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php foreach ($measurementHistory as $i =>$r): ?>
                <tr>
                    <td class="py-3 px-2"><?php echo $i + 1; ?></td>
                    <td class="py-3 px-2"><?php echo $r['overalheight'] !== null ? number_format((float)$r['overalheight'], 2) : '&mdash;'; ?></td>
                    <td class="py-3 px-2"><?php echo $r['circumference'] !== null ? number_format((float)$r['circumference'], 2) : '&mdash;'; ?></td>
                    <td class="py-3 px-2"><?php echo $r['crownwidth'] !== null ? number_format((float)$r['crownwidth'], 2) : '&mdash;'; ?></td>
                    <td class="py-3 px-2"><?php echo htmlspecialchars($r['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Health Class & Growing Class -->
<div class="mb-6 bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-lg">
    <div class="bg-slate-100 dark:bg-white/5 px-6 py-4 flex items-center justify-between border-b border-slate-200 dark:border-white/10">
        <h3 class="font-semibold text-base text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-heart-pulse text-rose-500"></i> Tree Health &amp; Growth History
        </h3>
        <button type="button" data-toggle="modal" data-target="#classes-modal" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-white text-xs px-4 py-2 rounded-xl font-medium shadow-md transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Health &amp; Growth
        </button>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-4 py-3">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Current Health Class</div>
                <div class="font-semibold text-slate-800 dark:text-white"><?php echo $currentHealthTitle !== false ? htmlspecialchars($currentHealthTitle) : 'Not assigned'; ?></div>
            </div>
            <div class="rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-4 py-3">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Current Growing Class</div>
                <div class="font-semibold text-slate-800 dark:text-white"><?php echo $currentGrowingTitle !== false ? htmlspecialchars($currentGrowingTitle) : 'Not assigned'; ?></div>
            </div>
        </div>
        <table id="listTreeClasses" class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-white/10">
                    <th class="py-3 px-2">#</th>
                    <th class="py-3 px-2">Health</th>
                    <th class="py-3 px-2">Grow</th>
                    <th class="py-3 px-2">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php foreach ($classHistory as $i =>$r): ?>
                <tr>
                    <td class="py-3 px-2"><?php echo $i + 1; ?></td>
                    <td class="py-3 px-2"><?php echo $r['health'] !== null ? htmlspecialchars($r['health']) : '&mdash;'; ?></td>
                    <td class="py-3 px-2"><?php echo $r['growing'] !== null ? htmlspecialchars($r['growing']) : '&mdash;'; ?></td>
                    <td class="py-3 px-2"><?php echo htmlspecialchars($r['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 2-Column Section for Tree Images & Sensor ID Record -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Tree Images -->
    <div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-lg">
        <div class="bg-slate-100 dark:bg-white/5 px-6 py-4 border-b border-slate-200 dark:border-white/10">
            <h3 class="font-semibold text-base text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-images text-cyan-500"></i> Tree Images
            </h3>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-white/10">
                        <th class="py-3 px-2">#</th>
                        <th class="py-3 px-2">File Name</th>
                        <th class="py-3 px-2 text-right">View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <?php echo getImage($dbh,$token); ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sensor ID Record -->
    <div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-lg">
        <div class="bg-slate-100 dark:bg-white/5 px-6 py-4 border-b border-slate-200 dark:border-white/10">
            <h3 class="font-semibold text-base text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-microchip text-purple-500"></i> Sensor ID Record
            </h3>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-white/10">
                        <th class="py-3 px-2">#</th>
                        <th class="py-3 px-2">Sensor ID</th>
                        <th class="py-3 px-2 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <?php echo getcensorIDAndStatus($dbh,$treeID); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="loading-spinner fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50 text-center" style="display:none;">
    <div class="animate-spin h-8 w-8 border-4 border-emerald-500 border-t-transparent rounded-full mx-auto"></div>
    <p class="mt-2 text-sm">Loading...</p>
</div>

<div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title font-semibold">Image Viewer</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body text-center"><img id="modalImage" src="" alt="Image" class="max-w-full mx-auto modal-zoomable rounded-lg"></div>
        <div class="modal-footer"><button type="button" class="bg-gradient-to-r from-emerald-600 to-emerald-500 text-white text-sm px-4 py-2 rounded-xl" data-dismiss="modal">Close</button></div>
    </div></div>
</div>

<div class="modal fade" id="measurement-modal">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title font-semibold">Add Tree Measurement</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">
            <div id="message"><span class="text-red-400 text-sm" id="error-message"></span></div>
            <form id="measurement-form" class="space-y-3 mt-2">
                <input type="text" id="treeid" name="treeid" value="<?php echo $treeID; ?>" hidden>
                <div><label class="block text-sm font-medium mb-1">Overall Height (m):</label>
                    <input type="number" id="OverallHeight" name="OverallHeight" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-800 dark:text-white"></div>
                <div><label class="block text-sm font-medium mb-1">Circumference (mm):</label>
                    <input type="number" id="circumference" name="circumference" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-800 dark:text-white"></div>
                <div><label class="block text-sm font-medium mb-1">Crown Width (m):</label>
                    <input type="number" id="CrownWidth" name="crown" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-800 dark:text-white"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="text-sm px-4 py-2 rounded-xl border border-white/10" data-dismiss="modal" id="btn_close">Close</button>
            <button type="button" class="bg-gradient-to-r from-emerald-600 to-emerald-500 text-white text-sm px-4 py-2 rounded-xl" id="btn-save">Save</button>
        </div>
    </div></div>
</div>

<div class="modal fade" id="classes-modal">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title font-semibold">Add Health &amp; Growth Class</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">
            <div id="classes-message"><span class="text-red-400 text-sm" id="classes-error-message"></span></div>
            <form id="classes-form" class="space-y-3 mt-2">
                <div><label for="healthClass" class="block text-sm font-medium mb-1">Health Class:</label>
                    <select id="healthClass" name="healthclass" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-800 dark:text-white">
                        <option value="">-- Select Health Class --</option>
                        <?php foreach ($healthOptions as$o): ?>
                            <option value="<?php echo (int)$o['id']; ?>"><?php echo htmlspecialchars($o['title']); ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div><label for="growingClass" class="block text-sm font-medium mb-1">Growing Class:</label>
                    <select id="growingClass" name="growingclass" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-800 dark:text-white">
                        <option value="">-- Select Growing Class --</option>
                        <?php foreach ($growingOptions as$o): ?>
                            <option value="<?php echo (int)$o['id']; ?>"><?php echo htmlspecialchars($o['title']); ?></option>
                        <?php endforeach; ?>
                    </select></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="text-sm px-4 py-2 rounded-xl border border-white/10" data-dismiss="modal" id="btn_close_classes">Close</button>
            <button type="button" class="bg-gradient-to-r from-emerald-600 to-emerald-500 text-white text-sm px-4 py-2 rounded-xl" id="btn-save-classes">Save</button>
        </div>
    </div></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="/treecamus/dist/js/leaflet-sidebar/src/L.Control.Sidebar.js"></script>

<script>
  $(function () {$('#listTreeMeasurement').DataTable({ paging: true, lengthChange: false, searching: false, ordering: true, order: [], info: true, autoWidth: false, responsive: true, pageLength: 4 }); });
</script>

<script type="module">
  import { initLeafletMap } from '/treecamus/dist/js/leafletmap_inSys_viewtree.js';
  document.addEventListener("DOMContentLoaded", function() {
      var data = <?php echo json_encode($marker); ?>;
      var geojsonData = <?php echo $geojsonData; ?>;
      var treeViewUrl = <?php echo json_encode($fullUrl); ?>;
      
      // ---- Marker pin fix: use Leaflet's default pin images from the CDN,
      //      and fall back to them if a custom marker image fails to load ----
      var PIN = 'https://unpkg.com/leaflet@1.9.4/dist/images/';
      L.Icon.Default.mergeOptions({
          iconUrl: PIN + 'marker-icon.png',
          iconRetinaUrl: PIN + 'marker-icon-2x.png',
          shadowUrl: PIN + 'marker-shadow.png'
      });
      document.addEventListener('error', function (e) {
          var t = e.target;
          if (t && t.tagName === 'IMG' && t.classList.contains('leaflet-marker-icon') && !t.dataset.pinFixed) {
              t.dataset.pinFixed = '1';
              t.src = PIN + 'marker-icon.png';
              t.style.width = '25px'; t.style.height = '41px';
              t.style.marginLeft = '-12px'; t.style.marginTop = '-41px';
          }
      }, true);

      // Initialize map
      var map = initLeafletMap(data, geojsonData, treeViewUrl);

      // Force recalculate map size after layout loads
      setTimeout(function() {
          if (map) {
              map.invalidateSize();
          } else if (window.map) {
              window.map.invalidateSize();
          }
      }, 300);
  });
</script>

<script>
  $(document).ready(function () {
      var zoomed = false;
      var loadingSpinner = document.querySelector('.loading-spinner');
      function showLoadingSpinner() { loadingSpinner.style.display = 'block'; }
      function hideLoadingSpinner() { loadingSpinner.style.display = 'none'; }

      $(".view-image").on("click", function () {
          var filename = $(this).data("image");
          showLoadingSpinner();
          $.ajax({
              type: "POST", url: "/treecamus/tree/displayIMG_secured_ajax.php", data: { filename: filename }, xhrFields: { responseType: 'blob' },
              success: function (response) {
                  if (response.size > 0) {
                      var imageUrl = URL.createObjectURL(response);
                      $("#modalImage").attr("src", imageUrl);
                      $("#imageModal").modal("show");
                      $("#modalImage").off("click").on("click", function () { zoomed = !zoomed; $(this).toggleClass("zoomed", zoomed); });
                      hideLoadingSpinner();
                  } else { alert("Image not found or invalid."); hideLoadingSpinner(); }
              },
              error: function () { alert("Error loading the image."); hideLoadingSpinner(); }
          });
      });
  });
</script>

<script>
  $(document).ready(function() {$(document).on('click', '#btn-save', function() {
          var TreeID = $('#treeid').val();
          var Height = $('#OverallHeight').val();
          var Circumference = $('#circumference').val();
          var Width = $('#CrownWidth').val();
          if (Height == "" || Circumference == "" || Width == "") {
              $('#error-message').text('All inputs are required to be filled out');
          } else {
              Swal.fire({
                  position: 'top', icon: 'warning', title: 'Are you sure you want to proceed? <br> This action cannot be undone',
                  showConfirmButton: true, showCancelButton: true, confirmButtonText: `Yes`,
              }).then((result) => {
                  if (result.isConfirmed) {
                      $.ajax({
                          url: '/treecamus/tree/inserttreemeasurement.php', method: 'POST',
                          data: { tid: TreeID, OHeight: Height, CFerence: Circumference, CWidth: Width },
                          success: function() {
                              $('#measurement-modal').modal('hide');
                              $('#measurement-form').trigger('reset');
                              var Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000 });
                              Toast.fire({ icon: "success", title: "You have successfully insert new tree measurement." });
                              setTimeout(function() { window.location.reload(); }, 2000);
                          }
                      });
                  }
              });
          }
      });
      $(document).on('click', '#btn_close', function() { $('#measurement-form').trigger('reset'); $('#error-message').text(''); });
  });
</script>

<script>
  $(function () {$('#listTreeClasses').DataTable({ paging: true, lengthChange: false, searching: false, ordering: true, order: [], info: true, autoWidth: false, responsive: true, pageLength: 4 }); });

  $(document).ready(function () {$(document).on('click', '#btn-save-classes', function () {
          var Health = $('#healthClass').val();
          var Growing = $('#growingClass').val();
          if (Health == "" && Growing == "") {
              $('#classes-error-message').text('Please select at least one class');
          } else {
              Swal.fire({
                  position: 'top', icon: 'warning', title: 'Are you sure you want to proceed? <br> This action cannot be undone',
                  showConfirmButton: true, showCancelButton: true, confirmButtonText: `Yes`,
              }).then((result) => {
                  if (result.isConfirmed) {
                      $.ajax({
                          url: window.location.href, method: 'POST', dataType: 'json',
                          data: { action: 'saveTreeClasses', healthclass: Health, growingclass: Growing },
                          success: function (res) {
                              var Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000 });
                              if (res.success) {
                                  $('#classes-modal').modal('hide');
                                  $('#classes-form').trigger('reset');
                                  Toast.fire({ icon: "success", title: res.message });
                                  setTimeout(function () { window.location.reload(); }, 2000);
                              } else {
                                  Toast.fire({ icon: "error", title: res.message });
                              }
                          },
                          error: function (xhr) { Swal.fire({ icon: 'error', title: 'Failed to save classes.', text: (xhr.responseText || '').substring(0, 300) }); }
                      });
                  }
              });
          }
      });
      $(document).on('click', '#btn_close_classes', function () { $('#classes-form').trigger('reset'); $('#classes-error-message').text(''); });
  });
</script>

<?php include '../includes/footer.php'; ?>