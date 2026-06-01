<?php
if (session_status() === PHP_SESSION_NONE) session_start();
ini_set('memory_limit', '256M');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../app/helpers/Env.php";
require_once __DIR__ . "/../app/router/router.php";

Env::load(dirname(__DIR__) . '/config/.Env');
