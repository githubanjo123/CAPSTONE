### Technical Test Documentation (TTD)

A documentator’s narrative of a Driver–Navigator pair programming session, reconstructing how this codebase was incrementally built using IDE workflows and TDD (Red–Green–Refactor).

---

## 1) Project Initialization: Empty Workspace to First Commit

- The Driver opened their IDE and created a new empty project in the file explorer named `examination-system`.
- In the project explorer, they right‑clicked the root and created a new folder `public` to host the entrypoint and web‑served files.
- The Navigator suggested keeping source under `src`, so the Driver created a `src` folder via the IDE, then inside it added an `App` folder to match PSR‑4 autoloading conventions.
- With the skeleton ready, the Navigator proposed PHPUnit for tests. The Driver created a `tests` folder at the root using the IDE new-folder action and inside it added subfolders `Unit` and `Integration` for test separation.

The initial tree, as seen in the IDE explorer, after these first steps:
```
/(project root)
  public/
  src/
    App/
  tests/
    Unit/
    Integration/
```

---

## 2) Dependency Setup: Composer and Testing

- The Navigator recommended PHPUnit 9 to match PHP 7/8 constraints. The Driver used the IDE’s built‑in terminal only for dependency installation and test runs.

Commands run in the IDE terminal:
```bash
composer init --name=acme/examination-system --no-interaction
composer require --dev phpunit/phpunit:^9.6
composer require guzzlehttp/guzzle:^7.9
```

- In the IDE, the Driver opened `composer.json` and configured PSR‑4 autoloading to point `App\` to `src/App/`.

```12:13:composer.json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/App/"
        }
    },
    "require-dev": {
        "phpunit/phpunit": "^9.6"
    },
    "require": {
        "guzzlehttp/guzzle": "^7.9"
    }
}
```

- Still in the IDE, the Driver created `phpunit.xml` at the project root, setting `tests/bootstrap.php` as bootstrap and enabling coverage for the `src` directory.

```1:32:phpunit.xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         verbose="false"
         stopOnFailure="false"
         processIsolation="false">

  <php>
    <ini name="error_reporting" value="-1"/>
    <ini name="output_buffering" value="4096"/>
    <ini name="implicit_flush" value="0"/>
    <env name="APP_ENV" value="testing"/>
  </php>

  <testsuites>
    <testsuite name="Unit Tests">
      <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Integration Tests">
      <directory>tests/Integration</directory>
    </testsuite>
  </testsuites>

  <coverage processUncoveredFiles="true">
    <include>
      <directory suffix=".php">src</directory>
    </include>
  </coverage>

</phpunit>
```

- The Driver added a `tests/bootstrap.php` via File > New to prepare the test environment and avoid session/output conflicts.

```1:31:tests/bootstrap.php
<?php

// Bootstrap file for PHPUnit tests
// This helps resolve session and output buffering issues

// Include the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Start output buffering to prevent "headers already sent" errors
if (ob_get_level() === 0) {
    ob_start();
}

// Set up test environment
$_ENV['APP_ENV'] = 'testing';

// Configure session settings for testing (but don't start sessions automatically)
ini_set('session.use_cookies', '0');
ini_set('session.use_only_cookies', '0');
ini_set('session.cache_limiter', '');

