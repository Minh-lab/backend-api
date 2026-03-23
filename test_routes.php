<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

// Get all routes
$routes = $app->make('router')->getRoutes();

echo "=== All Routes ===\n";
foreach($routes->getRoutes() as $route) {
    $path = $route->getPath();
    if (strpos($path, 'statistics') !== false || strpos($path, 'capstone') !== false) {
        echo $path . " [" . implode(',', $route->getMethods()) . "]\n";
    }
}

echo "\n=== Total Routes: " . count($routes->getRoutes()) . " ===\n";
