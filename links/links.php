<?php
$current_page = $current_page ?? '';

function nav_item($url, $icon, $label, $active, $target = '_self') {
    $classes = $active
        ? 'text-white bg-emerald-500/10 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
        : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent';
    $iconClass = $active ? 'text-emerald-600 dark:text-emerald-400' : '';
    echo '<a href="' . htmlspecialchars($url) . '" target="' . htmlspecialchars($target) . '"
           class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all ' . $classes . '">
            <i class="' . htmlspecialchars($icon) . ' ' . $iconClass . ' w-5"></i>
            <span>' . htmlspecialchars($label) . '</span>
        </a>';
}

function nav_dropdown_item($url, $icon, $label, $active) {
    $classes = $active
        ? 'text-white bg-emerald-500/10 border border-emerald-500/30'
        : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] border border-transparent';
    $iconClass = $active ? 'text-emerald-600 dark:text-emerald-400' : '';
    echo '<a href="' . htmlspecialchars($url) . '"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition-all ' . $classes . '">
            <i class="' . htmlspecialchars($icon) . ' ' . $iconClass . ' w-4"></i>
            <span>' . htmlspecialchars($label) . '</span>
        </a>';
}
?>

<?php nav_item('../dashboard', 'fa-solid fa-house', 'Home', $current_page === 'dashboard'); ?>

<?php if ((isset($_SESSION['usertype']) && $_SESSION['usertype'] == 1) || (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 2)): ?>
    <?php nav_item('../users/listusers.php', 'fa-solid fa-users-gear', 'Users', $current_page === 'users'); ?>
<?php endif; ?>

<?php if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 3): ?>

    <?php nav_item('../tree/add', 'fa-solid fa-circle-plus', 'New Tree', $current_page === 'addtree'); ?>

    <details class="group" <?= in_array($current_page, ['alltree', 'treefamily', 'treespecies']) ? 'open' : '' ?>>
        <summary class="flex items-center justify-between gap-3 px-3.5 py-3 rounded-xl text-sm font-medium cursor-pointer list-none transition-all text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04]">
            <span class="flex items-center gap-3">
                <i class="fa-solid fa-tree-city w-5"></i>
                <span>Trees</span>
            </span>
            <i class="fa-solid fa-chevron-right text-xs opacity-60 transition-transform duration-200 group-open:rotate-90"></i>
        </summary>
        <div class="ml-4 mt-1 flex flex-col gap-1 border-l border-slate-200 dark:border-white/10 pl-3">
            <?php nav_dropdown_item('../tree', 'fa-solid fa-list', 'All Tree', $current_page === 'alltree'); ?>
            <?php nav_dropdown_item('../tree/family', 'fa-solid fa-sitemap', 'Tree Family', $current_page === 'treefamily'); ?>
            <?php nav_dropdown_item('../tree/species', 'fa-solid fa-leaf', 'Tree Species', $current_page === 'treespecies'); ?>
        </div>
    </details>

<?php endif; ?>

<?php if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 4): ?>
    <?php nav_item('../tree', 'fa-solid fa-tree-city', 'Your Tree', $current_page === 'alltree'); ?>
<?php endif; ?>

<?php nav_item('../maps', 'fa-solid fa-map-location-dot', 'Maps', $current_page === 'maps'); ?>
<?php nav_item('../statistics', 'fa-solid fa-chart-line', 'Statistics', $current_page === 'stats'); ?>
<?php nav_item('../manual/Tree-CaMUS User Manual.pdf', 'fa-solid fa-book-open', 'User Manual', false, '_blank'); ?>