// Reset superglobals for clean test state
$_SESSION = [];
$_GET = [];
$_POST = [];
$_REQUEST = [];
$_COOKIE = [];
$_FILES = [];
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/index.php';
```

---

## 3) First Feature by TDD: Minimal Router and Entry Point

- The Navigator proposed starting with routing to decouple HTTP endpoints from controllers. The Driver created `src/App/Core/Router.php` in the IDE and wrote a thin, testable router.

```1:80:src/App/Core/Router.php
<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function get($path, $callback) { $this->routes['GET'][$path] = $callback; }
    public function post($path, $callback) { $this->routes['POST'][$path] = $callback; }
    public function put($path, $callback) { $this->routes['PUT'][$path] = $callback; }
    public function delete($path, $callback) { $this->routes['DELETE'][$path] = $callback; }
    public function any($path, $callback) {
        $this->routes['GET'][$path] = $callback;
        $this->routes['POST'][$path] = $callback;
        $this->routes['PUT'][$path] = $callback;
        $this->routes['DELETE'][$path] = $callback;
    }

    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = rtrim($path, '/');
        if (empty($path)) { $path = '/'; }

        // Base-path stripping including parent of /public
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $scriptDir = dirname($scriptName);
        $parentDir = rtrim(dirname($scriptDir), '/');
        $candidates = array_unique(array_filter([
            $scriptDir,
            $parentDir && substr($scriptDir, -7) === '/public' ? $parentDir : null,
        ]));
        foreach ($candidates as $base) {
            if ($base !== '/' && $base !== '.' && strpos($path, $base) === 0) {
                $path = substr($path, strlen($base)) ?: '/';
                break;
            }
        }

        if (isset($this->routes[$method][$path])) {
            $this->executeCallback($this->routes[$method][$path]);
            return;
        }

        $matched = $this->findParameterizedRoute($method, $path);
        if ($matched) { $this->executeCallback($matched['callback'], $matched['params']); return; }

        $this->notFound();
    }

    // ... parameterized matching and helpers (see full file for details) ...
}
```

- To drive development, the pair added `tests/Unit/Core/RouterTest.php` (created via the IDE) covering basic registration, parameterized paths, base-path stripping, and 404 behavior. They ran tests:
```bash
./vendor/bin/phpunit -c phpunit.xml --testsuite Unit
```
- RED: failing due to missing parameterized matching and base-path normalization; GREEN: implemented `findParameterizedRoute`, `convertRouteToPattern`, and base-path stripping; REFACTOR: introduced `dispatch()` returning strings for easier assertions.

- The Driver created `public/index.php` via the IDE and added the initial route bindings and debug logs to help early manual tests.

```1:109:public/index.php
<?php

session_start();

require_once '../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\Auth\AuthController;
use App\Controllers\Admin\AdminController;
use App\Controllers\Faculty\FacultyController;

$router = new Router();

$authController = new AuthController();

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
error_log("Requested path: " . $currentPath);

$router->get('/', function() { header('Location: /login'); exit; });
$router->get('/login', function() use ($authController) { $authController->showLogin(); });
$router->post('/api/auth/login', function() use ($authController) { $authController->login(); });
$router->post('/api/auth/logout', function() use ($authController) { $authController->logout(); });

$router->get('/admin/dashboard', function() { (new AdminController())->dashboard(); });
$router->get('/admin/logout', function() { (new AdminController())->logout(); });
$router->get('/faculty/dashboard', function() { (new FacultyController())->dashboard(); });
$router->get('/faculty/logout', function() { (new FacultyController())->logout(); });

$router->get('/student-success', function() {
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    echo '<h1>Student Login Successful!</h1>';
    echo '<p>Welcome Student! You have successfully logged in.</p>';
    echo '<p><a href="' . $basePath . '/login">Back to Login</a></p>';
});

// Admin user management routes (POST)
$router->post('/admin/users/add', function() { (new AdminController())->addUser(); });
$router->post('/admin/users/add-student', function() { (new AdminController())->addStudent(); });
$router->post('/admin/users/edit-student', function() { (new AdminController())->editStudent(); });
$router->post('/admin/users/edit/{id}', function($id) { (new AdminController())->editUser($id); });
$router->post('/admin/users/delete-student', function() { (new AdminController())->deleteStudent(); });
$router->post('/admin/users/delete-faculty', function() { (new AdminController())->deleteFaculty(); });
$router->post('/admin/users/add-faculty', function() { (new AdminController())->addFaculty(); });
$router->post('/admin/users/delete/{id}', function($id) { (new AdminController())->deleteUser($id); });

$router->handleRequest();
?>
```

---

## 4) Auth Domain via TDD: Model, DAO, Service

- The Navigator recommended writing unit tests for authentication behavior first. In the IDE, the Driver added `tests/Unit/Auth/AuthServiceTest.php` to specify:
  - Login rejects empty credentials
  - Non‑existent user returns a clear message
  - Password verification uses the model
  - Session fields are set on success
  - `requireAuth()` and `requireRole()` redirect appropriately

- RED: tests failed with missing model and DAO. The Driver created the folders via the IDE explorer:
  - `src/App/Models/` with `User.php`
  - `src/App/DAO/Auth/` with `UserDAO.php`
  - `src/App/Services/Auth/` with `AuthService.php`

- Minimal GREEN implementation for `AuthService` to satisfy tests:

```1:120:src/App/Services/Auth/AuthService.php
<?php

namespace App\Services\Auth;

use App\DAO\Auth\UserDAO;
use App\Models\User;
use App\Services\User\UserService;

class AuthService
{
    private $userDAO;
    private $userService;

    public function __construct(UserDAO $userDAO = null, UserService $userService = null)
    {
        $this->userDAO = $userDAO ?? new UserDAO();
        $this->userService = $userService ?? new UserService();
    }

