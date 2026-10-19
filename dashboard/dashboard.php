<?php
$current_page = 'dashboard';
include '../includes/header.php';
include '../includes/menu.php';

// Real monthly planting trend, built from the same tree-creation data
// used elsewhere (aggregated by month instead of by day)
$monthlySql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, DATE_FORMAT(created_at, '%b') as label, COUNT(*) as cnt
               FROM trees
               WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 9 MONTH)
               GROUP BY ym, label
               ORDER BY ym ASC";
$stmt = $dbh->prepare($monthlySql);
$stmt->execute();
$monthlyPlanting = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Real tree status / health distribution
$statusSql = "SELECT trs.treestatustitle, COUNT(*) as cnt
              FROM trees t
              JOIN treestatus trs ON t.treestatusID = trs.treestatusID
              GROUP BY trs.treestatustitle
              ORDER BY cnt DESC";
$stmt2 = $dbh->prepare($statusSql);
$stmt2->execute();
$statusRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
$statusTotal = array_sum(array_column($statusRows, 'cnt'));

$statusColors = ['emerald', 'amber', 'rose', 'cyan', 'violet'];
?>

<header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-gray-400 font-medium mb-1">
            <span>Home</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-gray-600"></i>
            <span class="text-emerald-500 dark:text-emerald-400 font-semibold">Dashboard Overview</span>
        </div>
        <div class="flex items-center space-x-3">
            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">Executive Dashboard</h2>
            <span class="flex h-2.5 w-2.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
        </div>
    </div>
</header>

<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="glass-card-emerald rounded-2xl p-5 relative group overflow-hidden">
        <div class="absolute top-0 right-0 p-4 text-emerald-500/10 group-hover:text-emerald-500/20 transition-colors">
            <i data-lucide="trees" class="w-16 h-16 -mr-4 -mt-4"></i>
        </div>
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-500 dark:text-emerald-400/90">Total Tree Planted</span>
        </div>
        <div class="text-3xl font-extrabold tracking-tight mb-1"><?php echo totalTreePlanted($dbh); ?></div>
        <div class="text-xs text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span>Across all campuses</span>
        </div>
    </div>

    <div class="glass-card-amber rounded-2xl p-5 relative group overflow-hidden">
        <div class="absolute top-0 right-0 p-4 text-amber-500/10 group-hover:text-amber-500/20 transition-colors">
            <i data-lucide="user-check" class="w-16 h-16 -mr-4 -mt-4"></i>
        </div>
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-500 dark:text-amber-400/90">Total Arborist</span>
        </div>
        <div class="text-3xl font-extrabold tracking-tight mb-1"><?php echo totalArborist($dbh); ?></div>
        <div class="text-xs text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            <span>On active duty</span>
        </div>
    </div>

    <div class="glass-card-violet rounded-2xl p-5 relative group overflow-hidden">
        <div class="absolute top-0 right-0 p-4 text-purple-500/10 group-hover:text-purple-500/20 transition-colors">
            <i data-lucide="building-2" class="w-16 h-16 -mr-4 -mt-4"></i>
        </div>
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-purple-500 dark:text-purple-400/90">Total Campus</span>
        </div>
        <div class="text-3xl font-extrabold tracking-tight mb-1"><?php echo totalCampus($dbh); ?></div>
        <div class="text-xs text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
            <span>Across all state branches</span>
        </div>
    </div>

    <div class="glass-card-cyan rounded-2xl p-5 relative group overflow-hidden">
        <div class="absolute top-0 right-0 p-4 text-cyan-500/10 group-hover:text-cyan-500/20 transition-colors">
            <i data-lucide="map" class="w-16 h-16 -mr-4 -mt-4"></i>
        </div>
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-cyan-500 dark:text-cyan-400/90">Total Zone</span>
        </div>
        <div class="text-3xl font-extrabold tracking-tight mb-1"><?php echo totalZone($dbh); ?></div>
        <div class="text-xs text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
            <span>Mapped coverage</span>
        </div>
    </div>
</section>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6"></div>

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

            <input
                type="text"
                id="dashboardSearch"
                placeholder="Search tree, zone..."
                class="w-full bg-white dark:bg-slate-900/80 border border-slate-300 dark:border-white/10 focus:border-emerald-500/60 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-all placeholder:text-slate-400">
        </div>
    </div>
