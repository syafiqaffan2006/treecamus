<?php
$current_page = 'alltree';
include_once '../connection/connection.php';
include_once '../function/function.php';
include_once '../settings/settings.php';
include '../includes/header.php';
include '../includes/menu.php';
?>

<header class="flex justify-between items-center mb-8 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">All Trees</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Browse and manage the full tree inventory</p>
    </div>
    <div class="flex items-center gap-2 bg-white/70 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 px-4 py-2 rounded-full text-xs text-slate-500 dark:text-slate-400">
        <a href="../dashboard" class="hover:text-emerald-500">Home</a><span>/</span><span class="text-slate-800 dark:text-white font-medium">List Of Tree</span>
    </div>
</header>

<section class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl p-6">
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
    <h3 class="text-base font-bold">
        <?php echo ($_SESSION['usertype'] == 3) ? 'List of Trees' : 'Trees You Care For'; ?>
    </h3>

    <!-- Custom Search Bar -->
    <div class="flex items-center space-x-3">
        <div class="relative w-full md:w-64">
            <i data-lucide="search"
               class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
            </i>
            <input type="text" id="dashboardSearch" placeholder="Search coordinate, zone..." class="w-full bg-white dark:bg-slate-900/80 border border-slate-300 dark:border-white/10 focus:border-emerald-500/60 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-all placeholder:text-slate-400">
        </div>
    </div>
</div>

    <div class="overflow-x-auto">
        <table id="example1" class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-white/10 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold">
                    <th class="p-4">No</th>
                    <th class="p-4">Family</th>
                    <th class="p-4">Species</th>
                    <th class="p-4">Coordinate</th>
                    <th class="p-4">Zone</th>
                    <th class="p-4">Image(s)</th>
                    <th class="p-4">Created at</th>
                    <th class="p-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/[0.03]">
                <?php
                if ($_SESSION['usertype'] != 4) {
                    listAlltrees($dbh);
                } elseif ($_SESSION['usertype'] == 4) {
                    // Rows are built here (next to the 8 <th> above) so the column count always matches
                    $aq = $dbh->prepare("SELECT tr.latitude, tr.longitude, tr.created_at, sp.speciestitle, z.zonetitle, fm.familytitle, tr.treetoken,
                                                CASE WHEN im.imageFileName IS NULL THEN 'No Image' ELSE 'Has Image' END AS imageIsFound
                                         FROM trees tr
                                         JOIN species sp ON tr.speciesID = sp.speciesID
                                         JOIN family fm ON fm.familyID = tr.familyID
                                         LEFT JOIN images im ON im.treeID = tr.treeID AND im.imagePrimary = 1
                                         LEFT JOIN `zone` z ON z.zoneID = tr.zoneID
                                         WHERE tr.user_id_arborist = :arboristID");
                    $aq->execute([':arboristID' => $_SESSION['user_id']]);
                    $an = 1;
                    while ($d = $aq->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $an++; ?></td>
                    <td><?php echo htmlspecialchars((string)$d['familytitle']); ?></td>
                    <td><?php echo htmlspecialchars((string)$d['speciestitle']); ?></td>
                    <td><?php echo htmlspecialchars($d['latitude'] . ',' . $d['longitude']); ?></td>
                    <td><?php echo htmlspecialchars((string)$d['zonetitle']); ?></td>
                    <td><?php echo htmlspecialchars($d['imageIsFound']); ?></td>
                    <td><?php echo htmlspecialchars($d['created_at']); ?></td>
                    <td><button type="button" name="view" class="btn btn-info" title="View" onclick="window.location='/treecamus/tree/view/<?php echo htmlspecialchars($d['treetoken']); ?>';"><i class="fa fa-eye"></i></button></td>
                </tr>
                <?php }
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal fade" id="modal-success1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-emerald-400">Success</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">New Tree has been successfully added.</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>
<div class="modal fade" id="modal-success">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-emerald-400">Success</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">Tree details has been successfully updated.</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>
<div class="modal fade" id="modal-warning1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-amber-400">Warning</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">Need to insert the new sensor ID to inactive this sensor ID!</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>
<div class="modal fade" id="modal-warning">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-amber-400">Warning</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">DO NOT MANIPULATE THE TOKEN!</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>

<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="../plugins/jszip/jszip.min.js"></script>
<script src="../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../plugins/pdfmake/vfs_fonts.js"></script>

<script>
  $(function () {const table = $("#example1").DataTable({responsive: true,lengthChange: false,autoWidth: false,searching: true,dom: 'Brtip',buttons: ["copy","csv","excel","pdf","print","colvis"]});

    // Custom search bar
    $('#dashboardSearch').on('input', function () {table.search(this.value).draw();});

    // Move DataTable buttons
    table.buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');});
</script>

<?php if (isset($_GET['editsuccess'])) { ?>
<script>$(document).ready(function(){ $("#modal-success").modal("show"); });</script>
<?php } ?>
<?php if (isset($_GET['success'])) { ?>
<script>$(document).ready(function(){ $("#modal-success1").modal("show"); });</script>
<?php } ?>
<?php if (isset($_GET['warning1'])) { ?>
<script>$(document).ready(function(){ $("#modal-warning1").modal("show"); });</script>
<?php } ?>
<?php if (isset($_GET['warning'])) { ?>
<script>$(document).ready(function(){ $("#modal-warning").modal("show"); });</script>
<?php } ?>

<?php include '../includes/footer.php'; ?>