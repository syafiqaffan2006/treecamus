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
$campusID = getcampusIDSelected($dbh, $token);
$zoneID = getzoneIDSelected($dbh, $token);
$familyID = getfamilyIDSelected($dbh, $token);
$speciesID = getspeciesIDSelected($dbh, $token);
$treestatusID = getTreestatusIDSelected($dbh, $token);
$arboristID = getArboristIDSelected($dbh, $token);
$treeID = getTreeID($dbh, $token);
$tagStatusID = getTagStatusIDSelected($dbh, $treeID);
$getCensorIDRecord = getTagIDRecord($dbh, $treeID);
$images = getImagesForTree($dbh, $treeID);

// 1. Process GET deletion request
if (isset($_GET['fid'])) {
    $imageID = $_GET['fid'];
    $thumbnailDeleted = isThumbnailDeleted($dbh, $treeID, $imageID);
    deleteSpecificFiles($dbh, $imageID);
    if ($thumbnailDeleted) {
        $newThumbnailIndex = findNewThumbnailIndex($images, $imageID);
        if ($newThumbnailIndex !== false) {
            $newThumbnailFileName = $images[$newThumbnailIndex]['imageFileName'];
            updateImagePrimary($dbh, $treeID, $newThumbnailFileName);
        }
    }
    header("Location: /treecamus/tree/edit/" . rawurlencode($token));
    exit();
}

