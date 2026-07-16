<?php

require_once 'config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Remove all session variables */

$_SESSION = [];

/* Delete session cookie */

if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(

        session_name(),

        '',

        time() - 42000,

        $params["path"],

        $params["domain"],

        $params["secure"],

        $params["httponly"]

    );
}

/* Destroy session */

session_destroy();

/* Prevent caching */

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/* Redirect */

header("Location: login.php");

exit;
