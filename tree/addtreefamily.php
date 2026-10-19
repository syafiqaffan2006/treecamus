<?php
ob_start(); // Turn on output buffering to prevent header errors

$current_page = 'treefamily';
include_once '../connection/connection.php';
include_once '../function/function.php';
include_once '../settings/settings.php';

// Handle form submission BEFORE rendering HTML or includes
if (isset($_POST['save'])) {
    $familytitle = addslashes($_POST['famName']);
    $is_display = $_POST['isallowed'];
    $familytoken = getToken(32);
    saveTreeFam($dbh, $familytitle, $is_display, $familytoken);
    header("Location: treefamily.php?success");
    exit();
}

// Load headers only when NOT redirecting
include '../includes/header.php';
include '../includes/menu.php';
?>

<header class="flex justify-between items-center mb-8 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Add Tree Family</h1>
    </div>
    <div class="flex items-center gap-2 bg-white/70 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 px-4 py-2 rounded-full text-xs text-slate-500 dark:text-slate-400">
        <a href="../dashboard" class="hover:text-emerald-500">Home</a><span>/</span>
        <a href="treefamily.php" class="hover:text-emerald-500">List Of Tree Family</a><span>/</span>
        <span class="text-slate-800 dark:text-white font-medium">Add Tree Family</span>
    </div>
</header>

<div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden max-w-2xl">
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-4">
        <h2 class="text-white font-bold text-lg">Add Tree Family</h2>
    </div>

    <form role="form" name="addtreefamily" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off" class="p-6 space-y-5">
        <div>
            <label for="famName" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Family Name</label>
            <input type="text" name="famName" id="famName" required
                class="w-full rounded-xl bg-white dark:bg-black/30 border border-slate-300 dark:border-white/10 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Is Displayed?</label>
            <div class="flex flex-col gap-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="isallowed" value="1" class="accent-emerald-500">
                    Yes, display on the system
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="isallowed" value="0" checked class="accent-red-500">
                    No, hide from display on the system
                </label>
            </div>
        </div>

        <div class="flex gap-2 pt-2">
            <button type="submit" name="save" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-[0_0_15px_rgba(16,185,129,0.3)] transition-all">Save</button>
            <button type="button" onclick='window.location="treefamily.php";' class="border border-slate-300 dark:border-white/10 text-slate-600 dark:text-slate-300 text-sm px-5 py-2.5 rounded-xl hover:bg-white/5">Cancel</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>