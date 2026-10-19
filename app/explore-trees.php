<?php
ini_set('display_errors', 0);

$current_page = 'info';

require 'connection/connection.php';
require 'function/function.php';
require 'settings/settings.php';

//require 'requirements/requirements.php';
require 'google-api/vendor/autoload.php';

session_start();

loadEnv(__DIR__ . '/../.env');

/* ---- Only the Google sign-in link is needed on this public page.
        The OAuth code exchange happens in index.php (the registered redirect URI),
        so no client secret and no PHPMailer are needed here. ---- */
$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setRedirectUri((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/treecamus/index.php');
$client->addScope("email");
$client->addScope("profile");

// The tree token must be valid, otherwise go back to the public general-information page
if (!isset($_GET['id']) || checkAllTreerecord($dbh, trim($_GET['id'])) != true) {
    header("Location: /treecamus/general-information");
    exit;
}
$token = trim($_GET['id']);
$treeID = getTreeID($dbh, $token);

/* ---- map: php to leaflet through json ---- */
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

$marker = array();
while ($d = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $marker[] = array(
        "lat" => $d["latitude"], "lon" => $d["longitude"], "treeID" => $d["treeUniqueCode"],
        "speciesDetails" => $d["speciestitle"], "familyDetails" => $d["familytitle"],
        "imageFileName" => $d["imageFileName"], "treetoken" => $d["treetoken"],
    );
}
$geojsonData = getPolygon($dbh);
$fullUrl = getViewPathForPublicAccessScreen(); // marker link -> /treecamus/explore-trees/<token>

// Primary image: same lookup as getPrimaryImageForTreeViewScreen() in viewtree.php
// (falls back to the default "no image" picture)
$imgStmt = $dbh->prepare("SELECT imageFileName FROM images WHERE treeID = :treeID AND imagePrimary = 1 LIMIT 1");
$imgStmt->execute(['treeID' => $treeID]);
$primaryImage = $imgStmt->fetchColumn();

$primaryImageSrc = $primaryImage
    ? '/treecamus/evidences/' . rawurlencode($primaryImage)
    : '/treecamus/dist/img/default_error_img_map/no_img_found.png';

/* ---- Health Class & Growing Class (same tables/columns as viewtree.php) ---- */
$HC = ['link' => 'trees_healthclasses',  'pk' => 'tree_health_id',  'linkCol' => 'healthclassid',
       'lookup' => 'healthclasses',  'lookupId' => 'healthclassid',  'lookupTitle' => 'healthclasstitle'];
$GC = ['link' => 'trees_growingclasses', 'pk' => 'tree_growing_id', 'linkCol' => 'growingclassid',
       'lookup' => 'growingclasses', 'lookupId' => 'growingclassid', 'lookupTitle' => 'growingclasstitle'];

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

// Current class = newest record ("Not assigned" when none)
$currentHealthTitle  = $healthRows  ? $healthRows[0]['title']  : false;
$currentGrowingTitle = $growingRows ? $growingRows[0]['title'] : false;

// One row per save (same timestamp): Health and Grow in their own columns
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

// Tree Measurement History: newest first
$mq = $dbh->prepare("SELECT overalheight, circumference, crownwidth, created_at
                     FROM treemeasurement
                     WHERE treeID = :t AND deleted_at IS NULL
                     ORDER BY created_at DESC, measurementid DESC");
$mq->execute([':t' => $treeID]);
$measurementHistory = $mq->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php browsertitle(); ?></title>
<link rel="shortcut icon" href="/treecamus/images/ugc-logo.png">
<link rel="manifest" href="/treecamus/manifest.json">

<script src="https://cdn.tailwindcss.com"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
  tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        fontFamily: {
          sans: ['Plus Jakarta Sans', 'sans-serif'],
          mono: ['JetBrains Mono', 'monospace'],
        },
        colors: {
          darkBg: '#070a12',
          lightBg: '#f4f7f5',
          glassBg: 'rgba(15, 23, 42, 0.65)',
          glassBgLight: 'rgba(255, 255, 255, 0.75)',
          emeraldGlow: '#10b981',
          cyanGlow: '#06b6d4',
          amberGlow: '#f59e0b',
          purpleGlow: '#8b5cf6',
        }
      }
    }
  }
