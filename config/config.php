<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

define('SITE_NAME', 'VIDURA Activity Clubs');
define('BASE_URL', 'http://localhost/vidurasite');