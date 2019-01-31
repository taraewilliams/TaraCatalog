<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set("America/Indiana/Indianapolis");
define("ROOT_PATH", getcwd());

if (PHP_SAPI == 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require '../vendor/autoload.php';

$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

/* Dependencies */
require __DIR__ . '/../src/dependencies.php';

/* Controllers */
foreach(glob('../api/*/*.php') as $file) {
    require_once($file);
}

/* Config */
foreach(glob('../config/*.php') as $file) {
    require_once($file);
}

/* Models */
foreach(glob('../model/*.php') as $file) {
    require_once($file);
}

/* Services */
foreach(glob('../service/*.php') as $file) {
    require_once($file);
}

$app->run();