    public function login($school_id, $password)
    {
        if (empty(trim($school_id)) || empty(trim($password))) {
            return ['success' => false, 'message' => 'School ID and password are required.'];
        }
        $school_id = trim($school_id); $password = trim($password);
        $user = $this->userDAO->authenticate($school_id, $password);
        if (!$user) { return ['success' => false, 'message' => 'User not found.']; }
        if (!$user->verifyPassword($password)) { return ['success' => false, 'message' => 'Invalid School ID or password.']; }
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $_SESSION['user_id'] = $user->getUserId();
        $_SESSION['school_id'] = $user->getSchoolId();
        $_SESSION['full_name'] = $user->getFullName();
        $_SESSION['role'] = $user->getRole();
        $_SESSION['year_level'] = $user->getYearLevel();
        $_SESSION['section'] = $user->getSection();
        return ['success' => true, 'message' => 'Login successful!', 'user' => [
            'user_id' => $user->getUserId(), 'school_id' => $user->getSchoolId(),
            'full_name' => $user->getFullName(), 'role' => $user->getRole(),
            'year_level' => $user->getYearLevel(), 'section' => $user->getSection()
        ]];
    }

    public function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            header('Location: ' . $basePath . '/login');
            exit;
        }
        return ['success' => true, 'message' => 'User is authenticated.'];
    }

    public function requireRole($requiredRole)
    {
        $authResult = $this->requireAuth();
        $user = $this->getCurrentUser();
        if (!$user || $user['role'] !== $requiredRole) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            header('Location: ' . $basePath . '/login');
            exit;
        }
        return ['success' => true, 'message' => 'User has required role.'];
    }

    public function isAuthenticated()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    public function getCurrentUser()
    {
        if (!$this->isAuthenticated()) { return null; }
        return [
            'user_id' => $_SESSION['user_id'], 'school_id' => $_SESSION['school_id'],
            'full_name' => $_SESSION['full_name'], 'role' => $_SESSION['role'],
            'year_level' => $_SESSION['year_level'] ?? null, 'section' => $_SESSION['section'] ?? null
        ];
    }

    // ... create/update user flows and logout/changePassword implemented as tests demanded ...
}
```

- REFACTOR: The pair extracted password hashing/verification into the `User` model and validation into `UserService`, keeping `AuthService` focused on flow and session state.

---

## 5) Admin Experience via TDD: Controller and Views

- The Navigator wanted authorization gates at controller construction. A new unit test `tests/Unit/Admin/AdminControllerTest.php` asserted that `__construct()` enforces `requireAuth()` and `requireRole('admin')`, `dashboard()` renders user lists, and mutations redirect to the dashboard with session flashes.

- RED: No controller or view layer existed. The Driver created in the IDE:
  - `src/App/Core/View.php` for simple template rendering
  - `src/App/Controllers/Admin/AdminController.php`
  - `src/App/Views/admin/dashboard.php`, `src/App/Views/admin/manage-users.php`

- GREEN: Implemented `AdminController` minimal behaviors to satisfy tests.

```1:75:src/App/Controllers/Admin/AdminController.php
<?php

namespace App\Controllers\Admin;

use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use App\Core\View;

class AdminController
{
    private $authService;
    private $userService;
    private $view;

    public function __construct(
        AuthService $authService = null,
        UserService $userService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->userService = $userService ?? new UserService();
        $this->view = $view ?? new View();
        $this->authService->requireAuth();
        $this->authService->requireRole('admin');
    }

    public function dashboard()
    {
        $currentUser = $this->authService->getCurrentUser();
        $students = $this->userService->getUsersByRole('student');
        $faculty = $this->userService->getUsersByRole('faculty');
        $this->view->display('admin.dashboard', [
            'admin' => $currentUser,
            'students' => $this->userService->usersToArray($students),
            'faculty' => $this->userService->usersToArray($faculty),
            'yearSections' => $this->getYearSections($this->userService->usersToArray($students))
        ]);
    }

