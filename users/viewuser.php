<?php
$current_page = 'users';
include_once '../connection/connection.php';
include_once '../function/function.php';
include_once '../settings/settings.php';

if (!isset($_GET['token'])) {
    header("Location: /treecamus/users/list");
    exit();
}

if (isset($_GET['token'])) {
    if (checkUserRecord($dbh, $_GET['token']) == true) {
        $token = $_GET['token'];
        $profile_image = getprofileimage($dbh, $token);
        $name = getname($dbh, $token);
        $email = getemail($dbh, $token);
        $roletitle = getroletitle($dbh, $token);
        $is_allow = getisallow($dbh, $token);
    } else {
        header("Location: /treecamus/logout");
        exit();
    }
}

include '../includes/header.php';
include '../includes/menu.php';
?>

<link rel="stylesheet" href="/treecamus/plugins/ekko-lightbox/ekko-lightbox.css">

<div class="flex items-center justify-between mb-6">
    <h1 class="text-lg font-medium">View Users</h1>
    <nav class="text-xs text-neutral-500 dark:text-neutral-400">
        <a href="/treecamus/dashboard" class="hover:underline">Home</a> /
        <a href="/treecamus/users/list" class="hover:underline">List of Users</a> /
        <span>View User</span>
    </nav>
</div>

<div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden max-w-2xl">

    <div class="bg-forest-600 px-6 py-4">
        <h2 class="text-white font-medium text-lg">View User</h2>
    </div>

    <div class="p-6 space-y-4">
        <?php $has_profile_photo = !empty($profile_image) && filter_var($profile_image, FILTER_VALIDATE_URL); ?>
          <div class="text-center">
            <?php if ($has_profile_photo) { ?>
                <a href="<?php echo $profile_image; ?>" data-toggle="lightbox" id="profile-photo-link">
                    <img src="<?php echo $profile_image; ?>"
                        class="w-32 h-32 object-cover rounded-lg mx-auto" alt="Profile photo" id="profile-photo-img"
                        onerror="this.closest('div.text-center').innerHTML = '<div class=&quot;w-32 h-32 rounded-lg bg-forest-100 dark:bg-neutral-700 flex items-center justify-center mx-auto&quot;><i class=&quot;fa fa-user text-forest-800 dark:text-forest-300 text-4xl&quot;></i></div>';">
                </a>
            <?php } else { ?>
                <div class="w-32 h-32 rounded-lg bg-forest-100 dark:bg-forest-900 flex items-center justify-center mx-auto">
                    <i class="fa fa-user text-forest-800 dark:text-forest-300 text-4xl"></i>
                </div>
            <?php } ?>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" value="<?php if (isset($name)) { echo $name; } ?>" disabled
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">E-mail</label>
            <input type="text" value="<?php if (isset($email)) { echo $email; } ?>" disabled
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <input type="text" value="<?php if (isset($roletitle)) { echo $roletitle; } ?>" disabled
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Is Allowed</label>
            <input type="text" value="<?php if (isset($is_allow)) { echo $is_allow; } ?>" disabled
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-500">
        </div>

        <div class="pt-2">
            <button type="button" onclick='window.location="/treecamus/users/list";'
                class="border border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300 text-sm px-5 py-2 rounded-lg">Back</button>
        </div>
    </div>
</div>

<script src="/treecamus/plugins/ekko-lightbox/ekko-lightbox.min.js"></script>
<script>
  $(function () {
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox({ alwaysShowClose: true });
    });
  });
</script>

<?php include '../includes/footer.php'; ?>