<?php
include("connection/connection.php");
include("function/function.php");

session_start();

// - unset all of the session variables.
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// - destroy the session.
session_unset();
session_destroy();
header("Location: /treecamus/app/index.php");
exit;
?>