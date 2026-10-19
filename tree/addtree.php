<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../connection/connection.php';
include_once '../function/function.php';
include_once '../settings/settings.php';

// Enable PDO error reporting
if (isset($dbh)) {$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// --- FORM PROCESSING (Runs before HTML headers load) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Resolve User ID with Fallbacks
    $google_id =$_SESSION['login_id'] ?? null;
    $userID = getUsersID($dbh,$google_id);

    // Fallback: If test ID isn't found in DB, default to session user_id or ID 1
    if (!$userID) {
        $userID =$_SESSION['user_id'] ?? 1;
    }

    // 2. Extract inputs from POST
    $treeUniqueName = !empty($_POST['treeUniqueName']) ?$_POST['treeUniqueName'] : null;
    $latitude       =$_POST['latitude'] ?? null;
    $longitude      =$_POST['longitude'] ?? null;
    $campus         = !empty($_POST['campus']) ?$_POST['campus'] : null;
    $zone           = !empty($_POST['zone']) ?$_POST['zone'] : null;
    $family         = !empty($_POST['family']) ?$_POST['family'] : null;
    $species        = !empty($_POST['species']) ?$_POST['species'] : null;
    $treestatus     = !empty($_POST['status']) ?$_POST['status'] : null;
    $tagID          = !empty($_POST['tagID']) ?$_POST['tagID'] : null;
    $censorID       = !empty($_POST['censorID']) ?$_POST['censorID'] : null;
    $arborist       = !empty($_POST['arborist']) ?$_POST['arborist'] : null;

    // 3. Generate tokens
    $treetoken  = getToken(36);
    $imagetoken = getToken(32);$treeID     = microtime(true) . uniqid();
    $tagstoken  = getToken(38);

    // 4. Retrieve chosen thumbnail index early
    $selectedThumbnailIndex = isset($_POST['selectedThumbnail']) ? (int)$_POST['selectedThumbnail'] : -1;

    try {
        // Validation Checks
        if (!empty($tagID) && TagidRecord($dbh,$tagID) == true) {
            header("Location: /treecamus/tree/add?warning");
            exit();
        } else if (!empty($censorID) && CensoridRecord($dbh,$censorID) == true) {
            header("Location: /treecamus/tree/add?warning2");
            exit();
        } else {
            // Save Tree
            saveTree($dbh,$treeUniqueName, $treeID,$latitude, $longitude,$userID, $arborist,$campus, $zone,$family, $species,$treestatus, $treetoken,$tagID);

            // Save Images
            $newfilename = [];
            if (!empty($_FILES['evidence']['name'][0])) {$i = 0;
                $fileCount = count($_FILES['evidence']['name']);
                while ($i <$fileCount) {
                    if ($_FILES['evidence']['error'][$i] === UPLOAD_ERR_OK) {$temp = explode(".", $_FILES['evidence']['name'][$i]);
                        $fileExtension = strtolower(end($temp));
                        $newfilename[$i] = microtime(true) . '_' . $i . '.' .$fileExtension;
                        $destinationPath = "../evidences/" . $newfilename[$i];

                        if (compressAndUploadImage($_FILES['evidence']['tmp_name'][$i],$destinationPath, $fileExtension)) {$isPrimary = ($i ===$selectedThumbnailIndex) ? 1 : 0;
                            saveevidence($dbh,$newfilename[$i],$_FILES['evidence']['type'][$i],$treeID, $imagetoken,$isPrimary);
                        }
                    }
                    $i++;
                }
            }

            // Save Sensor Tag
            if (!empty($censorID)) {
                savetag($dbh,$censorID, $treeID,$tagstoken);
            }

            // Update Primary Image
            if ($selectedThumbnailIndex >= 0 && isset($newfilename[$selectedThumbnailIndex])) {
                updateImagePrimary($dbh,$treeID, $newfilename[$selectedThumbnailIndex]);
            }

            // Redirect on completion
            header("Location: /treecamus/tree?success");
            exit();
        }
    } catch (PDOException $e) {
        echo "<div style='background:#fee2e2; border:2px solid #ef4444; color:#991b1b; padding:20px; margin:20px; font-family:sans-serif; border-radius:8px;'>";
        echo "<h3>Database Error:</h3>";
        echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><b>File:</b> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
        echo "</div>";
        exit();
    } catch (Exception $e) {
        echo "<div style='background:#fef3c7; border:2px solid #f59e0b; color:#92400e; padding:20px; margin:20px; font-family:sans-serif; border-radius:8px;'>";
        echo "<h3>General Error:</h3>";
        echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
        exit();
    }
}

