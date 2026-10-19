<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once(__DIR__ . "/../connection/connection.php");
include_once(__DIR__ . "/../function/function.php");
include_once(__DIR__ . "/../settings/settings.php");

// Must be signed in (session is set by index.php after Google login)
if (!isset($_SESSION['login_id']) || !isset($_SESSION['user_id'])) {
    header("Location: /treecamus/index.php");
    exit;
}

$userID = $_SESSION['user_id'];

// Load the real user + role from the database on every page
$stmtUser = $dbh->prepare("SELECT u.name, u.profile_image, r.roleID, r.roletitle
                           FROM users u
                           JOIN useraccess ua ON u.userID = ua.userID
                           JOIN roles r ON ua.roleID = r.roleID
                           WHERE u.userID = :uid AND u.is_allow = 1 AND ua.is_allow = 1");
$stmtUser->execute([':uid' => $userID]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: /treecamus/logout");
    exit;
}

$_SESSION['usertype'] = $user['roleID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TREE CaMUs</title>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
  tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        fontFamily: {
          sans: ['Plus Jakarta Sans', 'sans-serif'],
          mono: ['JetBrains Mono', 'monospace'],
        },
        colors: {
          darkBg: '#070a12',
          lightBg: '#f4f7f5',
          glassBg: 'rgba(15, 23, 42, 0.65)',
          glassBgLight: 'rgba(255, 255, 255, 0.75)',
          emeraldGlow: '#10b981',
          cyanGlow: '#06b6d4',
          amberGlow: '#f59e0b',
          purpleGlow: '#8b5cf6',
        }
      }
    }
  }
</script>

