<?php
require_once '../core/Router.php';
require_once '../config/config.php';

// Start the router
$router = new Router();
$router->dispatch($_SERVER['REQUEST_URI']);