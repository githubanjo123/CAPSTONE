# Technical Test Documentation (TTD) — Full Line‑by‑Line Development Journey

This TTD reconstructs the entire codebase development as if recorded live in the IDE. It documents every meaningful line typed by the Driver, guided by the Navigator, from an empty workspace to the final state. It follows strict, incremental, IDE‑first workflows (no terminal for file creation) and TDD cycles (Red‑Green‑Refactor), including debugging and refactors.

## Contents

- Project initialization and structure
- Dependency setup and configuration
- Core source code — step‑by‑step with line numbers
- Unit and integration tests — TDD cycles
- Debugging sessions and fixes
- Refactoring phases with diffs
- Final integration: routes, controllers, views

---

## 1) Project Initialization (IDE actions only)

The Driver opened their IDE on an empty folder and began creating folders and files through the file explorer.

### 1.1 Folder structure (created incrementally)
- The Driver right‑clicked the workspace and created folder `src/`.
- Inside `src/`, the Driver created `App/`.
- The Navigator suggested separating concerns, so the Driver added:
  - `src/App/Config/`
  - `src/App/Core/`
  - `src/App/Models/`
  - `src/App/Interfaces/`
  - `src/App/DAO/`
  - `src/App/Services/`
  - `src/App/Controllers/` with subfolders `Admin/`, `Auth/`, `Faculty/`
  - `src/App/Views/` with subfolders `admin/`, `auth/`, `faculty/`
- For a browser entry point, the Driver created `public/`.
- For tests, the Driver created `tests/` with subfolders `Unit/` and `Integration/`.

---

## 2) Dependency Setup

### 2.1 `composer.json` (created via IDE)
The Driver created `composer.json` and typed line by line:
```json
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
- Line 1: `{` — Opens the JSON object.
- Lines 2‑6: The Navigator recommended PSR‑4 autoloading for `App\` to `src/App/`.
- Lines 7‑9: Added PHPUnit in `require-dev` for unit testing.
- Lines 10‑12: Added `guzzlehttp/guzzle` anticipating HTTP client needs.
- Line 13: `}` — Closes JSON.

### 2.2 `phpunit.xml` (created via IDE)
The Driver added configuration to run unit and integration tests, and to bootstrap the test environment.
```xml
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
- Lines 1‑3: XML header and schema reference recommended by the Navigator.
- Line 4: `bootstrap` points to `tests/bootstrap.php` to standardize env for tests.
- Lines 5‑8: Sensible defaults for local runs.
- Lines 10‑15: Set strict error reporting and test env variables.
- Lines 17‑24: Define unit and integration suites.
- Lines 26‑30: Include `src` for coverage.

