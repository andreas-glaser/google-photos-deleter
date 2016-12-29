<?php

use AndreasGlaser\GooglePhotosDeleter\GooglePhotosDeleter;

require __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$googlePhotosDeleter = new GooglePhotosDeleter();
$googlePhotosDeleter->dispatchRequest(strtok($_SERVER['REQUEST_URI'], '?'));