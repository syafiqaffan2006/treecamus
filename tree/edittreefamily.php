<?php 
$current_page = 'treefamily'; // Keeps the menu highlighted correctly

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../connection/connection.php';
include_once '../function/function.php';
include_once '../settings/settings.php';

// Access Check
if(isset($_SESSION['userislogged']) && $_SESSION['userislogged'] != 1){
  header("Location: /treecamus/index.php");
  exit;
}

// Ensure tree family token exists
if(isset($_GET['id'])) {
    if(checktreeFamilyRecord($dbh, trim($_GET['id'])) == true) {
        $token = trim($_GET['id']); //family token
    } else {
      header("Location: /treecamus/logout");
      exit;
    }
}

// Handle form submission
if(isset($_POST['save'])){
  $familytitle = addslashes($_POST['famName']);
  $is_display = $_POST['isallowed'];

  updatetreeFamily($dbh, $familytitle, $is_display, $token);

  header("Location: /treecamus/tree/family?editsuccess"); 
  exit;
}

include '../includes/header.php';
include '../includes/menu.php';
?> 

<header class="flex justify-between items-center mb-8 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Edit Tree Family</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Modify tree family reference data</p>
    </div>
    <div class="flex items-center gap-2 bg-white/70 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 px-4 py-2 rounded-full text-xs text-slate-500 dark:text-slate-400">
        <a href="/treecamus/dashboard" class="hover:text-emerald-500 transition-colors">Home</a>
        <span>/</span>
        <a href="/treecamus/tree/family" class="hover:text-emerald-500 transition-colors">List Of Tree Family</a>
        <span>/</span>
        <span class="text-slate-800 dark:text-white font-medium">Edit</span>
    </div>
</header>

<section class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl p-6 max-w-3xl">
    <div class="mb-6 pb-4 border-b border-slate-200 dark:border-white/10">
        <h3 class="text-base font-bold flex items-center gap-2">
            <i data-lucide="edit-3" class="w-5 h-5 text-emerald-500"></i>
            Family Details
        </h3>
    </div>

    <form role="form" name="editTreeFam" method="post" action="/treecamus/tree/family/edit/<?php echo htmlspecialchars($token ?? ''); ?>" autocomplete="off" class="space-y-6">
        
        <!-- Family Name Input -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Family Name</label>
            <input 
                type="text" 
                name="famName" 
                class="w-full bg-white dark:bg-slate-900/80 border border-slate-300 dark:border-white/10 focus:border-emerald-500/60 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-all placeholder:text-slate-400" 
                value="<?php echo isset($token) ? htmlspecialchars(stripslashes(getFamtreeTitle($dbh, $token))) : ''; ?>" 
                required>
        </div>
        
        <!-- Display Status Radios -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Display Status</label>
            
            <div class="flex flex-col sm:flex-row gap-4">
                <label class="flex items-center gap-3 cursor-pointer group p-3 border border-slate-200 dark:border-white/10 rounded-xl bg-white/50 dark:bg-white/[0.02] hover:border-emerald-500/50 transition-all">
                    <input 
                        type="radio" 
                        name="isallowed" 
                        value="1" 
                        class="w-4 h-4 text-emerald-500 bg-slate-100 border-slate-300 focus:ring-emerald-500 focus:ring-2 dark:bg-slate-800 dark:border-slate-600 cursor-pointer" 
                        <?php if(isset($token) && getisDisplayFamily($dbh, $token) == 1) { echo "checked"; }?>>
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">
                        Yes, display on the system
                    </span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer group p-3 border border-slate-200 dark:border-white/10 rounded-xl bg-white/50 dark:bg-white/[0.02] hover:border-emerald-500/50 transition-all">
                    <input 
                        type="radio" 
                        name="isallowed" 
                        value="0" 
                        class="w-4 h-4 text-emerald-500 bg-slate-100 border-slate-300 focus:ring-emerald-500 focus:ring-2 dark:bg-slate-800 dark:border-slate-600 cursor-pointer" 
                        <?php if(isset($token) && getisDisplayFamily($dbh, $token) == 0) { echo "checked"; }?>>
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">
                        No, hide from display
                    </span>
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 flex items-center gap-3 border-t border-slate-200 dark:border-white/10 mt-6">
            <button type="submit" name="save" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-white font-semibold text-sm px-6 py-2.5 rounded-xl flex items-center gap-2 shadow-[0_0_15px_rgba(16,185,129,0.3)] transition-all">
                <i data-lucide="save" class="w-4 h-4"></i> Save Changes
            </button>
            <button type="button" onclick="window.location='/treecamus/tree/family';" class="bg-slate-200 hover:bg-slate-300 dark:bg-white/[0.05] dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-300 font-medium text-sm px-6 py-2.5 rounded-xl transition-all">
                Cancel
            </button>
        </div>
    </form>
</section>

<?php include '../includes/footer.php'; ?>