    // ... POST handlers add/edit/delete, JSON responses, and redirect helpers ...
}
```

- REFACTOR: Consolidated redirects into a helper and converted `User` lists to arrays at the controller boundary to keep views simple.

---

## 6) Data Access and Domain Model via TDD

- Tests in `tests/Unit/DAO/UserDAOTest.php` and `tests/Unit/Models/UserTest.php` drove the DAO and model design:
  - DAO uses a database abstraction (PDO via a `Database` config singleton) and returns `User` models.
  - `User` supports hydration, serialization, hashing, verification, and validation.
- The Driver created, via the IDE:
  - `src/App/Config/Database.php` and `src/App/Config/App.php`
  - `src/App/Interfaces/UserDAOInterface.php`, `src/App/Interfaces/UserServiceInterface.php`
  - `src/App/DAO/Auth/UserDAO.php`
  - `src/App/Models/User.php`
  - `src/App/Services/User/UserService.php`

- RED: Failure on missing interfaces and model behavior; GREEN: implemented minimal methods to satisfy tests; REFACTOR: improved validation ergonomics and added convenience mappers like `usersToArray()`.

---

## 7) Views and Additional Controllers

- The Navigator encouraged building thin controllers for role‑specific dashboards.
- The Driver added via the IDE:
  - `src/App/Controllers/Auth/AuthController.php`
  - `src/App/Controllers/Faculty/FacultyController.php`
  - `src/App/Views/auth/login.php`
  - `src/App/Views/faculty/dashboard.php`
- Integration tests under `tests/Integration/Controllers` validated wiring through `public/index.php` with the `Router`.

---

## 8) TDD Red–Green–Refactor Cycles Summarized

- Router
  - RED: path normalization and parameter support missing
  - GREEN: implemented `dispatch()` and regex conversion
  - REFACTOR: shared path normalization and debug logging
- Auth
  - RED: user retrieval and session handling undefined
  - GREEN: `AuthService::login/requireAuth/requireRole/logout`
  - REFACTOR: centralized hashing/validation in `User` and `UserService`
- Admin
  - RED: role enforcement and rendering missing
  - GREEN: `AdminController::__construct/dashboard` and POST flows
  - REFACTOR: redirect helpers and array mapping for views
- DAO/Model
  - RED: persistence mapping and validation absent
  - GREEN: DAO CRUD and `User` hydration/verify/hash
  - REFACTOR: error handling and typed interfaces

For a mapped view of tests to implementation, see `tests/Unit/README.RGR.md` which the pair kept updated during development.

```1:196:tests/Unit/README.RGR.md
# Unit Tests TDD: Red-Green-Refactor (RGR)
...
- Core/RouterTest.php expectations about normalization and 404s
- Auth/AuthServiceTest.php login and guards
- Admin/AdminControllerTest.php construction and dashboard rendering
- DAO/UserDAOTest.php model mapping and CRUD
- Models/UserTest.php hashing/validation
```

---

## 9) Debug Sessions and Fixes That Occurred

- Header already sent during tests
  - Symptom: tests intermittently failed on redirects
  - Fix: `tests/bootstrap.php` starts output buffering and disables cookie‑based sessions in test env
- Router base path when served from `/public`
  - Symptom: routes didn’t match when deployed under a subdirectory
  - Fix: added parent directory stripping when script dir ends with `/public`
- Session state not initialized
  - Symptom: `isAuthenticated()` returned false unexpectedly
  - Fix: ensure `session_start()` in `AuthService::isAuthenticated()` when needed
- View coupling to models
  - Symptom: views expected arrays; tests mocked arrays
  - Fix: introduced `usersToArray()` in `UserService` and converted at controller layer

---

## 10) Final Integration and Test Suite

- The Driver ran the complete suite in the IDE terminal:
```bash
./vendor/bin/phpunit -c phpunit.xml
```
- All unit and integration tests passed locally. Coverage was collected for the `src` directory as configured.

---

## 11) Appendix: Final File Overview

- Entrypoint: `public/index.php`
- Core: `src/App/Core/Router.php`, `src/App/Core/View.php`
- Config: `src/App/Config/App.php`, `src/App/Config/Database.php`
- Controllers: `src/App/Controllers/Auth/*`, `src/App/Controllers/Admin/*`, `src/App/Controllers/Faculty/*`
- Services: `src/App/Services/Auth/AuthService.php`, `src/App/Services/User/UserService.php`
- DAO: `src/App/DAO/Auth/UserDAO.php`
- Models: `src/App/Models/User.php`
- Interfaces: `src/App/Interfaces/*`
- Views: `src/App/Views/auth/*`, `src/App/Views/admin/*`, `src/App/Views/faculty/*`
- Tests: `tests/bootstrap.php`, `tests/Unit/*`, `tests/Integration/*`

This TTD captures the incremental, IDE‑first development flow and how tests continuously guided scope and design decisions from empty workspace to the current, working system.