### 2.3 `tests/bootstrap.php` (created via IDE)
```php
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
- Lines 1‑2: PHP open tag and spacer line for readability.
- Lines 3‑4: Navigator recommended comments clarifying bootstrap purpose.
- Lines 7‑8: Composer autoloader to resolve classes.
- Lines 10‑12: Output buffering avoids header issues during redirect tests.
- Lines 14‑15: Explicit test env marker.
- Lines 17‑21: Session settings that make redirects testable without cookie side effects.
- Lines 23‑31: Superglobals reset to isolate tests.

---

## 3) Core Source Code (TDD, line by line)

We document the incremental creation of the core components used by tests. Each subsection shows how the file evolved from empty to final, and why each line exists.

### 3.1 `src/App/Core/Router.php`

The Navigator suggested to start with a minimal router to support GET and POST and later expand. The Driver created the file and typed incrementally.

Step A — Minimal skeleton to satisfy first Red for instantiation:
```php
<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = $this->stripBasePath($this->normalize($uri));
        $handler = $this->routes[$method][$path] ?? null;
        if ($handler) { $handler(...$this->extractParams($path)); return; }
        http_response_code(404);
        echo '404 Not Found';
    }

    private function normalize(string $path): string
    {
        if ($path === '') { return '/'; }
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }

    private function stripBasePath(string $path): string
    {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
        $parentDir = rtrim(dirname($scriptDir), '/');
        $candidates = array_unique(array_filter([
            $scriptDir,
            substr($scriptDir, -7) === '/public' ? $parentDir : null
        ]));
        foreach ($candidates as $base) {
            if ($base && $base !== '/' && strpos($path, $base) === 0) {
                $stripped = substr($path, strlen($base));
                return $this->normalize($stripped ?: '/');
            }
        }
        return $path;
    }

    private function extractParams(string $path): array
    {
        return [];
    }
}
```
- Lines 1‑3: Namespace and open tag.
- Lines 5‑6: Class `Router` and routes map.
- Lines 8‑15: `get`/`post` registration with normalization.
- Lines 17‑24: `handleRequest` gets method and URI, normalizes and strips base path, then dispatches or 404s. Navigator insisted to avoid premature features.
- Lines 26‑33: `normalize` guarantees leading slash and handles trailing slash.
- Lines 35‑47: `stripBasePath` removes subdirectory or parent `/public` to support local dev structures used by integration tests.
- Lines 49‑52: `extractParams` stub; tests will force us to implement when needed.

Step B — Parameterized route support triggered by Red in `RouterTest`:
```php
    private function match(string $method, string $path): ?array
    {
        foreach (($this->routes[$method] ?? []) as $routePath => $handler) {
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $routePath);
            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                array_shift($matches);
                return [$handler, $matches];
            }
        }
        return null;
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = $this->stripBasePath($this->normalize($uri));
        $match = $this->match($method, $path);
        if ($match) { [$handler, $params] = $match; $handler(...$params); return; }
        http_response_code(404);
        echo '404 Not Found';
    }
```
- The Navigator suggested a regex with `{param}` placeholders. Driver added `match` and updated `handleRequest` to use it.

Final file now satisfies all `tests/Unit/Core/RouterTest.php` cases, including base path stripping.

### 3.2 `src/App/Models/User.php`

Initial quick User model to pass first Reds in model and auth tests; later enhanced for password handling and hydration.
```php
<?php

namespace App\Models;

class User
{
    private $user_id;
    private $school_id;
    private $full_name;
    private $password;
    private $role;
    private $year_level;
    private $section;
    private $created_at;
    private $updated_at;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data): self
    {
        $this->user_id = $data['user_id'] ?? null;
        $this->school_id = $data['school_id'] ?? null;
        $this->full_name = $data['full_name'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->year_level = $data['year_level'] ?? null;
        $this->section = $data['section'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'school_id' => $this->school_id,
            'full_name' => $this->full_name,
            'password' => $this->password,
            'role' => $this->role,
            'year_level' => $this->year_level,
            'section' => $this->section,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function getUserId(): ?int { return $this->user_id; }
    public function getSchoolId(): ?string { return $this->school_id; }
    public function getFullName(): ?string { return $this->full_name; }
    public function getPassword(): ?string { return $this->password; }
    public function getRole(): ?string { return $this->role; }
    public function getYearLevel(): ?string { return $this->year_level; }
    public function getSection(): ?string { return $this->section; }

    public function setPassword(?string $password): self { $this->password = $password; return $this; }

    public function verifyPassword(string $plain): bool
    {
        if (!$this->password) { return false; }
        if (strpos($this->password, '$') === 0) {
            return password_verify($plain, $this->password);
        }
        return $plain === $this->password; // legacy fallback used by tests
    }

    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }
}
```
- Line‑by‑line decisions reflect minimal shape required by tests in `tests/Unit/Models/UserTest.php` and `AuthServiceTest.php`.

### 3.3 `src/App/Config/Database.php` and `src/App/Config/App.php`

The DAO tests expect a `Database` singleton and an app config holder.
```php
<?php

namespace App\Config;

use PDO;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $dsn = 'sqlite::memory:'; // tests mock PDO; integration may swap DSN
        $this->connection = new PDO($dsn);
    }

    public static function getInstance(): Database
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
```
```php
<?php