</script>

<script>
  if (localStorage.getItem('theme') === 'dark' ||
     (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  }
</script>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<link rel="stylesheet" href="/treecamus/dist/js/leaflet-sidebar/src/L.Control.Sidebar.css">

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
  /* DataTables restyled to match the glass theme */
  .dataTables_wrapper .dataTables_paginate { display: flex; align-items: center; gap: 0.35rem; margin-top: 1rem; flex-wrap: wrap; }
  .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0.4rem 0.75rem; border-radius: 0.65rem; font-size: 0.75rem; font-weight: 500; border: 1px solid rgba(148,163,184,0.25); color: inherit; cursor: pointer; }
  html.dark .dataTables_wrapper .dataTables_paginate .paginate_button { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #e2e8f0; }
  .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: linear-gradient(to right, #059669, #10b981); color: #fff !important; border-color: transparent; }
  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { opacity: 0.35; cursor: not-allowed; }
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) { border-color: rgba(16,185,129,0.5); color: #10b981; }
  .dataTables_wrapper .dataTables_info { font-size: 0.75rem; color: #64748b; margin-top: 1rem; }
  html.dark .dataTables_wrapper .dataTables_info { color: #94a3b8; }
  table.dataTable thead th { border-bottom: none !important; }
  table.dataTable tbody td { border-top: none !important; }
  #listTreeMeasurement tbody td, #listTreeClasses tbody td { padding: 0.75rem 0.5rem; }

  /* rows printed by the helper functions (images / sensor) */
  table.rows-tw td { padding: 0.75rem 0.5rem; }
  .view-image { background: linear-gradient(to right, #059669, #10b981); color: #fff; border: none; border-radius: 0.6rem; padding: 0.35rem 0.7rem; font-size: 0.75rem; cursor: pointer; }
  .view-image:hover { opacity: 0.9; }

  /* image viewer modal */
  .modal-zoomable { cursor: zoom-in; transition: transform 0.2s; }
  .modal-zoomable.zoomed { transform: scale(1.5); }
  .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; overflow-y: auto; z-index: 1050; background: rgba(0,0,0,0.6); }
  .modal.show { display: block; }
  .modal-dialog { margin: 3rem auto; max-width: 800px; position: relative; }
  .modal-dialog-centered { display: flex; align-items: center; min-height: calc(100% - 3rem); }
  .modal-content { background: #0f172a; color: #f1f5f9; border: 1px solid rgba(255,255,255,0.1); border-radius: 1rem; overflow: hidden; width: 100%; }
  .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
  .modal-body { padding: 1rem 1.25rem; }
  .modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.08); }
  .modal-title { margin: 0; }
  .close { background: none; border: none; color: inherit; cursor: pointer; font-size: 1.25rem; line-height: 1; }
</style>
</head>
<body class="bg-lightBg dark:bg-darkBg text-slate-800 dark:text-slate-100 font-sans min-h-screen relative overflow-x-hidden">

<div class="fixed top-[-100px] left-[-100px] w-[500px] h-[500px] rounded-full bg-emerald-500/10 blur-[120px] pointer-events-none z-0"></div>
<div class="fixed bottom-[-200px] right-[-100px] w-[600px] h-[600px] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>

<div class="flex min-h-screen relative z-10">

<!-- ===== Sidebar (Guest version) ===== -->
<aside class="w-64 bg-slate-950/75 backdrop-blur-xl border-r border-white/10 p-6 flex flex-col fixed h-screen z-50">

    <div class="flex items-center gap-3 pb-6 border-b border-white/10">
        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-[0_0_15px_rgba(16,185,129,0.3)] overflow-hidden">
            <img src="/treecamus/images/logo UGC.png" alt="TREE CaMUs logo" class="w-full h-full object-contain p-1">
        </div>
        <div>
            <h2 class="text-base font-bold tracking-tight leading-tight text-white">TREE CaMUs</h2>
            <span class="text-xs font-semibold text-emerald-400">UiTM Green Centre</span>
        </div>
    </div>

    <div class="flex items-center gap-3 my-5 p-3 bg-white/[0.03] border border-white/10 rounded-xl">
        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white">
            <i class="fa-solid fa-earth-asia text-sm"></i>
        </div>
        <div class="overflow-hidden">
            <h4 class="text-xs font-semibold text-white">Guest</h4>
            <span class="text-[10px] font-bold text-emerald-400 tracking-wider">PUBLIC ACCESS</span>
        </div>
    </div>

    <nav class="flex flex-col gap-1.5 mt-2">
        <?php
        $public_links = [
            ['key' => 'home',   'href' => '/treecamus/home',  'icon' => 'fa-house',       'label' => 'Home',                'target' => ''],
            ['key' => 'stats',  'href' => '/treecamus/public-statistics', 'icon' => 'fa-chart-line',  'label' => 'Statistics',          'target' => ''],
            ['key' => 'info',   'href' => '/treecamus/general-information',   'icon' => 'fa-circle-info', 'label' => 'General Information', 'target' => ''],
            ['key' => 'manual', 'href' => '/treecamus/manual/Tree-CaMUS User Manual.pdf', 'icon' => 'fa-book-open', 'label' => 'User Manual', 'target' => '_blank'],
        ];
        foreach ($public_links as $link): $active = ($current_page === $link['key']);
        ?>
            <a href="<?= htmlspecialchars($link['href']) ?>" <?= $link['target'] ? 'target="' . $link['target'] . '"' : '' ?>
               class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all
               <?= $active
                   ? 'text-white bg-emerald-500/10 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
                   : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' ?>">
                <i class="fa-solid <?= $link['icon'] ?> <?= $active ? 'text-emerald-400' : '' ?> w-5"></i>
                <span><?= htmlspecialchars($link['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="mt-auto pt-4 border-t border-white/10 space-y-2">
        <button id="theme-toggle"
                class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-slate-400 hover:text-white hover:bg-white/[0.04] transition-all">
            <i class="fa-solid fa-sun w-5 dark:hidden"></i>
            <i class="fa-solid fa-moon w-5 hidden dark:inline"></i>
            <span class="dark:hidden">Light mode</span>
            <span class="hidden dark:inline">Dark mode</span>
        </button>
    </div>
</aside>

<main class="ml-64 flex-1 p-8">

<header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">View Tree</h1>
        <div class="mt-2 inline-flex items-center gap-2 bg-white/70 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 px-4 py-2 rounded-full text-xs text-slate-500 dark:text-slate-400">
            <a href="/treecamus/info.php" class="hover:text-emerald-500">General Information</a><span>/</span>
            <span class="text-slate-800 dark:text-white font-medium">View Tree</span>
        </div>
    </div>
    <div class="flex items-center gap-3">
            <div class="flex items-center gap-3">
        <!-- Install App -->
        <div id="installButtonContainer" style="display: none;">
            <button id="installButton" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-slate-900 text-white dark:bg-white/10 border border-white/10 hover:opacity-90 transition-all">
                <i class="fa-solid fa-download"></i> Install App
            </button>
        </div>
        <!-- Sign in with Google -->
        <a href="<?php echo htmlspecialchars($client->createAuthUrl()); ?>"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-white text-slate-800 border border-slate-300 shadow-sm hover:bg-slate-50 transition-all">
            <img src="/treecamus/images/google.svg" alt="" class="w-5 h-5">
            Sign in with Google
        </a>
    </div>
    </div>
</header>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

    <div class="space-y-4">
        <div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-4 py-3"><h3 class="text-white font-semibold text-sm flex items-center gap-2"><i class="fa-solid fa-tree"></i> Image Thumbnail</h3></div>
            <img src="<?php echo htmlspecialchars($primaryImageSrc); ?>" alt="Tree Image" class="w-full h-56 object-cover">
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
        <div class="p-4 space-y-3">
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Tree Unique Code</label>
                <input type="text" value="<?php echo htmlspecialchars(getTreeName($dbh, $token)); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Coordinate</label>
                    <input type="text" value="<?php echo htmlspecialchars(getTreeCoordinate($dbh, $token)); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Campus</label>
                    <input type="text" value="<?php echo htmlspecialchars(getcampusNameSelected($dbh, $token)); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Zone</label>
                    <input type="text" value="<?php echo htmlspecialchars(getZoneNameSelected($dbh, $token)); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Family</label>
                    <input type="text" value="<?php echo htmlspecialchars(getfamilyNameSelected($dbh, $token)); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Species</label>
                    <input type="text" value="<?php echo htmlspecialchars(getSpeciesNameSelected($dbh, $token)); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Tree Status</label>
                    <input type="text" value="<?php echo htmlspecialchars(getTreestatusSelected($dbh, $token)); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">PIC</label>
                    <input type="text" value="<?php echo htmlspecialchars(getArboristSelected($dbh, $token)); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
                <div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Tag ID</label>
                    <input type="text" value="<?php echo htmlspecialchars(gettagID($dbh, $token)); ?>" readonly class="w-full rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-2 text-sm"></div>
            </div>
        </div>
    </div>
</div>

<!-- Tree Measurement History (read-only) -->
<div class="mb-6 bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-lg">
    <div class="bg-slate-100 dark:bg-white/5 px-6 py-4 flex items-center justify-between border-b border-slate-200 dark:border-white/10">
        <h3 class="font-semibold text-base text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-ruler-combined text-emerald-500"></i> Tree Measurement History
        </h3>
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
                <?php foreach ($measurementHistory as $i => $r): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo $r['overalheight'] !== null ? number_format((float)$r['overalheight'], 2) : '&mdash;'; ?></td>
                    <td><?php echo $r['circumference'] !== null ? number_format((float)$r['circumference'], 2) : '&mdash;'; ?></td>
                    <td><?php echo $r['crownwidth'] !== null ? number_format((float)$r['crownwidth'], 2) : '&mdash;'; ?></td>
                    <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tree Health & Growth History (read-only) -->
<div class="mb-6 bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-lg">
    <div class="bg-slate-100 dark:bg-white/5 px-6 py-4 flex items-center justify-between border-b border-slate-200 dark:border-white/10">
        <h3 class="font-semibold text-base text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-heart-pulse text-rose-500"></i> Tree Health &amp; Growth History
        </h3>
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
                <?php foreach ($classHistory as $i => $r): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo $r['health'] !== null ? htmlspecialchars($r['health']) : '&mdash;'; ?></td>
                    <td><?php echo $r['growing'] !== null ? htmlspecialchars($r['growing']) : '&mdash;'; ?></td>
                    <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 2-Column Section for Tree Images & Sensor ID Record -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-lg">
        <div class="bg-slate-100 dark:bg-white/5 px-6 py-4 border-b border-slate-200 dark:border-white/10">
            <h3 class="font-semibold text-base text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-images text-cyan-500"></i> Tree Images
            </h3>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="rows-tw w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-white/10">
                        <th class="py-3 px-2">#</th>
                        <th class="py-3 px-2">File Name</th>
                        <th class="py-3 px-2">View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <?php echo getImage($dbh, $token); ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-lg">
        <div class="bg-slate-100 dark:bg-white/5 px-6 py-4 border-b border-slate-200 dark:border-white/10">
            <h3 class="font-semibold text-base text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-microchip text-purple-500"></i> Sensor ID Record
            </h3>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="rows-tw w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-white/10">
                        <th class="py-3 px-2">#</th>
                        <th class="py-3 px-2">Sensor ID</th>
                        <th class="py-3 px-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <?php echo getcensorIDAndStatus($dbh, $treeID); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="mt-6 text-xs text-slate-400 dark:text-slate-500 flex justify-between">
    <strong><?php copyright(); ?></strong>
    <span>Version 3.2.0</span>
</footer>

<!-- Loading spinner -->
<div class="loading-spinner fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[2000] text-center" style="display:none;">
    <div class="animate-spin h-8 w-8 border-4 border-emerald-500 border-t-transparent rounded-full mx-auto"></div>
    <p class="mt-2 text-sm">Loading...</p>
</div>

<!-- Image viewer modal -->
<div class="modal" id="imageModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-semibold">Image Viewer</h5>
                <button type="button" class="close" data-close-image>&times;</button>
            </div>
            <div class="modal-body text-center overflow-hidden">
                <img id="modalImage" src="" alt="Image" class="max-w-full mx-auto modal-zoomable rounded-lg">
            </div>
            <div class="modal-footer">
                <button type="button" class="bg-gradient-to-r from-emerald-600 to-emerald-500 text-white text-sm px-4 py-2 rounded-xl" data-close-image>Close</button>
            </div>
        </div>
    </div>
</div>

</main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="/treecamus/dist/js/leaflet-sidebar/src/L.Control.Sidebar.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script type="module">
  import { initLeafletMap } from '/treecamus/dist/js/leafletmap_pub.js';
  document.addEventListener("DOMContentLoaded", function () {
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

      var map = initLeafletMap(data, geojsonData, treeViewUrl);

      // Recalculate map size once the layout has settled
      setTimeout(function () {
          var m = map || window.map;
          if (m && m.invalidateSize) m.invalidateSize();
      }, 300);
  });
</script>

<script>
  $(function () {
      var tableCfg = { paging: true, lengthChange: false, searching: false, ordering: true, order: [], info: true, autoWidth: false, responsive: true, pageLength: 4 };
      $('#listTreeMeasurement').DataTable(tableCfg);
      $('#listTreeClasses').DataTable(tableCfg);

      // ---- Image viewer ----
      var zoomed = false;
      var loadingSpinner = document.querySelector('.loading-spinner');
      function closeImageModal() { $('#imageModal').removeClass('show'); $('#modalImage').removeClass('zoomed').attr('src', ''); zoomed = false; }

      $(document).on('click', '.view-image', function () {
          var filename = $(this).data('image');
          loadingSpinner.style.display = 'block';
          $.ajax({
              type: 'POST',
              url: '/treecamus/tree/displayIMG_secured_ajax.php',
              data: { filename: filename },
              xhrFields: { responseType: 'blob' },
              success: function (response) {
                  loadingSpinner.style.display = 'none';
                  if (response.size > 0) {
                      $('#modalImage').attr('src', URL.createObjectURL(response));
                      $('#imageModal').addClass('show');
                  } else {
                      alert('Image not found or invalid.');
                  }
              },
              error: function () { loadingSpinner.style.display = 'none'; alert('Error loading the image.'); }
          });
      });

      $('#modalImage').on('click', function () { zoomed = !zoomed; $(this).toggleClass('zoomed', zoomed); });
      $(document).on('click', '[data-close-image]', closeImageModal);
      $('#imageModal').on('click', function (e) { if (e.target === this) closeImageModal(); });
  });

  // ---- Light / dark toggle ----
  var themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
      themeToggle.addEventListener('click', function () {
          var isDark = document.documentElement.classList.toggle('dark');
          localStorage.setItem('theme', isDark ? 'dark' : 'light');
      });
  }
</script>

<script src="/treecamus/index.js" type="module"></script>
</body>
</html>
