<?php
$current_page = 'users';
include_once '../connection/connection.php';
include_once '../function/function.php';
include_once '../settings/settings.php';
include '../includes/header.php';
include '../includes/menu.php';
?>

<header class="flex justify-between items-center mb-8 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Users</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage user access and roles</p>
    </div>
    <div class="flex items-center gap-2 bg-white/70 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 px-4 py-2 rounded-full text-xs text-slate-500 dark:text-slate-400">
        <a href="../dashboard" class="hover:text-emerald-500">Home</a><span>/</span><span class="text-slate-800 dark:text-white font-medium">List of Users</span>
    </div>
</header>

<section class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl p-6">
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <h3 class="text-base font-bold">List of Users</h3>
    </div>

    <div class="overflow-x-auto">
        <table id="example1" class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-white/10 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold">
                    <th class="p-4">#</th>
                    <th class="p-4">User Name</th>
                    <th class="p-4">E-mail</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Is Allow</th>
                    <th class="p-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/[0.03]">
                <?php listUsers($dbh); ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Status modals -->
<div class="modal fade" id="modal-success">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-emerald-400">Success</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">User Has Been Added Successfully.</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>
<div class="modal fade" id="modal-warning">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-amber-400">Warning</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">Failed to Insert User.</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>
<div class="modal fade" id="modal-updated">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-emerald-400">Success</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">Data has been edited successfully.</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>
<div class="modal fade" id="modal-notupdated">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h4 class="modal-title text-amber-400">Warning</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">Data failed to update.</div>
        <div class="modal-footer"><button type="button" class="text-sm px-4 py-2 rounded-lg border border-white/10" data-dismiss="modal">Close</button></div>
    </div></div>
</div>

<!-- listUsers() prints plain Bootstrap .btn-info action buttons; this restyles them
     to match the theme without touching the shared function.php -->
<style>
  #example1 .btn { display: inline-flex; align-items: center; justify-content: center;
      border: none; border-radius: 0.65rem; padding: 0.4rem 0.65rem; margin-right: 0.25rem;
      font-size: 0.8rem; cursor: pointer; color: #fff;
      background: linear-gradient(to right, #059669, #10b981); }
  #example1 .btn:hover { opacity: 0.9; }
</style>

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
  $(function () {
    const table = $("#example1").DataTable({
      responsive: true, lengthChange: false, autoWidth: false, searching: true,
      dom: 'Brtip', buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
    });
    table.buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
  });
</script>

<?php if (isset($_GET['success'])) { ?>
<script>$(document).ready(function(){ $("#modal-success").modal("show"); });</script>
<?php } elseif (isset($_GET['failed'])) { ?>
<script>$(document).ready(function(){ $("#modal-warning").modal("show"); });</script>
<?php } elseif (isset($_GET['updated'])) { ?>
<script>$(document).ready(function(){ $("#modal-updated").modal("show"); });</script>
<?php } elseif (isset($_GET['notupdated'])) { ?>
<script>$(document).ready(function(){ $("#modal-notupdated").modal("show"); });</script>
<?php } ?>

<?php include '../includes/footer.php'; ?>
