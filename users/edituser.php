<?php
$current_page = 'users';
include_once '../connection/connection.php';
include_once '../function/function.php';
include_once '../settings/settings.php';

/*
 * NOTE: the variables for the user being edited use the "eu_" prefix on purpose.
 * Generic names like $userID / $role / $name can be overwritten by includes/header.php
 * or includes/menu.php (which use them for the LOGGED-IN user), and that made the
 * Save button update the wrong person's role.
 */

if (!isset($_GET['token']) && !isset($_POST['save'])) {
    header("Location: /treecamus/users/list");
    exit();
}

/* ---------- SAVE ---------- */
if (isset($_POST['save'])) {
    $eu_token = trim($_POST['userstoken'] ?? '');
    $eu_id    = getUserID($dbh, $eu_token);          // always derived from the token, never from a hidden field

    if (empty($eu_id)) {
        header("Location: /treecamus/users/list");
        exit();
    }

    $role     = (int)($_POST['role'] ?? 0);
    $is_allow = (int)($_POST['isallowed'] ?? 0);
    $uatoken  = $_POST['uatoken'] ?? getToken(32);

    if ($role > 0) {
        if (checkUserAccessRecord($dbh, $eu_token) == false) {
            AddUserAccess($dbh, $eu_id, $role, $_SESSION['user_id'], $is_allow, $uatoken);
        } else {
            UpdateUserAccess($dbh, $eu_id, $role, $_SESSION['user_id'], $is_allow);
        }
        UpdateUsersIsAllowed($dbh, $is_allow, $eu_token);   // redirects to the list when done
    }
}

/* ---------- LOAD ---------- */
if (isset($_GET['token'])) {
    if (checkUserRecord($dbh, $_GET['token']) == true) {
        $eu_token = $_GET['token'];
        $eu_id    = getUserID($dbh, $eu_token);
        $eu_name  = getname($dbh, $eu_token);
        $eu_email = getemail($dbh, $eu_token);
        $eu_role  = getroleid($dbh, $eu_token);
    } else {
        header("Location: /treecamus/logout");
        exit();
    }
} else {
    // came from a POST that did not save (e.g. no role chosen): reload the same user
    $eu_id    = getUserID($dbh, $eu_token);
    $eu_name  = getname($dbh, $eu_token);
    $eu_email = getemail($dbh, $eu_token);
    $eu_role  = getroleid($dbh, $eu_token);
}

$eu_hasAccess = checkUserAccessRecord($dbh, $eu_token);

// current "Is Allowed" value of THIS user (getAccessIsAllowed() has a JOIN without ON, so it can return another user's value)
$eu_allow = 0;
$q = $dbh->prepare("SELECT ua.is_allow FROM treecamusdb.useraccess ua
                    JOIN treecamusdb.users u ON u.userID = ua.userID
                    WHERE u.userstoken = :t LIMIT 1");
$q->execute([':t' => $eu_token]);
$row = $q->fetch(PDO::FETCH_ASSOC);
if ($row) { $eu_allow = (int)$row['is_allow']; }

include '../includes/header.php';
include '../includes/menu.php';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-lg font-medium">Edit User</h1>
    <nav class="text-xs text-neutral-500 dark:text-neutral-400">
        <a href="/treecamus/dashboard" class="hover:underline">Home</a> /
        <a href="/treecamus/users/list" class="hover:underline">List of Users</a> /
        <span>Edit User</span>
    </nav>
</div>

<div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden max-w-2xl">

    <div class="bg-emerald-600 px-6 py-4">
        <h2 class="text-white font-medium text-lg">Edit User</h2>
    </div>

    <form action="/treecamus/users/edit" name="form" method="POST" class="p-6 space-y-4">

        <input type="hidden" name="uatoken" value="<?php echo htmlspecialchars(getToken(32)); ?>">
        <input type="hidden" name="userstoken" value="<?php echo htmlspecialchars($eu_token); ?>">

        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" value="<?php echo htmlspecialchars($eu_name ?? ''); ?>" disabled
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">E-mail</label>
            <input type="text" value="<?php echo htmlspecialchars($eu_email ?? ''); ?>" disabled
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <select name="role" id="role" required class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 px-3 py-2 text-sm">
                <option value="">SELECT ONE</option>
                <?php
                if ($eu_hasAccess == false) {
                    getRoleList($dbh);
                } else {
                    getSelectedRoleList($dbh, $eu_role);
                }
                ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Is Allowed</label>
            <div class="flex flex-col gap-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" id="customRadio1" name="isallowed" value="1" class="accent-emerald-600"
                        <?php if ($eu_allow == 1) { echo "checked"; } ?>>
                    Yes, allow this user to access the system
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" id="customRadio2" name="isallowed" value="0" class="accent-red-500"
                        <?php if ($eu_allow == 0) { echo "checked"; } ?>>
                    No, do not allow this user to access the system
                </label>
            </div>
        </div>

        <div class="flex gap-2 pt-2">
            <button type="submit" name="save" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">Save</button>
            <button type="button" onclick='window.location="/treecamus/users/list";' class="border border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300 text-sm px-5 py-2 rounded-lg">Back</button>
        </div>
    </form>
</div>

<?php if ($eu_hasAccess == true) { ?>
<script>
  $(document).ready(function(){
      $("#role").children("option:eq(0)").hide();
  });
</script>
<?php } ?>

<?php include '../includes/footer.php'; ?>