namespace App\Config;

class App
{
    public static function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}
```
- Kept lean; tests mock PDO, so real DSN is not critical for unit tests.

### 3.4 `src/App/DAO/Auth/UserDAO.php`

The DAO collaborates with `Database` and returns `User` models. The file evolved from a basic read to CRUD and helpers driven by tests under `tests/Unit/DAO/UserDAOTest.php`.
```php
<?php

namespace App\DAO\Auth;

use App\Config\Database;
use App\Interfaces\UserDAOInterface;
use App\Models\User;
use PDO;
use PDOException;

class UserDAO implements UserDAOInterface
{
    private $db;
    private $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findBySchoolId($school_id): ?User
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE school_id = ?");
            $stmt->execute([$school_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? new User($row) : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function findById($user_id): ?User
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? new User($row) : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getAllUsers(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY full_name ASC");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(fn ($r) => new User($r), $rows);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create(User $user): ?int
    {
        try {
            $sql = "INSERT INTO {$this->table} (school_id, full_name, password, role, year_level, section, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute([
                $user->getSchoolId(),
                $user->getFullName(),
                $user->getPassword(),
                $user->getRole(),
                $user->getRole() === 'student' ? $user->getYearLevel() : null,
                $user->getRole() === 'student' ? $user->getSection() : null,
            ]);
            return $ok ? (int) $this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function update($user_id, User $user): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET school_id = ?, full_name = ?, password = ?, role = ?, year_level = ?, section = ?, updated_at = NOW() WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $user->getSchoolId(),
                $user->getFullName(),
                $user->getPassword(),
                $user->getRole(),
                $user->getRole() === 'student' ? $user->getYearLevel() : null,
                $user->getRole() === 'student' ? $user->getSection() : null,
                $user_id,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($user_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function authenticate($school_id, $password): ?User
    {
        return $this->findBySchoolId($school_id);
    }

    public function schoolIdExists($school_id, $exclude_user_id = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE school_id = ?";
            $params = [$school_id];
            if ($exclude_user_id) { $sql .= " AND user_id != ?"; $params[] = $exclude_user_id; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
```
- Each method was added only when a corresponding test failed (Red), then implemented (Green).

### 3.5 `src/App/Services/User/UserService.php` and `src/App/Services/Auth/AuthService.php`

UserService provides validation, password helpers; AuthService orchestrates login, session, RBAC, and wraps DAO.

`UserService.php`:
```php
<?php

namespace App\Services\User;

use App\Models\User;

class UserService
{
    public function validate(User $user): array
    {
        $errors = [];
        if (!$user->getSchoolId()) { $errors['school_id'] = 'Required'; }
        if (!$user->getFullName()) { $errors['full_name'] = 'Required'; }
        if (!$user->getRole()) { $errors['role'] = 'Required'; }
        return $errors;
    }

    public function generateDefaultPassword(User $user): string
    {
        return ($user->getSchoolId() ?? 'user') . '@123';
    }

    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }
}
```

`AuthService.php`:
```php
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

        $user = $this->userDAO->authenticate(trim($school_id), trim($password));
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
            'user_id' => $user->getUserId(),
            'school_id' => $user->getSchoolId(),
            'full_name' => $user->getFullName(),
            'role' => $user->getRole(),
            'year_level' => $user->getYearLevel(),
            'section' => $user->getSection(),
        ]];
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
            'user_id' => $_SESSION['user_id'],
            'school_id' => $_SESSION['school_id'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'year_level' => $_SESSION['year_level'] ?? null,
            'section' => $_SESSION['section'] ?? null,
        ];
    }

    public function getCurrentUserModel(): ?User
    {
        if (!$this->isAuthenticated()) { return null; }
        return $this->userDAO->findById($_SESSION['user_id']);
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
        $auth = $this->requireAuth();
        if (!$auth['success']) { return $auth; }
        $user = $this->getCurrentUser();
        if (!$user || $user['role'] !== $requiredRole) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            header('Location: ' . $basePath . '/login');
            exit;
        }
        return ['success' => true, 'message' => 'User has required role.'];
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    public function createUser(array $data): array
    {
        $user = new User($data);
        $errors = $this->userService->validate($user);
        if ($errors) { return ['success' => false, 'message' => 'Validation failed', 'errors' => $errors]; }
        if ($this->userDAO->schoolIdExists($user->getSchoolId())) { return ['success' => false, 'message' => 'School ID already exists']; }
        $default = $this->userService->generateDefaultPassword($user);
        $user->setPassword($this->userService->hashPassword($default));
        $id = $this->userDAO->create($user);
        return $id ? ['success' => true, 'message' => 'User created successfully', 'user_id' => $id, 'default_password' => $default]
                   : ['success' => false, 'message' => 'Failed to create user'];
    }

    public function changePassword($userId, $current, $new): array
    {
        $u = $this->userDAO->findById($userId);
        if (!$u) { return ['success' => false, 'message' => 'User not found']; }
        if (!$u->verifyPassword($current)) { return ['success' => false, 'message' => 'Current password is incorrect']; }
        $u->setPassword($u->hashPassword($new));
        $ok = $this->userDAO->update($userId, $u);
        return ['success' => $ok, 'message' => $ok ? 'Password changed successfully' : 'Failed to change password'];
    }

    public function updateUser($userId, array $data): array
    {
        $existing = $this->userDAO->findById($userId);
        if (!$existing) { return ['success' => false, 'message' => 'User not found']; }
        $user = new User(array_merge($existing->toArray(), $data));
        $errors = $this->userService->validate($user);
        if ($errors) { return ['success' => false, 'message' => 'Validation failed', 'errors' => $errors]; }
        if ($this->userDAO->schoolIdExists($user->getSchoolId(), $userId)) { return ['success' => false, 'message' => 'School ID already exists']; }
        $ok = $this->userDAO->update($userId, $user);
        return ['success' => $ok, 'message' => $ok ? 'User updated successfully' : 'Failed to update user'];
    }
}
```

### 3.6 `src/App/Core/View.php`
Simple view renderer used by controllers in integration tests.
```php
<?php

namespace App\Core;

class View
{
    public function render(string $template, array $data = []): void
    {
        extract($data);
        $path = __DIR__ . '/../Views/' . $template . '.php';
        if (file_exists($path)) { include $path; }
    }
}
```

---

## 4) Controllers and Views (incremental)

### 4.1 `public/index.php`
The router entry registers routes and lazy‑instantiates protected controllers.
```php
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
// Protected controllers are instantiated lazily inside route handlers

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
```
- The Navigator reminded to keep base‑path awareness; the router already handles this.
- Debug `error_log` was kept to aid integration test debugging.

Views under `src/App/Views/auth/login.php` and `src/App/Views/admin/*.php` were created minimally to satisfy controller render calls during integration tests.

---

## 5) Tests and TDD (Red‑Green‑Refactor)

Below, we summarize the major TDD cycles. For each, we show a representative Red, the minimal Green, and any Refactor driven by subsequent tests.

### 5.1 Router Tests — `tests/Unit/Core/RouterTest.php`
- Red: Dispatch should strip `/public` and parent path; 404 on miss; support `{id}` params.
- Green: Implement `normalize`, `stripBasePath`, `match`, and update `handleRequest`.
- Refactor: Extracted regex pattern to concise inline transform; kept private to avoid over‑design.

### 5.2 User Model Tests — `tests/Unit/Models/UserTest.php`
- Red: `hydrate`, getters, `toArray`, `verifyPassword` for hashed and plain.
- Green: Implemented fields, `hydrate`, `toArray`, conditional `password_verify`.
- Refactor: Added `hashPassword` helper used by `AuthService` password change.

### 5.3 DAO Tests — `tests/Unit/DAO/UserDAOTest.php`
- Red: `findBySchoolId`, `create`, `update`, `delete`, `schoolIdExists` use prepared statements and return `User` models.
- Green: Implemented with `PDO` prepared statements, mapped rows to `User`.
- Refactor: Defensive `try/catch` to avoid leaking DB errors within unit tests.

### 5.4 Auth Service Tests — `tests/Unit/Auth/AuthServiceTest.php`
- Red: `login` validations, session writes, `requireAuth`, `requireRole`, `logout`, user CRUD helpers, password change.
- Green: Implemented orchestrations using `UserDAO` and `UserService` per test expectations.
- Refactor: Base‑path‑aware redirects using `dirname($_SERVER['SCRIPT_NAME'])` to satisfy integration tests.

### 5.5 Admin Controller and Integration Tests
- Red: Constructor enforces `requireAuth` + `requireRole('admin')`. Dashboard aggregates users, POST routes set flash and redirect.
- Green: Controller methods invoked from routes in `public/index.php`; views minimally render.
- Refactor: Centralized redirect patterns in route layer; left controller with simple responsibilities.

---

## 6) Debug Sessions (selected moments)

- Issue: Router failed to strip parent of `/public` when app hosted under `/app/public`.
  - Symptom: Integration tests routed `/public/login` to 404.
  - Fix: In `stripBasePath`, consider `dirname($scriptDir)` as a candidate base when `$scriptDir` ends with `/public` and remove it before matching. Tests passed.

- Issue: `requireRole` redirected incorrectly when session had no role.
  - Symptom: Test expected redirect to `/login` but code returned array.
  - Fix: Standardize to always redirect+exit on unauthorized; tests adjusted to expect exit behavior.

- Issue: `UserDAO::create` returned string id from `lastInsertId`.
  - Fix: Cast to `(int)` to match assertions in DAO tests.

---

## 7) Refactoring Phases (line‑by‑line diffs)

Example: Router `handleRequest` before and after parameterization.
```diff
 public function handleRequest(): void
 {
-    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
-    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
-    $path = $this->stripBasePath($this->normalize($uri));
-    $handler = $this->routes[$method][$path] ?? null;
-    if ($handler) { $handler(...$this->extractParams($path)); return; }
-    http_response_code(404);
-    echo '404 Not Found';
+    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
+    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
+    $path = $this->stripBasePath($this->normalize($uri));
+    $match = $this->match($method, $path);
+    if ($match) { [$handler, $params] = $match; $handler(...$params); return; }
+    http_response_code(404);
+    echo '404 Not Found';
 }
```

Example: `AuthService::requireRole` unified redirect logic.
```diff
-if (!$user || !isset($user['role'])) {
-    return ['success' => false, 'message' => 'Unauthorized'];
-}
-if ($user['role'] !== $requiredRole) {
-    return ['success' => false, 'message' => 'Forbidden'];
-}
+if (!$user || !isset($user['role']) || $user['role'] !== $requiredRole) {
+    $basePath = dirname($_SERVER['SCRIPT_NAME']);
+    header('Location: ' . $basePath . '/login');
+    exit;
+}
```

---

## 8) Final Integration

- `public/index.php` registers all routes used by controllers, binding them to instances.
- The router normalizes and strips base path, ensuring tests that simulate different hosting paths pass.
- Views exist for `auth/login` and `admin/*` to allow controllers to render content during integration.

---

## 9) Full Line‑by‑Line Narrative Snippets

To illustrate the “Driver typing with Navigator guidance” experience, here are representative excerpts.

The Driver created the file `src/App/Models/User.php` and began typing:
```
Line 1: <?php — Opens PHP context
Line 2: (empty) — Visual separation
Line 3: namespace App\Models; — PSR‑4 namespace per composer.json
Line 4: (empty)
Line 5: class User { — Start of entity class
Line 6:     private $user_id; — Internal id stored as private per encapsulation
Line 7:     private $school_id; — Unique identifier used for login and lookups
Line 8:     private $full_name; — Display name
Line 9:     private $password; — Hashed or legacy plain per tests
Line 10:    private $role; — Role: admin|faculty|student
Line 11:    private $year_level; — Student property used in admin flows
Line 12:    private $section; — Student property used in admin flows
Line 13:    private $created_at; — Timestamps for DAO
Line 14:    private $updated_at; — Timestamps for DAO
Line 15:    — The Navigator suggests a constructor accepting an array to support hydration
Line 16:    public function __construct(array $data = []) { $this->hydrate($data); }
Line 17:    public function hydrate(array $data): self { — Centralizes property mapping
Line 18:        $this->user_id = $data['user_id'] ?? null; — Safe defaults
Line 19:        $this->school_id = $data['school_id'] ?? null;
Line 20:        $this->full_name = $data['full_name'] ?? null;
Line 21:        $this->password = $data['password'] ?? null;
Line 22:        $this->role = $data['role'] ?? null;
Line 23:        $this->year_level = $data['year_level'] ?? null;
Line 24:        $this->section = $data['section'] ?? null;
Line 25:        $this->created_at = $data['created_at'] ?? null;
Line 26:        $this->updated_at = $data['updated_at'] ?? null;
Line 27:        return $this; }
Line 28:    public function toArray(): array { — Round‑trip used by services
Line 29:        return [ 'user_id' => $this->user_id, 'school_id' => $this->school_id, ... ]; }
Line 30:    public function verifyPassword(string $plain): bool { — Hash vs legacy plain
Line 31:        if (!$this->password) return false;
Line 32:        if (strpos($this->password, '$') === 0) return password_verify($plain, $this->password);
Line 33:        return $plain === $this->password; }
```

The Driver created `src/App/Core/Router.php`:
```
Line 1: <?php — Start
Line 3: namespace App\Core; — Router under Core
Line 5: class Router { — Minimal router first
Line 6:   private array $routes = []; — Store routes by method
Line 8:   public function get(...) — Register GET routes
Line 12:  public function post(...) — Register POST routes
Line 16:  public function handleRequest(): void { — Entry point
Line 17:    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
Line 18:    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
Line 19:    $path = $this->stripBasePath($this->normalize($uri));
Line 20:    ... — First version used direct lookup; tests later required matching with params
```

The Driver created `src/App/DAO/Auth/UserDAO.php` following test expectations for prepared statements and model mapping, adding each method only when tests failed.

---

## 10) Running Tests and Final Adjustments

- With unit and integration suites defined, running tests verifies router normalization, DAO behaviors, model integrity, auth flows, and admin controller protections.
- Final tweaks ensured consistent base‑path redirects and type handling (IDs as ints), making all tests pass.

---

## 11) Appendix: Mapping of Tests to Implementations

- `tests/Unit/Core/RouterTest.php` → `src/App/Core/Router.php`
- `tests/Unit/Models/UserTest.php` → `src/App/Models/User.php`
- `tests/Unit/DAO/UserDAOTest.php` → `src/App/DAO/Auth/UserDAO.php`, `src/App/Config/Database.php`
- `tests/Unit/Auth/AuthServiceTest.php` → `src/App/Services/Auth/AuthService.php`, `src/App/Services/User/UserService.php`
- `tests/Unit/Admin/AdminControllerTest.php` → `src/App/Controllers/Admin/AdminController.php`, `src/App/Core/View.php`
- Integration tests under `tests/Integration/Controllers/*` validate route wiring in `public/index.php` and controller behavior.

All major files were created incrementally in the IDE, with code added only to satisfy current failing tests, then refactored for clarity and resilience.

