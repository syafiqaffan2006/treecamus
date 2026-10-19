<?php
// Prevent global browser and proxy caching across all pages
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/**
 * Front Controller for TREE-CAMUS v2 clean URLs.
 */

$route = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Strip the project's base folder name from the route if present
$basePath = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($basePath !== '' && strpos($route, $basePath) === 0) {
    $route = trim(substr($route, strlen($basePath)), '/');
}

// Static routes: clean URL => real file path (relative to project root)
$routes = [
    'dashboard'           => 'dashboard/dashboard.php',
    'tree'                => 'tree/listtree.php',
    'tree/add'            => 'tree/addtree.php',
    'tree/family'         => 'tree/treefamily.php',
    'tree/family/add'     => 'tree/addtreefamily.php',
    'tree/species'        => 'tree/treespecies.php',
    'tree/species/add'    => 'tree/addtreespecies.php',
    'users/list'          => 'users/listusers.php',
    'users/view'          => 'users/viewuser.php',
    'users/edit'          => 'users/edituser.php',
    'maps'                => 'maps/map.php',
    'statistics'          => 'statistic/statistic.php',
    'public-statistics'   => 'app/public-statistic.php',
    'general-information' => 'app/info.php',
    'explore-trees'       => 'app/explore-trees.php',
    'home'                => 'app/index.php',
    'tree/edit'           => 'tree/editTree.php',
    'tree/view'           => 'tree/viewtree.php',
    'users/edit'          => 'users/edituser.php',
    'users/view'          => 'users/viewuser.php',
    'logout'              => 'app/logout.php',
    ''                    => 'app/index.php',
];

$target = null;

// Dynamic routes with a token, e.g. /tree/edit/abc123, /tree/view/abc123
if (preg_match('#^tree/edit/(.+)$#', $route, $m)) {
    $_GET['id'] = $m[1];
    $target = 'tree/editTree.php';
} elseif (preg_match('#^tree/view/(.+)$#', $route, $m)) {
    $_GET['id'] = $m[1];
    $target = 'tree/viewtree.php';
} elseif (preg_match('#^explore-trees/(.+)$#', $route, $m)) {
    $_GET['id'] = $m[1];
    $target = 'app/explore-trees.php';
} elseif (preg_match('#^tree/family/edit/(.+)$#', $route, $m)) {
    $_GET['id'] = $m[1];
    $target = 'tree/edittreefamily.php';
} elseif (preg_match('#^tree/species/edit/(.+)$#', $route, $m)) {
    $_GET['id'] = $m[1];
    $target = 'tree/edittreespecies.php';
} elseif (preg_match('#^users/view/(.+)$#', $route, $m)) {
    $_GET['token'] = $m[1];
    $target = 'users/viewuser.php';
} elseif (preg_match('#^users/edit/(.+)$#', $route, $m)) {
    $_GET['token'] = $m[1];
    $target = 'users/edituser.php';
} elseif ($route === 'users') {
    // old menu link /treecamus/users -> new list URL
    header('Location: /treecamus/users/list');
    exit;
} elseif (isset($routes[$route])) {
    $target = $routes[$route];
}

if ($target === null) {
    http_response_code(404);
    echo "404 - Page not found.";
    exit;
}

$projectRoot = __DIR__;
$targetDir = $projectRoot . '/' . dirname($target);
$targetFile = basename($target);

chdir($targetDir);
include $targetFile;