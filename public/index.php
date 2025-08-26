<?php

session_start();

require_once '../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\Auth\AuthController;
use App\Controllers\Admin\AdminController;
use App\Controllers\Faculty\FacultyController;

// Initialize router
$router = new Router();

// Create controllers
$authController = new AuthController();
// Lazy-instantiate protected controllers inside route handlers to avoid auth redirects on /login
$adminController = new AdminController();
$facultyController = new FacultyController();

// Debug information (remove this later)
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
error_log("Requested path: " . $currentPath);

// Root route - redirect to login
$router->get('/', function() {
    header('Location: /login');
    exit;
});

// Login page
$router->get('/login', function() use ($authController) {
    $authController->showLogin();
});

// API routes for authentication
$router->post('/api/auth/login', function() use ($authController) {
    $authController->login();
});

$router->post('/api/auth/logout', function() use ($authController) {
    $authController->logout();
});

// Admin Dashboard Routes
$router->get('/admin/dashboard', function() {
    (new AdminController())->dashboard();
});

$router->get('/admin/logout', function() {
    (new AdminController())->logout();
});

// Faculty Dashboard Routes
$router->get('/faculty/dashboard', function() {
    (new FacultyController())->dashboard();
});

$router->get('/faculty/logout', function() {
    (new FacultyController())->logout();
});

// Student success placeholder (until student dashboard exists)
$router->get('/student-success', function() {
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    echo '<h1>Student Login Successful!</h1>';
    echo '<p>Welcome Student! You have successfully logged in.</p>';
    echo '<p><a href="' . $basePath . '/login">Back to Login</a></p>';
});

// Admin User Management Routes
$router->post('/admin/users/add', function() {
    (new AdminController())->addUser();
});

$router->post('/admin/users/add-student', function() {
    (new AdminController())->addStudent();
});

$router->post('/admin/users/edit-student', function() {
    (new AdminController())->editStudent();
});

$router->post('/admin/users/edit/{id}', function($id) {
    (new AdminController())->editUser($id);
});

$router->post('/admin/users/delete-student', function() {
    (new AdminController())->deleteStudent();
});

// Admin Faculty Management Routes
$router->post('/admin/users/add-faculty', function() {
    (new AdminController())->addFaculty();
});

$router->post('/admin/users/edit-faculty', function() {
    (new AdminController())->editFaculty();
});

$router->post('/admin/users/delete-faculty', function() {
    (new AdminController())->deleteFaculty();
});

$router->post('/admin/users/delete/{id}', function($id) {
    (new AdminController())->deleteUser($id);
});

// Handle the request
$router->handleRequest();
?>