<?php
require __DIR__ . '/../init.php';

// ---- Map Data Fetching ----
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

$stmt =$dbh->prepare($sql);$stmt->execute();
$rowCount =$stmt->rowCount();

if ($rowCount > 0) {$marker = array();
    while ($d = $stmt->fetch(PDO::FETCH_ASSOC)) {$marker[] = array(
            "lat" => $d["latitude"], "lon" => $d["longitude"], "treeID" => $d["treeUniqueCode"],
            "speciesDetails" => $d["speciestitle"], "familyDetails" => $d["familytitle"],
            "imageFileName" => $d["imageFileName"], "treetoken" => $d["treetoken"]
        );
    }
} else {
    $marker = array();
}
$geojsonData = getPolygon($dbh);$fullUrl = getViewPathForPublicAccessScreen();

// ---- Page setup ----
$current_page = 'home';
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
<script src="https://unpkg.com/lucide@latest"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Leaflet & Map Plugins CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<link rel="stylesheet" href="/treecamus/dist/js/leaflet-sidebar/src/L.Control.Sidebar.css">

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

  #listfamily tbody td, #listspecies tbody td, #listtree tbody td { padding: 0.85rem 1rem; vertical-align: middle; }
  #listfamily tbody tr, #listspecies tbody tr, #listtree tbody tr { border-bottom: 1px solid rgba(148,163,184,0.15); }

  /* login-error notice */
  .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; overflow-y: auto; z-index: 1050; background: rgba(0,0,0,0.6); }
  .modal.show { display: block; }
  .modal-dialog { margin: 3rem auto; max-width: 500px; position: relative; }
  .modal-dialog-centered { display: flex; align-items: center; min-height: calc(100% - 3rem); }
  .modal-content { background: #0f172a; color: #f1f5f9; border: 1px solid rgba(255,255,255,0.1); border-radius: 1rem; overflow: hidden; width: 100%; backdrop-filter: blur(20px); }
  .modal-header { display: flex; align-items: center; justify-between; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
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

<!-- ===== Sidebar ===== -->
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
        foreach ($public_links as $link):$active = ($current_page ===$link['key']);
        ?>
            <a href="<?= htmlspecialchars($link['href']) ?>" <?= $link['target'] ? 'target="' . $link['target'] . '"' : '' ?>
               class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all
               <?= $active
                   ? 'text-white bg-emerald-500/10 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
                   : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' ?>">
                <i class="fa-solid <?= $link['icon'] ?> <?=$active ? 'text-emerald-400' : '' ?> w-5"></i>
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

<header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center space-x-3">
            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">Home</h2>
            <span class="flex h-2.5 w-2.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <!-- Install App -->
        <!-- <div id="installButtonContainer" style="display: none;">
            <button id="installButton" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-slate-900 text-white dark:bg-white/10 border border-white/10 hover:opacity-90 transition-all">
                <i class="fa-solid fa-download"></i> Install App
            </button>
        </div> -->
        <!-- Sign in with Google -->
        <a href="<?php echo htmlspecialchars($client->createAuthUrl()); ?>"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-white text-slate-800 border border-slate-300 shadow-sm hover:bg-slate-50 transition-all">
            <img src="/treecamus/images/google.svg" alt="" class="w-5 h-5">
            Sign in with Google
        </a>
    </div>
</header>

<!-- ===== Leaflet Map Card Section ===== -->
<div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden mb-6">
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

<footer class="mt-6 text-xs text-slate-400 dark:text-slate-500 flex justify-between">
    <strong><?php copyright(); ?></strong>
    <span>Version 3.2.0</span>
</footer>

<!-- Login error notice (shown after ?error) -->
<div class="modal <?php echo isset($_GET['error']) ? 'show' : ''; ?>" id="modal-error">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-semibold text-amber-400">Warning</h4>
                <button type="button" class="close" onclick="document.getElementById('modal-error').classList.remove('show')">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-sm">Unable to log into the system. Please contact your Administrator for registering purposes.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="text-sm px-4 py-2 rounded-xl border border-white/10" onclick="document.getElementById('modal-error').classList.remove('show')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Map Libraries Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="/treecamus/dist/js/leaflet-sidebar/src/L.Control.Sidebar.js"></script>

<script>
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

<script src="/treecamus/index.js" type="module"></script>
<script>
//   window.addEventListener('load', function () {
//     if ('serviceWorker' in navigator) {
//       navigator.serviceWorker.register('/treecamus/sw.js').catch(function () { console.log('SW registration failed'); });
//     }
//   });

  $(function () {
    const cfg = { responsive: true, lengthChange: false, autoWidth: false, searching: true, dom: 'rtip' };
    const family  = $('#listfamily').DataTable(cfg);
    const species = $('#listspecies').DataTable(cfg);
    const trees   = $('#listtree').DataTable(cfg);
    $('#familySearch').on('input',  function () { family.search(this.value).draw(); });
    $('#speciesSearch').on('input', function () { species.search(this.value).draw(); });
    $('#treeSearch').on('input',    function () { trees.search(this.value).draw(); });
  });
</script>

</main>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
  if (window.lucide) { lucide.createIcons(); }
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const html = document.documentElement;
        const isDark = html.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
  }
</script>
</body>
</html>