// --- PAGE TEMPLATE RENDER ---
$current_page = 'addtree';
include '../includes/header.php';
include '../includes/menu.php';
?>

<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/select2/js/select2.full.min.js"></script>
<script src="camera.js"></script>

<style>
  .thumbnail-card.selected { outline: 2px solid #10b981; }
  .required { color: #f87171; }
  .select2-container .select2-selection--single {
      height: 42px !important; border-radius: 0.75rem !important;
      display: flex; align-items: center;
  }
</style>

<header class="flex justify-between items-center mb-8 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Add New Tree</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Register a new tree into the inventory</p>
    </div>
    <div class="flex items-center gap-2 bg-white/70 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 px-4 py-2 rounded-full text-xs text-slate-500 dark:text-slate-400">
        <a href="../dashboard/dashboard.php" class="hover:text-emerald-500">Home</a><span>/</span><span class="text-slate-800 dark:text-white font-medium">New Tree</span>
    </div>
</header>

<div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden">
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-4">
        <h2 class="text-white font-bold text-lg">Add New Tree</h2>
        <p class="text-emerald-50 text-sm">Please fill in information below</p>
    </div>

    <form action="" method="post" enctype="multipart/form-data" autocomplete="off" class="p-6 space-y-4">

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tree Unique Code</label>
            <input type="text" name="treeUniqueName" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Latitude<span class="required">*</span></label>
                <input type="text" id="latitude" name="latitude" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Longitude<span class="required">*</span></label>
                <input type="text" id="longitude" name="longitude" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="button" onclick="getLocation()" class="flex-1 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-white text-sm font-semibold rounded-xl py-2.5 shadow-[0_0_15px_rgba(16,185,129,0.3)] transition-all">Get Coordinate</button>
                <button type="button" onclick="clearCoordinate()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-amber-400 text-sm font-semibold rounded-xl py-2.5 transition-all">Clear</button>
            </div>
        </div>

        <div>
            <label for="campus" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Campus<span class="required">*</span></label>
            <select class="select2 w-full" name="campus" data-placeholder="Select a campus" style="width:100%" required>
                <option></option>
                <?php getCampusName($dbh); ?>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Zone</label>
                <select name="zone" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
                    <option value="" selected disabled hidden>Select a Zone</option>
                    <?php getZoneName($dbh); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Family<span class="required">*</span></label>
                <select name="family" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
                    <option value="" selected disabled hidden>Select a family</option>
                    <?php getFamilyName($dbh); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Species<span class="required">*</span></label>
                <select name="species" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
                    <option value="" selected disabled hidden>Select a species</option>
                    <?php getSpeciesName($dbh); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tree Status<span class="required">*</span></label>
                <select name="status" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
                    <option value="" selected disabled hidden>Select a status</option>
                    <?php getTreeStatusName($dbh); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Arborist<span class="required">*</span></label>
                <select name="arborist" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
                    <option value="" selected disabled hidden>Select an arborist</option>
                    <?php getArboristIsAllowedName($dbh); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tag ID</label>
                <input type="text" name="tagID" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Sensor ID</label>
            <input type="text" name="censorID" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">File input <em class="normal-case text-slate-400">(PNG, JPEG)</em></label>
                <button type="button" id="toggleCameraBtn" class="text-xs font-semibold text-emerald-500 hover:text-emerald-400">
                    <i class="fa-solid fa-camera"></i> Use Camera Instead
                </button>
            </div>
            <input type="file" name="evidence[]" id="evidence" multiple class="w-full text-sm rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-slate-800 dark:text-white file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-500 file:text-white file:px-3 file:py-1.5 file:text-sm">
        </div>

        <div id="cameraCapturePanel" class="hidden border border-slate-200 dark:border-white/10 rounded-xl overflow-hidden">
            <div class="bg-slate-100 dark:bg-white/5 px-4 py-2.5">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200"><i class="fa-solid fa-video text-emerald-500"></i> Capture Tree Photo</h3>
            </div>
            <div class="p-4 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Select Camera</label>
                        <select id="camera-list" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-800 dark:text-white"></select>
                    </div>
                    <button type="button" id="start-button" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-white text-sm font-semibold rounded-xl py-2 transition-all">Start Camera</button>
                    <button type="button" id="stop-button" class="bg-white/5 border border-white/10 hover:bg-white/10 text-red-400 text-sm font-semibold rounded-xl py-2 transition-all">Stop Camera</button>
                    <button type="button" id="capture-button" class="bg-white/5 border border-white/10 hover:bg-white/10 text-emerald-400 text-sm font-semibold rounded-xl py-2 transition-all">Capture Image</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Live Preview</p>
                        <video id="camera-preview" autoplay class="w-full rounded-lg bg-black/80"></video>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Captured Snapshot</p>
                        <canvas id="image-canvas" class="w-full rounded-lg bg-black/40"></canvas>
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Captured photos are added to the File input above and go through the same thumbnail selection as uploaded files.</p>
            </div>
        </div>

        <div id="thumbnail-options-card" class="border border-amber-500/30 bg-amber-500/5 rounded-xl overflow-hidden hidden">
            <div class="bg-amber-500/20 px-4 py-2">
                <h3 class="text-amber-400 font-semibold text-sm">Thumbnail Selection</h3>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 px-4 pt-2"><strong>Attention:</strong> Please select an image with the <b>TREE TAG</b> to serve as the thumbnail.</p>
            <div class="flex flex-wrap gap-3 p-4" id="thumbnail-options"></div>
        </div>

        <div class="flex gap-2 pt-2">
            <button type="submit" name="save" id="btnSave" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-[0_0_15px_rgba(16,185,129,0.3)] transition-all">Save</button>
            <button type="button" id="btnCancel" onclick='window.location="/treecamus/tree";' class="border border-slate-300 dark:border-white/10 text-slate-600 dark:text-slate-300 text-sm px-5 py-2.5 rounded-xl hover:bg-white/5">Cancel</button>
        </div>
    </form>
</div>

<div class="modal fade" id="modal-warning">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-amber-400">Warning</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">Invalid file type!</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>
<div class="modal fade" id="modal-warning1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-amber-400">Warning</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">The tag ID already exists. Please insert a new tag ID.</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>
<div class="modal fade" id="modal-warning2">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-amber-400">Warning</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">The sensor ID already exists. Please insert a new sensor ID.</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>
<div class="modal fade" id="thumbnailSelectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-amber-400">Warning</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">Please select a thumbnail image.</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>

<div class="loading-spinner fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50 text-center" style="display:none;">
    <div class="animate-spin h-8 w-8 border-4 border-emerald-500 border-t-transparent rounded-full mx-auto"></div>
    <p class="mt-2 text-sm">Loading...</p>
</div>

<div class="modal" id="mapModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-semibold">Confirm Location</h5>
                <div class="ml-auto flex gap-3">
                    <button type="button" onclick="refreshMap()" title="Refresh Map"><i class="fa-solid fa-arrows-rotate"></i></button>
                    <button type="button" data-dismiss="modal" title="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <div class="modal-body">
                <p class="mt-0 text-sm">Please review the location on the map below:</p>
                <div id="map-spinner" class="text-center">
                    <div class="animate-spin h-8 w-8 border-4 border-emerald-500 border-t-transparent rounded-full mx-auto"></div>
                    <p class="text-sm">Loading...</p>
                </div>
                <div id="map-modal" style="width: 100%; height: 400px; border-radius: 0.75rem; overflow: hidden;"></div>
                <div id="map-error" class="bg-red-500/10 text-red-400 text-sm rounded-lg p-3 mt-3" style="display: none;">Map could not be loaded. Please try again later.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="bg-red-500/20 text-red-400 hover:bg-red-500/30 text-sm px-4 py-2 rounded-xl" data-dismiss="modal" onclick="clearAndClose()">No, I'll enter it manually</button>
                <button type="button" class="bg-gradient-to-r from-emerald-600 to-emerald-500 text-white text-sm px-4 py-2 rounded-xl" onclick="closeModal()">Yes, this is correct</button>
            </div>
        </div>
    </div>
</div>

<script>
  var x = document.getElementById("demo");
  var map;
  var loadingSpinner = document.querySelector('.loading-spinner');
  var mapSpinner = document.querySelector('#map-spinner');
  var mapError = document.querySelector('#map-error');

  function showLoadingSpinner() { loadingSpinner.style.display = 'block'; }
  function hideLoadingSpinner() { loadingSpinner.style.display = 'none'; }
  function showMapSpinner() { mapSpinner.style.display = 'block'; }
  function hideMapSpinner() { mapSpinner.style.display = 'none'; }

  function getLocation() {
      showLoadingSpinner();
      if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(showPosition, showError);
      } else {
          x.innerHTML = "Geolocation is not supported by this browser.";
      }
  }

  function clearCoordinate() {
      document.getElementById("latitude").value = "";
      document.getElementById("longitude").value = "";
      if (map) { map.remove(); map = null; }
  }

  function showPosition(position) {
      document.getElementById("latitude").value = position.coords.latitude;
      document.getElementById("longitude").value = position.coords.longitude;
      hideLoadingSpinner();
      $('#mapModal').modal('show');
      initializeMap();
  }
  $('#mapModal').on('shown.bs.modal', function() { refreshMap(); });

  function initializeMap() {
      if (!map) {
          var latitude = document.getElementById("latitude").value;
          var longitude = document.getElementById("longitude").value;
          map = L.map("map-modal").setView([latitude, longitude], 15);
          L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19 }).addTo(map);
          var marker = L.marker([latitude, longitude]).addTo(map);
          marker.bindTooltip(`Latitude: ${latitude}, Longitude: ${longitude}`).openTooltip();
      }
      hideMapSpinner();
  }

  function refreshMap() {
      showMapSpinner();
      if (map) { map.remove(); map = null; }
      initializeMap();
      hideMapSpinner();
  }

  function showError(error) {
      switch (error.code) {
          case error.PERMISSION_DENIED: x.innerHTML = "User denied the request for Geolocation."; break;
          case error.POSITION_UNAVAILABLE: x.innerHTML = "Location information is unavailable."; break;
          case error.TIMEOUT: x.innerHTML = "The request to get user location timed out."; break;
          case error.UNKNOWN_ERROR: x.innerHTML = "An unknown error occurred."; break;
      }
      hideLoadingSpinner();
  }

  function clearAndClose() { clearCoordinate(); $('#mapModal').modal('hide'); }
  function closeModal() { $('#mapModal').modal('hide'); }

  $(function() {$('.select2').select2(); });

  $(function() {$('input[type=file]').change(function() {
          var val = $(this).val().toLowerCase(),
              regex = new RegExp("(.*?)\.(pdf|png|PNG|jpg|jpeg|JPG|JPEG)$");
          if (!(regex.test(val))) {
              $(this).val('');$("#modal-warning").modal("show");
          }
      });
  });

  <?php if (isset($_GET['warning'])) { ?>
  $(document).ready(function() {$("#modal-warning1").modal("show"); });
  <?php } ?>
  <?php if (isset($_GET['warning2'])) { ?>
  $(document).ready(function() {$("#modal-warning2").modal("show"); });
  <?php } ?>

  $(document).ready(function() {
      const evidenceInput = $('input[name="evidence[]"]');
      const thumbnailOptionsCard = $('#thumbnail-options-card');
      const thumbnailOptionsDiv = $('#thumbnail-options');
      const thumbnailSelectModal = $('#thumbnailSelectModal');
      const thumbnailForm = $('form');
      let selectedThumbnail = -1;

      evidenceInput.on('change', function() {
          thumbnailOptionsDiv.html('');
          for (let i = 0; i < this.files.length; i++) {
              const thumbnailCard = $('<div>', {
                  class: 'border border-white/10 rounded-xl p-2 thumbnail-card cursor-pointer relative w-28',
                  click: function() {
                      selectedThumbnail = i;
                      $('.thumbnail-card').removeClass('selected');$(this).addClass('selected');
                  }
              });
              const image = $('<img>', { src: URL.createObjectURL(this.files[i]), class: 'w-full h-20 object-cover rounded-lg' });
              const fileName = $('<p>', { text: this.files[i].name, class: 'text-xs mt-1 truncate' });
              const icon = $('<i>', { class: 'fa-solid fa-check absolute top-1 right-1 text-emerald-400', css: { display: i === selectedThumbnail ? 'block' : 'none' } });
              thumbnailCard.append(image).append(icon).append(fileName);
              thumbnailOptionsDiv.append(thumbnailCard);
          }
          if (this.files.length > 0) { thumbnailOptionsCard.removeClass('hidden'); }
          else { thumbnailOptionsCard.addClass('hidden'); }
      });

      thumbnailForm.submit(function(event) {
          if (selectedThumbnail === -1 && evidenceInput.get(0).files.length > 0) {
              event.preventDefault();
              thumbnailSelectModal.modal('show');
          } else {
              $('<input>').attr({ type: 'hidden', name: 'selectedThumbnail', value: selectedThumbnail }).appendTo(thumbnailForm);
          }
      });
  });
  $(function() {
      $('#toggleCameraBtn').on('click', function() {
          $('#cameraCapturePanel').toggleClass('hidden');
          const nowHidden = $('#cameraCapturePanel').hasClass('hidden');
          $(this).html(nowHidden
              ? '<i class="fa-solid fa-camera"></i> Use Camera Instead'
              : '<i class="fa-solid fa-keyboard"></i> Use File Upload Instead');
      });
  });
</script>

<?php include '../includes/footer.php'; ?>