<script>
  if (localStorage.getItem('theme') === 'dark' ||
     (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  }
</script>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="/treecamus/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
  /* DataTables pagination, search box, and length selector restyled to match the glass theme */
  .dataTables_wrapper .dataTables_paginate {
      display: flex; align-items: center; gap: 0.35rem; margin-top: 1rem; flex-wrap: wrap;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button {
      padding: 0.4rem 0.75rem; border-radius: 0.65rem; font-size: 0.75rem; font-weight: 500;
      border: 1px solid rgba(148,163,184,0.25); color: inherit; cursor: pointer;
  }
  html.dark .dataTables_wrapper .dataTables_paginate .paginate_button {
      background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); color: #e2e8f0;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: linear-gradient(to right, #059669, #10b981); color: #fff !important; border-color: transparent;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
      opacity: 0.35; cursor: not-allowed;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
      border-color: rgba(16,185,129,0.5); color: #10b981;
  }
  .dataTables_wrapper .dataTables_info {
      font-size: 0.75rem; color: #64748b; margin-top: 1rem;
  }
  html.dark .dataTables_wrapper .dataTables_info { color: #94a3b8; }
  .dataTables_wrapper .dataTables_length select,
  .dataTables_wrapper .dataTables_filter input {
      border-radius: 0.65rem; border: 1px solid rgba(148,163,184,0.3); padding: 0.35rem 0.6rem; font-size: 0.75rem;
      background: #fff; color: #1e293b;
  }
  html.dark .dataTables_wrapper .dataTables_length select,
  html.dark .dataTables_wrapper .dataTables_filter input {
      background: rgba(0,0,0,0.3); border-color: rgba(255,255,255,0.1); color: #fff;
  }
  table.dataTable thead th { border-bottom: none !important; }
  table.dataTable tbody td { border-top: none !important; }
  select option {
    background-color: #0f172a;
    color: #f1f5f9;
    padding: 0.5rem;
  }
  html:not(.dark) select option {
    background-color: #ffffff;
    color: #1e293b;
  }
</style>

<style>
  .select2-container--default .select2-selection--single { background: #fff !important; }
  .select2-container--default .select2-selection--single .select2-selection__rendered { color: #1c1c1a !important; }
  .select2-dropdown { background: #fff !important; z-index: 9999; }
  .select2-container--default .select2-results__option { color: #1c1c1a !important; background: #fff !important; }
  .select2-container--default .select2-results__option--highlighted[aria-selected] { background: #10b981 !important; color: #fff !important; }
  .select2-search--dropdown .select2-search__field { color: #1c1c1a !important; background: #fff !important; }

  .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; overflow-y: auto; z-index: 1050; background: rgba(0,0,0,0.6); }
  .modal.show { display: block; }
  .modal-dialog { margin: 3rem auto; max-width: 500px; position: relative; }
  .modal-lg { max-width: 800px; }
  .modal-dialog-centered { display: flex; align-items: center; min-height: calc(100% - 3rem); }
  .modal-content { background: #0f172a; color: #f1f5f9; border: 1px solid rgba(255,255,255,0.1); border-radius: 1rem; overflow: hidden; width: 100%; backdrop-filter: blur(20px); }
  .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
  .modal-body { padding: 1rem 1.25rem; }
  .modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.08); }
  .modal-title { margin: 0; }
  .close { background: none; border: none; color: inherit; cursor: pointer; font-size: 1.25rem; line-height: 1; }

  .glass-card-emerald { background: rgba(18, 24, 38, 0.7); border: 1px solid rgba(16, 185, 129, 0.2); box-shadow: inset 0 1px 0 rgba(16, 185, 129, 0.3), 0 10px 30px -10px rgba(16, 185, 129, 0.15); }
  .dark .glass-card-emerald:hover { border-color: rgba(16, 185, 129, 0.5); box-shadow: inset 0 1px 0 rgba(16, 185, 129, 0.5), 0 12px 35px -8px rgba(16, 185, 129, 0.25); }
  .glass-card-amber { background: rgba(18, 24, 38, 0.7); border: 1px solid rgba(245, 158, 11, 0.2); box-shadow: inset 0 1px 0 rgba(245, 158, 11, 0.3), 0 10px 30px -10px rgba(245, 158, 11, 0.15); }
  .dark .glass-card-amber:hover { border-color: rgba(245, 158, 11, 0.5); box-shadow: inset 0 1px 0 rgba(245, 158, 11, 0.5), 0 12px 35px -8px rgba(245, 158, 11, 0.25); }
  .glass-card-violet { background: rgba(18, 24, 38, 0.7); border: 1px solid rgba(139, 92, 246, 0.2); box-shadow: inset 0 1px 0 rgba(139, 92, 246, 0.3), 0 10px 30px -10px rgba(139, 92, 246, 0.15); }
  .dark .glass-card-violet:hover { border-color: rgba(139, 92, 246, 0.5); box-shadow: inset 0 1px 0 rgba(139, 92, 246, 0.5), 0 12px 35px -8px rgba(139, 92, 246, 0.25); }
  .glass-card-cyan { background: rgba(18, 24, 38, 0.7); border: 1px solid rgba(6, 182, 212, 0.2); box-shadow: inset 0 1px 0 rgba(6, 182, 212, 0.3), 0 10px 30px -10px rgba(6, 182, 212, 0.15); }
  .dark .glass-card-cyan:hover { border-color: rgba(6, 182, 212, 0.5); box-shadow: inset 0 1px 0 rgba(6, 182, 212, 0.5), 0 12px 35px -8px rgba(6, 182, 212, 0.25); }

  html:not(.dark) .glass-card-emerald,
  html:not(.dark) .glass-card-amber,
  html:not(.dark) .glass-card-violet,
  html:not(.dark) .glass-card-cyan {
      background: rgba(255,255,255,0.7);
      box-shadow: 0 10px 30px -10px rgba(0,0,0,0.08);
  }

  .pulse-glow { animation: pulse-glow-anim 2s infinite ease-in-out; }
  @keyframes pulse-glow-anim {
      0%, 100% { opacity: 0.8; transform: scale(1); }
      50% { opacity: 1; transform: scale(1.1); }
  }
</style>
</head>
<body class="bg-lightBg dark:bg-darkBg text-slate-800 dark:text-slate-100 font-sans min-h-screen relative overflow-x-hidden">

<div class="fixed top-[-100px] left-[-100px] w-[500px] h-[500px] rounded-full bg-emerald-500/10 blur-[120px] pointer-events-none z-0"></div>
<div class="fixed bottom-[-200px] right-[-100px] w-[600px] h-[600px] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>

<div class="flex min-h-screen relative z-10">