</div>

    <div class="overflow-x-auto">
        <table id="example1" class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-white/10 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold">
                    <th class="p-4 w-20">No</th>
                    <th class="p-4">Tree name</th>
                    <th class="p-4">Coordinate</th>
                    <th class="p-4">Zone</th>
                    <th class="p-4 text-center w-24">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/[0.03]">
                <?php if ($_SESSION['usertype'] == 3) { ?>
                    <?php listAlltreesdashboard($dbh); ?>
                <?php } elseif ($_SESSION['usertype'] == 4) { ?>
                    <?php listTreesHasbeenCareByArborist($dbh, $userID); ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<footer class="mt-6 text-xs text-slate-400 dark:text-slate-500 flex justify-between">
    <strong><?php copyright(); ?></strong>
    <span>Version 3.2.0</span>
</footer>

<script>
  $(function () {
    const table = $("#example1").DataTable({responsive: true,lengthChange: false,autoWidth: false,searching: true,dom: 'rtip'});
    $('#dashboardSearch').on('input', function () {table.search(this.value).draw();});
  });

  function initChart() {
      const canvas = document.getElementById('plantingChart');
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      const dpr = window.devicePixelRatio || 1;
      const rect = canvas.getBoundingClientRect();
      canvas.width = rect.width * dpr;
      canvas.height = rect.height * dpr;
      ctx.scale(dpr, dpr);

      const months = <?php echo json_encode(array_column($monthlyPlanting, 'label')); ?>;
      const dataValues = <?php echo json_encode(array_map('intval', array_column($monthlyPlanting, 'cnt'))); ?>;

      if (months.length === 0) return;

      const padding = 30;
      const width = rect.width - padding * 2;
      const height = rect.height - padding * 2;
      const maxVal = Math.max(...dataValues, 10) * 1.2;

      const isDark = document.documentElement.classList.contains('dark');
      ctx.strokeStyle = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)';
      ctx.lineWidth = 1;
      for (let i = 0; i <= 4; i++) {
          const y = padding + (height / 4) * i;
          ctx.beginPath();
          ctx.moveTo(padding, y);
          ctx.lineTo(rect.width - padding, y);
          ctx.stroke();
      }

      const points = dataValues.map((val, idx) => {
          const x = months.length > 1 ? padding + (width / (months.length - 1)) * idx : padding + width / 2;
          const y = rect.height - padding - (val / maxVal) * height;
          return { x, y };
      });

      const gradient = ctx.createLinearGradient(0, 0, 0, rect.height);
      gradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
      gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

      ctx.beginPath();
      ctx.moveTo(points[0].x, points[0].y);
      for (let i = 1; i < points.length; i++) {
          const xc = (points[i].x + points[i - 1].x) / 2;
          const yc = (points[i].y + points[i - 1].y) / 2;
          ctx.quadraticCurveTo(points[i - 1].x, points[i - 1].y, xc, yc);
      }
      ctx.lineTo(points[points.length - 1].x, points[points.length - 1].y);
      ctx.lineTo(points[points.length - 1].x, rect.height - padding);
      ctx.lineTo(points[0].x, rect.height - padding);
      ctx.closePath();
      ctx.fillStyle = gradient;
      ctx.fill();

      ctx.beginPath();
      ctx.moveTo(points[0].x, points[0].y);
      for (let i = 1; i < points.length; i++) {
          const xc = (points[i].x + points[i - 1].x) / 2;
          const yc = (points[i].y + points[i - 1].y) / 2;
          ctx.quadraticCurveTo(points[i - 1].x, points[i - 1].y, xc, yc);
      }
      ctx.lineTo(points[points.length - 1].x, points[points.length - 1].y);
      ctx.strokeStyle = '#10B981';
      ctx.lineWidth = 3;
      ctx.shadowColor = 'rgba(16, 185, 129, 0.5)';
      ctx.shadowBlur = 10;
      ctx.stroke();
      ctx.shadowBlur = 0;

      points.forEach((pt, idx) => {
          ctx.beginPath();
          ctx.arc(pt.x, pt.y, 4, 0, Math.PI * 2);
          ctx.fillStyle = isDark ? '#090D16' : '#ffffff';
          ctx.strokeStyle = '#06B6D4';
          ctx.lineWidth = 2;
          ctx.fill();
          ctx.stroke();

          ctx.fillStyle = isDark ? '#9CA3AF' : '#64748b';
          ctx.font = '10px "JetBrains Mono"';
          ctx.textAlign = 'center';
          ctx.fillText(months[idx], pt.x, rect.height - 10);
      });
  }

  window.addEventListener('load', function () {
      lucide.createIcons();
      initChart();
      window.addEventListener('resize', initChart);
  });
</script>

<?php include '../includes/footer.php'; ?>
