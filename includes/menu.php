<?php
$current_page = $current_page ?? '';
$user_name = $user['name'] ?? 'User';
$user_role = $user['roletitle'] ?? 'MODERATOR';
$user_initials = strtoupper(substr(trim($user_name), 0, 1) . (strpos($user_name, ' ') !== false ? substr(strrchr($user_name, ' '), 1, 1) : ''));
?>

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
        <div class="relative">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-xs font-bold text-white">
                <?= htmlspecialchars($user_initials ?: 'U') ?>
            </div>
            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-slate-950"></span>
        </div>
        <div class="overflow-hidden">
            <h4 class="text-xs font-semibold truncate max-w-[120px] text-white"><?= htmlspecialchars($user_name) ?></h4>
            <span class="text-[10px] font-bold text-emerald-400 tracking-wider"><?= htmlspecialchars(strtoupper($user_role)) ?></span>
        </div>
    </div>

    <nav class="flex flex-col gap-1.5 mt-2">
        <a href="/treecamus/dashboard"
           class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all
           <?= $current_page === 'dashboard'
               ? 'text-white bg-emerald-500/10 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
               : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' ?>">
            <i class="fa-solid fa-house <?= $current_page === 'dashboard' ? 'text-emerald-400' : '' ?> w-5"></i>
            <span>Home</span>
        </a>

        <?php if ((isset($_SESSION['usertype']) && $_SESSION['usertype'] == 1) || (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 2)): ?>
            <a href="/treecamus/users"
               class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all
               <?= $current_page === 'users'
                   ? 'text-white bg-emerald-500/10 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
                   : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' ?>">
                <i class="fa-solid fa-users <?= $current_page === 'users' ? 'text-emerald-400' : '' ?> w-5"></i>
                <span>Users</span>
            </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 3): ?>

            <a href="/treecamus/tree/add"
               class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all
               <?= $current_page === 'addtree'
                   ? 'text-white bg-emerald-500/10 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
                   : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' ?>">
                <i class="fa-solid fa-circle-plus <?= $current_page === 'addtree' ? 'text-emerald-400' : '' ?> w-5"></i>
                <span>New Tree</span>
            </a>

            <details class="group" <?= in_array($current_page, ['alltree', 'treefamily', 'treespecies']) ? 'open' : '' ?>>
                <summary class="flex items-center justify-between gap-2 px-3.5 py-3 rounded-xl font-medium text-sm cursor-pointer list-none transition-all
                    <?= in_array($current_page, ['alltree', 'treefamily', 'treespecies'])
                        ? 'text-white bg-emerald-500/10 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
                        : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' ?>">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-tree-city w-5 <?= in_array($current_page, ['alltree', 'treefamily', 'treespecies']) ? 'text-emerald-400' : '' ?>"></i>
                        <span>Trees</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs opacity-60 transition-transform duration-200 group-open:rotate-90"></i>
                </summary>
                <div class="ml-6 mt-1 flex flex-col gap-1 border-l border-white/10 pl-3">
                    <a href="/treecamus/tree" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= $current_page === 'alltree' ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-white' ?>">All Tree</a>
                    <a href="/treecamus/tree/family" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= $current_page === 'treefamily' ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-white' ?>">Tree Family</a>
                    <a href="/treecamus/tree/species" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all <?= $current_page === 'treespecies' ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-white' ?>">Tree Species</a>
                </div>
            </details>

        <?php endif; ?>

        <?php if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 4): ?>
            <a href="/treecamus/tree"
               class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all
               <?= $current_page === 'alltree'
                   ? 'text-white bg-emerald-500/10 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
                   : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' ?>">
                <i class="fa-solid fa-tree-city <?= $current_page === 'alltree' ? 'text-emerald-400' : '' ?> w-5"></i>
                <span>Your Tree</span>
            </a>
        <?php endif; ?>

        <a href="/treecamus/maps"
           class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all
           <?= $current_page === 'maps'
               ? 'text-white bg-emerald-500/10 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
               : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' ?>">
            <i class="fa-solid fa-map-location-dot <?= $current_page === 'maps' ? 'text-emerald-400' : '' ?> w-5"></i>
            <span>Maps</span>
        </a>

        <a href="/treecamus/statistics"
           class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all
           <?= $current_page === 'stats'
               ? 'text-white bg-emerald-500/10 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]'
               : 'text-slate-400 hover:text-white hover:bg-white/[0.04]' ?>">
            <i class="fa-solid fa-chart-line <?= $current_page === 'stats' ? 'text-emerald-400' : '' ?> w-5"></i>
            <span>Statistics</span>
        </a>

        <a href="/treecamus/manual/Tree-CaMUS User Manual.pdf" target="_blank"
           class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm text-slate-400 hover:text-white hover:bg-white/[0.04] transition-all">
            <i class="fa-solid fa-book-open w-5"></i>
            <span>User Manual</span>
        </a>
    </nav>

    <div class="mt-auto pt-4 border-t border-white/10 space-y-2">
        <button id="theme-toggle"
                class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-slate-400 hover:text-white hover:bg-white/[0.04] transition-all">
            <i class="fa-solid fa-sun w-5 dark:hidden"></i>
            <i class="fa-solid fa-moon w-5 hidden dark:inline"></i>
            <span class="dark:hidden">Light mode</span>
            <span class="hidden dark:inline">Dark mode</span>
        </button>
        <a href="/treecamus/logout" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-slate-400 hover:text-white hover:bg-white/[0.04] transition-all">
            <i class="fa-solid fa-right-from-bracket w-5"></i>
            <span>Sign out</span>
        </a>
    </div>
</aside>

<main class="ml-64 flex-1 p-8">