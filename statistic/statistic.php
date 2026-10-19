<?php
$current_page = 'stats';
include_once '../connection/connection.php';
include_once '../function/function.php';
include_once '../settings/settings.php';
include '../includes/header.php';
include '../includes/menu.php';

$familyData = getFamilyStatisticsWithPercentage($dbh);
$speciesData = getSpeciesStatisticsWithPercentage($dbh);
$treeData = getTreeData($dbh);
?>

<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>

<header class="flex justify-between items-center mb-8 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Statistic</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">System-wide tree data insights</p>
    </div>
    <div class="flex items-center gap-2 bg-white/70 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 px-4 py-2 rounded-full text-xs text-slate-500 dark:text-slate-400">
        <a href="/treecamus/dashboard" class="hover:text-emerald-500">Home</a><span>/</span><span class="text-slate-800 dark:text-white font-medium">Statistic</span>
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

<div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-4 flex items-center justify-between">
        <h2 class="text-white font-bold text-lg">Recent Uploaded Trees</h2>
        <span class="text-emerald-50 text-sm">Total Trees: <strong><?php echo statBoxTotalTree($dbh); ?></strong></span>
    </div>
    <div class="p-4"><div id="treeChart" style="width: 100%; height: 400px"></div></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-4 flex items-center justify-between">
            <h2 class="text-white font-bold text-lg">Family Statistics</h2>
            <span class="text-emerald-50 text-sm">Total Family Type: <strong><?php echo statBoxTotalFamily($dbh); ?></strong></span>
        </div>
        <div class="p-4"><div id="familyChart" style="width: 100%; height: 400px"></div></div>
    </div>
    <div class="bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-4 flex items-center justify-between">
            <h2 class="text-white font-bold text-lg">Species Statistics</h2>
            <span class="text-emerald-50 text-sm">Total Species Type: <strong><?php echo statBoxTotalSpecies($dbh); ?></strong></span>
        </div>
        <div class="p-4"><div id="speciesChart" style="width: 100%; height: 400px"></div></div>
    </div>
</div>

<div class="modal fade" id="treeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title font-semibold">Planted Tree</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body" id="treeModalBody"></div>
    </div></div>
</div>

<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
  var themeColors = ['#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#3b82f6', '#f43f5e', '#22c55e', '#eab308', '#a855f7', '#0ea5e9'];

  function createPieChart(elId, title, rawData, colors) {
      var container = document.getElementById(elId);
      if(!container) return;
      var chart = echarts.init(container);
      var data = (rawData || []).map(function (d) { return { name: d.name, value: d.y }; });
      var isDark = document.documentElement.classList.contains('dark');

      chart.setOption({
          color: colors,
          textStyle: { color: isDark ? '#e2e8f0' : '#1e293b' },
          tooltip: { trigger: 'item', formatter: '{b}: <b>{c} Trees</b> ({d}%)' },
          series: [{
              name: 'Count', type: 'pie', radius: '65%', avoidLabelOverlap: true,
              data: data,
              label: { formatter: '{b}: {c} Trees ({d}%)', color: isDark ? '#e2e8f0' : '#1e293b' }
          }]
      });
      window.addEventListener('resize', function () { chart.resize(); });
  }

  var familyData = <?php echo json_encode($familyData ?: []); ?>;
  var speciesData = <?php echo json_encode($speciesData ?: []); ?>;

  createPieChart('familyChart', 'Family Statistics', familyData, themeColors);
  createPieChart('speciesChart', 'Species Statistics', speciesData, themeColors);
</script>

<script>
  var treeData = <?php echo json_encode($treeData ?: []); ?>;
  var isDark = document.documentElement.classList.contains('dark');

  var now = new Date();
  var thirtyDaysAgo = new Date(now.getTime() - (30 * 24 * 60 * 60 * 1000));

  var filteredData = treeData.filter(function (item) {
      var itemDate = new Date(item.day);
      return itemDate >= thirtyDaysAgo;
  });
  filteredData.sort(function (a, b) { return new Date(a.day) - new Date(b.day); });

  var treeCounts = [], treeDates = [], treeDetails = [];
  filteredData.forEach(function (item) {
      treeCounts.push(item.tree_count);
      treeDates.push(item.day);
      var dayTreeDetails = [];
      if (item.trees_planted && Array.isArray(item.trees_planted)) {
          item.trees_planted.forEach(function (tree) {
              dayTreeDetails.push({ tree_unique_code: tree.tree_unique_code, family: tree.family, species: tree.species });
          });
      }
      treeDetails.push(dayTreeDetails);
  });

  var chartElem = document.getElementById('treeChart');
  if(chartElem) {
      var treeChart = echarts.init(chartElem);
      treeChart.setOption({
          title: { text: 'Recent Trees Planted / Uploaded', textStyle: { color: isDark ? '#e2e8f0' : '#1e293b' } },
          tooltip: {
              trigger: 'axis',
              formatter: function (params) {
                  var idx = params[0].dataIndex;
                  var details = treeDetails[idx] || [];
                  var html = '<div style="max-height: 200px; overflow-y: auto; padding: 9px;"><b>' + params[0].value + ' Trees planted / uploaded on this day</b><br/><br>';
                  details.forEach(function (tree, i) {
                      html += '<b>' + (i + 1) + '</b>. Tree Unique Code: <b>' + tree.tree_unique_code + '</b><br/>';
                      html += '&nbsp;&nbsp;&nbsp; - Family: ' + tree.family + '<br/>';
                      html += '&nbsp;&nbsp;&nbsp; - Species: ' + tree.species + '<br/> <br>';
                  });
                  html += '</div>';
                  return html;
              }
          },
          xAxis: { type: 'category', data: treeDates, name: 'Date', axisLabel: { color: isDark ? '#94a3b8' : '#64748b' } },
          yAxis: { type: 'value', name: 'Number of Trees', axisLabel: { color: isDark ? '#94a3b8' : '#64748b' } },
          color: ['#10b981'],
          series: [{ name: 'Trees', type: 'line', data: treeCounts, symbol: 'circle', symbolSize: 8 }]
      });

      treeChart.on('click', function (params) {
          var idx = params.dataIndex;
          var details = treeDetails[idx] || [];
          var message = '';
          details.forEach(function (tree, i) {
              message += (i + 1) + '. Tree Unique Code: <b>' + tree.tree_unique_code + '</b><br/>';
              message += 'Family: ' + tree.family + '<br/>';
              message += 'Species: ' + tree.species + '<br/><br/>';
          });
          $('#treeModalBody').html(message);
          $('#treeModal').modal('show');
      });

      window.addEventListener('resize', function () { treeChart.resize(); });
  }
</script>

<?php include '../includes/footer.php'; ?>