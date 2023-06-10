<?php

use Controllers\DiskController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/',$uri);

if ($uri[1] !== 'disk') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$state = null;
if (isset($uri[2])) {
    $state = (int) $uri[2];
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

$controller = new DiskController($dbConnection, $requestMethod);
$controller->processRequest();