// 2. Process POST save request
if (isset($_POST['save'])) {
    $treeName = $_POST['treeName'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $campus = $_POST['campus'];
    $zone = $_POST['zone'];
    $family = $_POST['family'];
    $species = $_POST['species'];
    $treestatus = $_POST['status'];
    $arborist = $_POST['arborist'];
    $imagetoken = getToken(32);
    $tagID = $_POST['tagID'];
    $censorID = empty($_POST['censorID']) ? NULL : $_POST['censorID'];
    $tagstoken1 = getToken(38);
    $censorIDstatus = $_POST['censorIDstatus'];

    // Move index initialization before evidence upload
    $selectedThumbnailIndex = (isset($_POST['selectedThumbnail']) && $_POST['selectedThumbnail'] !== '') ? intval($_POST['selectedThumbnail']) : -1;
    $newfilename = [];

    if (!empty($_FILES['evidence']['name'][0])) {
        $i = 0;
        while ($i < count($_FILES['evidence']['name'])) {
            $temp = explode(".", $_FILES['evidence']['name'][$i]);
            $newfilename[$i] = microtime(true) . '.' . end($temp);
            move_uploaded_file($_FILES['evidence']['tmp_name'][$i], "../evidences/" . $newfilename[$i]);
            saveevidence($dbh, $newfilename[$i], $_FILES['evidence']['type'][$i], $treeID, $imagetoken, $i === $selectedThumbnailIndex ? 1 : 0);
            $i++;
        }
    }

    if ($censorID != NULL && $getCensorIDRecord != $censorID) {
        if ($censorIDstatus == 1) {
            if (CensoridRecord($dbh, $censorID) == true) {
                updateTreeDetails($dbh, $treeName, $latitude, $longitude, $campus, $zone, $family, $species, $treestatus, $arborist, $tagID, $token);
                header("Location: /treecamus/tree?editsuccess");
                exit();
            } else {
                updatetag2($dbh, $treeID);
                savetag2($dbh, $censorID, $treeID, $censorIDstatus, $tagstoken1);
                updateTreeDetails($dbh, $treeName, $latitude, $longitude, $campus, $zone, $family, $species, $treestatus, $arborist, $tagID, $token);
                header("Location: /treecamus/tree?editsuccess");
                exit();
            }
        } else {
            header("Location: /treecamus/tree?warning1");
            exit();
        }
    }

    updateTreeDetails($dbh, $treeName, $latitude, $longitude, $campus, $zone, $family, $species, $treestatus, $arborist, $tagID, $token);

    if ($selectedThumbnailIndex >= 0) {
        if ($selectedThumbnailIndex < count($images)) {
            updateImagePrimary($dbh, $treeID, $images[$selectedThumbnailIndex]['imageFileName']);
        } else {
            $newImageIndex = $selectedThumbnailIndex - count($images);
            if (isset($newfilename[$newImageIndex])) {
                $newImageFileName = $newfilename[$newImageIndex];
                updateImagePrimary($dbh, $treeID, $newImageFileName);
            }
        }
    }
    header("Location: /treecamus/tree?editsuccess");
    exit();
}

// 3. Output HTML header and menu after all headers/redirects are evaluated
include '../includes/header.php';
include '../includes/menu.php';
?>

<script src="/treecamus/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/treecamus/plugins/select2/js/select2.full.min.js"></script>

<style>
  .thumbnail-card.selected { outline: 2px solid #10b981; }
  .required { color: #f87171; }
  .select2-container .select2-selection--single { height: 42px !important; border-radius: 0.75rem !important; display: flex; align-items: center; }
</style>

<header class="flex justify-between items-center mb-8 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Edit Tree</h1>
    </div>
    <div class="flex items-center gap-2 bg-white/70 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 px-4 py-2 rounded-full text-xs text-slate-500 dark:text-slate-400">
        <a href="/treecamus/dashboard" class="hover:text-emerald-500">Home</a><span>/</span>
        <a href="/treecamus/tree" class="hover:text-emerald-500">List Of Tree</a><span>/</span>
        <span class="text-slate-800 dark:text-white font-medium">Edit Tree</span>
    </div>
</header>

<div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden">
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-4">
        <h2 class="text-white font-bold text-lg">Edit Tree</h2>
    </div>

    <form role="form" name="editTree" method="post" action="/treecamus/tree/edit/<?php echo htmlspecialchars($token); ?>" enctype="multipart/form-data" autocomplete="off" class="p-6 space-y-4">

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tree Unique Code<span class="required">*</span></label>
            <input type="text" name="treeName" required value="<?php echo getTreeName($dbh, $token); ?>"
                class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Latitude<span class="required">*</span></label>
                <input type="text" name="latitude" id="latitude" required value="<?php echo getlatitude($dbh, $token); ?>"
                    class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Longitude<span class="required">*</span></label>
                <input type="text" name="longitude" id="longitude" required value="<?php echo getlongitude($dbh, $token); ?>"
                    class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="button" onclick="getLocation()" class="flex-1 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-white text-sm font-semibold rounded-xl py-2.5 shadow-[0_0_15px_rgba(16,185,129,0.3)] transition-all">Get Coordinate</button>
                <button type="button" onclick="clearCoordinate()" class="flex-1 bg-white/5 border border-white/10 hover:bg-white/10 text-amber-400 text-sm font-semibold rounded-xl py-2.5 transition-all">Clear</button>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Campus<span class="required">*</span></label>
            <select class="select2 w-full" name="campus" data-placeholder="Select a campus" style="width:100%" required>
                <option></option>
                <?php showlistCampusAndSelectedCampus($dbh, $campusID); ?>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Zone</label>
                <select name="zone" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white">
                    <?php showlistZoneAndSelectedZone($dbh, $zoneID); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Family</label>
                <select name="family" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white">
                    <?php showlistfamilyAndSelectedfamily($dbh, $familyID); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Species</label>
                <select name="species" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white">
                    <?php showlistspeciesAndSelectedspecies($dbh, $speciesID); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tree Status<span class="required">*</span></label>
                <select name="status" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white">
                    <?php showlistTreeStatusAndSelectedTreeStatus($dbh, $treestatusID); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Arborist<span class="required">*</span></label>
                <select name="arborist" required class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white">
                    <?php showlistArboristAndSelectedArborist($dbh, $arboristID); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tag ID</label>
                <input type="text" name="tagID" readonly value="<?php echo gettagID($dbh, $token); ?>"
                    class="w-full rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-500">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Sensor ID</label>
                <input type="text" name="censorID" value="<?php echo getcensorID($dbh, $treeID); ?>"
                    class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Sensor ID Status</label>
                <select name="censorIDstatus" class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white">
                    <?php showlistTagstatusAndSelectedTagStatus($dbh, $tagStatusID); ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">File input <em class="normal-case text-slate-400">(PNG, JPEG)</em></label>
            <input type="file" name="evidence[]" id="evidence" multiple class="w-full text-sm rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-slate-800 dark:text-white file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-500 file:text-white file:px-3 file:py-1.5 file:text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Image record</label>
            <div class="overflow-x-auto border border-slate-200 dark:border-white/10 rounded-xl">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5">
                            <th class="py-2 px-3">#</th><th class="px-3">File Name</th><th class="px-3">Type</th><th class="px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody><?php echo getFileForEdit($dbh, $treeID); ?></tbody>
                </table>
            </div>
        </div>

        <div id="thumbnail-options-card" class="border border-amber-500/30 bg-amber-500/5 rounded-xl overflow-hidden">
            <div class="bg-amber-500/20 px-4 py-2"><h3 class="text-amber-400 font-semibold text-sm">Thumbnail Selection</h3></div>
            <?php if (!empty($images)) { ?>
            <p class="text-xs text-slate-500 dark:text-slate-400 px-4 pt-2"><strong>Attention:</strong> Please select an image with the <b>TREE TAG</b> to serve as the thumbnail.</p>
            <?php } ?>
            <div class="flex flex-wrap gap-3 p-4" id="thumbnail-options">
                <?php if (empty($images)) { ?>
                    <p class="text-sm text-slate-400">No images uploaded.</p>
                <?php } else { ?>
                    <?php foreach ($images as $index => $image) { ?>
                        <div class="border border-white/10 rounded-xl p-2 thumbnail-card cursor-pointer relative w-28 <?php if ($image['imagePrimary'] == 1) echo 'selected'; ?>">
                            <label class="cursor-pointer">
                                <input type="radio" name="thumbnail" value="<?= $index ?>" class="hidden" <?php if ($image['imagePrimary'] == 1) echo 'checked'; ?>>
                                <img src="/treecamus/evidences/<?= $image['imageFileName'] ?>" class="w-full h-20 object-cover rounded-lg">
                                <p class="text-xs mt-1 truncate"><?= $image['imageFileName'] ?></p>
                            </label>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <input type="hidden" name="selectedThumbnail" id="selectedThumbnail" value="">

        <div class="flex gap-2 pt-2">
            <button type="submit" name="save" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-[0_0_15px_rgba(16,185,129,0.3)] transition-all">Save</button>
            <button type="button" onclick='window.location="/treecamus/tree";' class="border border-slate-300 dark:border-white/10 text-slate-600 dark:text-slate-300 text-sm px-5 py-2.5 rounded-xl hover:bg-white/5">Cancel</button>
        </div>
    </form>
</div>

<div id="delete" class="modal fade">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-red-400">Warning</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">Are you sure you want to delete this specific file?</div>
        <div class="modal-footer">
            <button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Cancel</button>
            <a class="text-sm px-4 py-2 rounded-lg bg-red-500/20 text-red-400 btn-ok">Yes</a>
        </div>
    </div></div>
</div>
<div class="modal fade" id="modal-warning">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-amber-400">Warning</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">Invalid file type!</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>

<script>
  var x = document.getElementById("demo");
  function getLocation() {
      if (navigator.geolocation) navigator.geolocation.getCurrentPosition(showPosition, showError);
      else x.innerHTML = "Geolocation is not supported by this browser.";
  }
  function clearCoordinate() { document.getElementById("latitude").value = ""; document.getElementById("longitude").value = ""; }
  function showPosition(position) {
      document.getElementById("latitude").value = position.coords.latitude;
      document.getElementById("longitude").value = position.coords.longitude;
  }
  function showError(error) {
      switch (error.code) {
          case error.PERMISSION_DENIED: x.innerHTML = "User denied the request for Geolocation."; break;
          case error.POSITION_UNAVAILABLE: x.innerHTML = "Location information is unavailable."; break;
          case error.TIMEOUT: x.innerHTML = "The request to get user location timed out."; break;
          case error.UNKNOWN_ERROR: x.innerHTML = "An unknown error occurred."; break;
      }
  }
  $(function() { $('.select2').select2(); });
  $(function() {
      $('input[type=file]').change(function() {
          var val = $(this).val().toLowerCase(), regex = new RegExp("(.*?)\.(pdf|png|PNG|jpg|jpeg|JPG|JPEG)$");
          if (!(regex.test(val))) { $(this).val(''); $("#modal-warning").modal("show"); }
      });
  });
  $('#delete').on('show.bs.modal', function (e) { $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href')); });
  $(document).ready(function() {
      const thumbnailOptionsDiv = $('#thumbnail-options');
      const selectedThumbnailInput = $('#selectedThumbnail');
      const initiallySelected = $('.thumbnail-card input[type="radio"]:checked');
      let selectedThumbnailIndex = -1;
      if (initiallySelected.length > 0) { selectedThumbnailIndex = parseInt(initiallySelected.val()); selectedThumbnailInput.val(selectedThumbnailIndex); }
      function addNewThumbnailCard(index, imageSrc, imageName) {
          const card = $('<div>', { class: 'border border-white/10 rounded-xl p-2 thumbnail-card cursor-pointer relative w-28' });
          const label = $('<label>', { class: 'cursor-pointer' });
          const radio = $('<input>', { type: 'radio', name: 'thumbnail', value: index, class: 'hidden' });
          const img = $('<img>', { src: imageSrc, class: 'w-full h-20 object-cover rounded-lg' });
          const name = $('<p>', { text: imageName, class: 'text-xs mt-1 truncate' });
          label.append(radio).append(img).append(name);
          card.append(label);
          thumbnailOptionsDiv.append(card);
      }
      $('#evidence').change(function() {
          const files = this.files;
          for (let i = 0; i < files.length; i++) {
              const reader = new FileReader();
              reader.onload = function(event) { addNewThumbnailCard(i + $('.thumbnail-card').length, event.target.result, files[i].name); };
              reader.readAsDataURL(files[i]);
          }
          $('p:contains("No images uploaded.")').hide();
      });
      thumbnailOptionsDiv.on('click', '.thumbnail-card', function() {
          $('.thumbnail-card').removeClass('selected');
          $(this).addClass('selected');
          const radio = $(this).find('input[type=radio]');
          radio.prop('checked', true);
          const index = radio.val();
          selectedThumbnailInput.val(index);
          selectedThumbnailIndex = index;
      });
  });
</script>

<?php include '../includes/footer.php'; ?>