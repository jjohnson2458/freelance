<?php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/core/Env.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Csrf.php';
require_once BASE_PATH . '/core/ErrorHandler.php';

use Core\Env;
use Core\Database;
use Core\Router;
use Core\ErrorHandler;

// Load environment
Env::load(BASE_PATH . '/.env');

// Initialize error handler
ErrorHandler::init();

// Start session
session_start();

// Initialize CSRF
Core\Csrf::init();

// Load routes and dispatch
$router = new Router();
require_once BASE_PATH . '/config/routes.php';
$router->dispatch();
