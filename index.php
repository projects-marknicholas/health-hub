<?php
require 'config.php';
require 'router.php';

// Controllers
require 'controllers/auth.php';
require 'controllers/admin.php';
require 'controllers/user.php';

// Initialize Router
$router = new Router();

// Auth
$router->post('/api/auth/register', 'AuthController@register');
$router->post('/api/auth/login', 'AuthController@login');

// Admin
$router->post('/api/v1/set-availability', 'AdminController@set_availability');
$router->get('/api/v1/availability', 'AdminController@availability');
$router->get('/api/v1/appointment', 'AdminController@appointment');

// User
$router->post('/api/v2/set-appointment', 'UserController@set_appointment');
$router->get('/api/v2/availability', 'UserController@availability');
$router->get('/api/v2/appointment', 'UserController@appointment');
$router->delete('/api/v2/appointment', 'UserController@delete_appointment');

// Dispatch the request
$router->dispatch();
?>