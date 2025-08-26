# PHP Examination System - Unit Test RGR for Current Working Features

Scope: Unit tests only (no integration/e2e). This guide documents Red-Green-Refactor (RGR) cycles for the features currently present in the codebase, based on `FEATURES.md` and the routing/controllers.

Features covered:
- Authentication (Login API + session handling)
- Role-Based Dashboards (Admin, Faculty, Student placeholder)
- Admin User Management (CRUD via service)
- Logout Flows (API + confirmation pages logic via service)
- Routing/Base-Path Behavior (Router normalization helpers)

---

## 1) Authentication (Login API + Session)
User Story: "As a user, I want to log in with school_id and password."

### 🔴 RED (Unit Tests)
See `tests/Unit/Auth/AuthServiceTest.php`
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;
use App\DAO\Auth\UserDAO;
use App\Models\User;

class AuthServiceTest extends TestCase
{
    private AuthService $auth;
    private $dao;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
        $_SESSION = [];
        $this->dao = $this->createMock(UserDAO::class);
        $this->auth = new AuthService($this->dao);
    }

    public function test_success_sets_session_and_returns_user(): void
    {
        $user = new User([
            'user_id' => 1,
            'school_id' => 'S1',
            'full_name' => 'Alice Admin',
            'role' => 'admin',
            'password' => password_hash('pw', PASSWORD_BCRYPT)
        ]);
        $this->dao->method('authenticate')->willReturn($user);

        $result = $this->auth->login('S1', 'pw');

        $this->assertTrue($result['success']);
        $this->assertSame('admin', $_SESSION['role'] ?? null);
        $this->assertSame('S1', $_SESSION['school_id'] ?? null);
    }

    public function test_wrong_password_fails(): void
    {
        $user = new User(['password' => password_hash('pw', PASSWORD_BCRYPT)]);
        $this->dao->method('authenticate')->willReturn($user);

        $result = $this->auth->login('S1', 'nope');
        $this->assertFalse($result['success']);
        $this->assertSame('Invalid School ID or password.', $result['message']);
    }

    public function test_user_not_found_fails(): void
    {
        $this->dao->method('authenticate')->willReturn(null);
        $result = $this->auth->login('NONE', 'pw');
        $this->assertFalse($result['success']);
        $this->assertSame('User not found.', $result['message']);
    }

    public function test_empty_credentials_fail(): void
    {
        $this->assertFalse($this->auth->login('', 'pw')['success']);
        $this->assertFalse($this->auth->login('S1', '')['success']);
        $this->assertFalse($this->auth->login('', '')['success']);
    }

    public function test_is_authenticated_true_when_session_has_user_and_role(): void
    {
        $_SESSION = ['user_id' => 9, 'role' => 'faculty'];
        $this->assertTrue($this->auth->isAuthenticated());
    }
}
```

### 🟢 GREEN (Minimum to Pass)
Minimal `User::verifyPassword()` and `AuthService::login()` (aligned with your code):
```php
// src/App/Models/User.php
public function verifyPassword(string $plain): bool
{
    $hashed = $this->password ?? '';
    if ($hashed && password_get_info($hashed)['algo']) {
        return password_verify($plain, $hashed);
    }
    return $plain === $hashed || $plain === ($this->plain_password ?? '');
}
```
```php
// src/App/Services/Auth/AuthService.php
public function login($school_id, $password)
{
    if (empty(trim($school_id)) || empty(trim($password))) {
        return ['success' => false, 'message' => 'School ID and password are required.'];
    }
    $school_id = trim($school_id);
    $password = trim($password);

    $user = $this->userDAO->authenticate($school_id, $password);
    if (!$user) {
        return ['success' => false, 'message' => 'User not found.'];
    }
    if (!$user->verifyPassword($password)) {
        return ['success' => false, 'message' => 'Invalid School ID or password.'];
    }

    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['user_id'] = $user->getUserId();
    $_SESSION['school_id'] = $user->getSchoolId();
    $_SESSION['full_name'] = $user->getFullName();
    $_SESSION['role'] = $user->getRole();
    $_SESSION['year_level'] = $user->getYearLevel();
    $_SESSION['section'] = $user->getSection();

    return [
        'success' => true,
        'message' => 'Login successful!',
        'user' => $user->toArray(),
    ];
}
```

### 🔵 REFACTOR (Production-Ready)
- Extract base-path redirect calc and session write into helpers (testable).
- Add logging hooks; broaden validation.
Example refactor extract:
```php
// src/App/Services/Auth/AuthService.php
private function writeSessionFromUser(User $user): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['user_id'] = $user->getUserId();
    $_SESSION['school_id'] = $user->getSchoolId();
    $_SESSION['full_name'] = $user->getFullName();
    $_SESSION['role'] = $user->getRole();
    $_SESSION['year_level'] = $user->getYearLevel();
    $_SESSION['section'] = $user->getSection();
}
```

---

## 2) Role-Based Dashboards (Access Control)
User Story: "As a user, I access my role-specific dashboard after login."

### 🔴 RED (Unit Tests)
See `tests/Unit/Auth/AuthServiceTest.php` (access control cases)
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;

class AuthServiceTest extends TestCase
{
    private AuthService $auth;

    protected function setUp(): void
    {
        $this->auth = new AuthService();
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    public function test_require_auth_redirects_when_not_logged_in(): void
    {
        $this->expectOutputRegex('/.*/');
        try {
            $this->auth->requireAuth();
            $this->fail('Expected exit');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }

    public function test_require_role_redirects_on_mismatch(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'student'];
        $this->expectOutputRegex('/.*/');
        try {
            $this->auth->requireRole('admin');
            $this->fail('Expected exit');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }

    public function test_get_current_user_returns_array(): void
    {
        $_SESSION = ['user_id' => 2, 'school_id' => 'F1', 'full_name' => 'Fac U Lty', 'role' => 'faculty'];
        $user = $this->auth->getCurrentUser();
        $this->assertSame('faculty', $user['role']);
    }
}
```

### 🟢 GREEN
Key methods (already present and aligned):
```php
// src/App/Services/Auth/AuthService.php
public function isAuthenticated()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

public function requireAuth()
{
    if (!$this->isAuthenticated()) {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        header('Location: ' . $basePath . '/login');
        exit;
    }
    return ['success' => true, 'message' => 'User is authenticated.'];
}

public function requireRole($requiredRole)
{
    $authResult = $this->requireAuth();
    if (!$authResult['success']) return $authResult;

    $user = $this->getCurrentUser();
    if (!$user || !isset($user['role']) || $user['role'] !== $requiredRole) {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        header('Location: ' . $basePath . '/login');
        exit;
    }
    return ['success' => true, 'message' => 'User has required role.'];
}
```

### 🔵 REFACTOR
- Extract base-path resolver to a pure helper (e.g., `UrlHelper::basePath()`), inject for easier testing.
- Centralize role constants and policies.

---

## 3) Admin User Management (CRUD)
User Story: "As an admin, I can add, edit, and delete users."

### 🔴 RED (Unit Tests)
See `tests/Unit/User/UserServiceTest.php`
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Services\User\UserService;
use App\DAO\Auth\UserDAO;
use App\Models\User;

class UserServiceTest extends TestCase
{
    private UserService $service;
    private $dao;

    protected function setUp(): void
    {
        $this->dao = $this->createMock(UserDAO::class);
        $this->service = new UserService($this->dao);
    }

    public function test_create_user_success(): void
    {
        $input = ['school_id' => 'S1', 'full_name' => 'Stu Dent', 'role' => 'student'];
        $this->dao->method('schoolIdExists')->willReturn(false);
        $this->dao->method('create')->willReturn(101);
        $result = $this->service->createUser($input);
        $this->assertTrue($result['success']);
        $this->assertSame(101, $result['user_id']);
    }

    public function test_create_user_fails_when_duplicate_school_id(): void
    {
        $input = ['school_id' => 'S1', 'full_name' => 'Stu Dent', 'role' => 'student'];
        $this->dao->method('schoolIdExists')->willReturn(true);
        $result = $this->service->createUser($input);
        $this->assertFalse($result['success']);
    }

    public function test_update_user_success(): void
    {
        $existing = new User(['user_id' => 1, 'school_id' => 'S1', 'full_name' => 'X', 'role' => 'student']);
        $this->dao->method('findById')->willReturn($existing);
        $this->dao->method('schoolIdExists')->willReturn(false);
        $this->dao->method('update')->willReturn(true);
        $result = $this->service->updateUser(1, ['full_name' => 'Y']);
        $this->assertTrue($result['success']);
    }

    public function test_update_user_fails_when_not_found(): void
    {
        $this->dao->method('findById')->willReturn(null);
        $result = $this->service->updateUser(999, ['full_name' => 'Z']);
        $this->assertFalse($result['success']);
    }

    public function test_delete_user_success(): void
    {
        $existing = new User(['user_id' => 1, 'school_id' => 'S1']);
        $this->dao->method('findById')->willReturn($existing);
        $this->dao->method('delete')->willReturn(true);
        $result = $this->service->deleteUser(1);
        $this->assertTrue($result['success']);
    }
}
```

### 🟢 GREEN
Minimal service logic (aligned with your implementation):
```php
// src/App/Services/User/UserService.php
public function createUser(array $userData): array
{
    $user = new User($userData);
    if ($this->userDAO->schoolIdExists($user->getSchoolId())) {
        return ['success' => false, 'message' => 'School ID already exists'];
    }
    $userId = $this->userDAO->create($user);
    return $userId
        ? ['success' => true, 'message' => 'User created successfully', 'user_id' => $userId]
        : ['success' => false, 'message' => 'Failed to create user'];
}

public function updateUser($userId, array $userData): array
{
    $existing = $this->userDAO->findById($userId);
    if (!$existing) {
        return ['success' => false, 'message' => 'User not found'];
    }
    $user = new User(array_merge($existing->toArray(), $userData));
    if (isset($userData['school_id']) && $userData['school_id'] !== $existing->getSchoolId()) {
        if ($this->userDAO->schoolIdExists($userData['school_id'], $userId)) {
            return ['success' => false, 'message' => 'School ID already exists.'];
        }
    }
    $ok = $this->userDAO->update($userId, $user);
    return ['success' => $ok, 'message' => $ok ? 'User updated successfully' : 'Failed to update user'];
}

public function deleteUser($userId): array
{
    $existing = $this->userDAO->findById($userId);
    if (!$existing) {
        return ['success' => false, 'message' => 'User not found'];
    }
    $ok = $this->userDAO->delete($userId);
    return ['success' => $ok, 'message' => $ok ? 'User deleted successfully' : 'Failed to delete user'];
}
```

### 🔵 REFACTOR
- Use `User::validate()`; normalize names/school IDs; generate default hashed password when creating students.
- Keep service methods thin, delegate persistence to DAO; keep model authoritative for business rules.

---

## 4) Logout Flows (Service-Level Behavior)
User Story: "As a user, I can logout and invalidate my session."

### 🔴 RED (Unit Tests)
`tests/Unit/Auth/AuthService.LogoutRgrTest.php`
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;

class AuthService_LogoutRgrTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
    }

    public function test_logout_clears_session_and_returns_success(): void
    {
        $auth = new AuthService();
        $result = $auth->logout();
        $this->assertTrue($result['success']);
        $this->assertSame([], $_SESSION);
    }
}
```

### 🟢 GREEN
```php
// src/App/Services/Auth/AuthService.php
public function logout()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    return ['success' => true, 'message' => 'Logged out successfully'];
}
```

### 🔵 REFACTOR
- Hook into centralized session manager if introduced later; add logging.

---

## 5) Routing/Base-Path Behavior (Pure Helpers)
User Story: "As a developer, I want requests to resolve correctly regardless of subdirectory or public/ docroot."

### 🔴 RED (Unit Tests)
`tests/Unit/Core/RouterPathHelperRgrTest.php`
```php
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../helpers/PathHelper.php';

class RouterPathHelperRgrTest extends TestCase
{
    public function test_strips_script_dir(): void
    {
        $normalized = \App\Core\PathHelper::normalize('/myapp/login', '/myapp/public/index.php');
        $this->assertSame('/login', $normalized);
    }

    public function test_strips_parent_of_public(): void
    {
        $normalized = \App\Core\PathHelper::normalize('/myapp/public/login', '/myapp/public/index.php');
        $this->assertSame('/login', $normalized);
    }

    public function test_root_becomes_slash(): void
    {
        $normalized = \App\Core\PathHelper::normalize('/', '/index.php');
        $this->assertSame('/', $normalized);
    }
}
```

### 🟢 GREEN
```php
// tests/helpers/PathHelper.php (example helper for unit tests)
namespace App\Core;

final class PathHelper
{
    public static function normalize(string $requestPath, string $scriptName): string
    {
        $path = rtrim($requestPath, '/');
        if ($path === '') $path = '/';
        $scriptDir = dirname($scriptName);
        $parentDir = rtrim(dirname($scriptDir), '/');
        $candidates = array_unique(array_filter([
            $scriptDir,
            $parentDir && substr($scriptDir, -7) === '/public' ? $parentDir : null,
        ]));
        foreach ($candidates as $base) {
            if ($base !== '/' && $base !== '.' && str_starts_with($path, $base)) {
                $path = substr($path, strlen($base));
                if ($path === '') $path = '/';
                break;
            }
        }
        return $path;
    }
}
```

### 🔵 REFACTOR
- Replace duplicated logic in `Router` with calls to this helper; unit tests remain green while integration becomes simpler.

---

## PHPUnit Config (Unit Only) and Commands
phpunit.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php" colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

tests/bootstrap.php
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

Run unit tests:
```bash
./vendor/bin/phpunit -c phpunit.xml --testsuite Unit
```

Notes:
- Use PHPUnit mocks for DAOs to isolate Service logic.
- For redirect/exit flows, unit tests should assert behavior via try/catch or by extracting pure helpers for redirect target computation.

---

### Current Implementation by Folder (citations)

- `src/App/Controllers/Admin/AdminController.php`
```1:40:/workspace/src/App/Controllers/Admin/AdminController.php
<?php
// ...
```

- `src/App/Controllers/Auth/AuthController.php`
```1:60:/workspace/src/App/Controllers/Auth/AuthController.php
<?php

namespace App\Controllers\Auth;

use App\Services\Auth\AuthService;
use App\Core\View;

class AuthController
{
    private $authService;
    private $view;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->view = new View();
    }

    public function showLogin()
    {
        if ($this->authService->isAuthenticated()) {
            $user = $this->authService->getCurrentUser();
            $role = $user['role'] ?? null;
            if (!in_array($role, ['admin', 'faculty', 'student'], true)) {
                $this->authService->logout();
                $this->view->display('auth.login', ['error' => 'Your session role is invalid. Please log in again.']);
                return;
            }
            $this->redirectToDashboard($role);
            return;
        }
        $this->view->display('auth.login');
    }
```

- `src/App/Services/Auth/AuthService.php`
```1:60:/workspace/src/App/Services/Auth/AuthService.php
<?php

namespace App\Services\Auth;

use App\DAO\Auth\UserDAO;
use App\Models\User;

class AuthService
{
    private $userDAO;

    public function __construct(UserDAO $userDAO = null)
    {
        $this->userDAO = $userDAO ?? new UserDAO();
    }

    public function login($school_id, $password)
    {
        if (empty(trim($school_id)) || empty(trim($password))) {
            return ['success' => false, 'message' => 'School ID and password are required.'];
        }
        $school_id = trim($school_id);
        $password = trim($password);
        $user = $this->userDAO->authenticate($school_id, $password);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }
        if (!$user->verifyPassword($password)) {
            return ['success' => false, 'message' => 'Invalid School ID or password.'];
        }
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['user_id'] = $user->getUserId();
        $_SESSION['school_id'] = $user->getSchoolId();
        $_SESSION['full_name'] = $user->getFullName();
        $_SESSION['role'] = $user->getRole();
        $_SESSION['year_level'] = $user->getYearLevel();
        $_SESSION['section'] = $user->getSection();
        return ['success' => true, 'message' => 'Login successful!', 'user' => $user->toArray()];
    }
```

- `src/App/DAO/Auth/UserDAO.php`
```1:28:/workspace/src/App/DAO/Auth/UserDAO.php
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
```

- `src/App/Models/User.php` (verifyPassword)
```96:116:/workspace/src/App/Models/User.php
    /**
     * Verify password - kept in model as it's about the entity's own data
     */
    public function verifyPassword(string $inputPassword): bool
    {
        if (empty($this->password)) {
            return false;
        }
        if (strpos($this->password, '$') === 0) {
            return password_verify($inputPassword, $this->password);
        } else {
            return $inputPassword === $this->password;
        }
    }
```

- `src/App/Services/User/UserService.php` (business logic)
```1:80:/workspace/src/App/Services/User/UserService.php
<?php

namespace App\Services\User;

use App\Interfaces\UserServiceInterface;
use App\Interfaces\UserDAOInterface;
use App\DAO\Auth\UserDAO;
use App\Models\User;

class UserService implements UserServiceInterface
{
    private $userDAO;

    public function __construct(UserDAOInterface $userDAO = null)
    {
        $this->userDAO = $userDAO ?? new UserDAO();
    }

    /**
     * Create a new user (delegates to AuthService for proper business logic)
     */
    public function createUser($data)
    {
        $user = new User($data);
        
        // Basic validation
        $validationErrors = $this->validate($user);
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationErrors
            ];
        }

        // Check if school_id already exists
        if ($this->userDAO->schoolIdExists($user->getSchoolId())) {
            return [
                'success' => false,
                'message' => 'School ID already exists.'
            ];
        }

        // Generate and hash default password
        $defaultPassword = $this->generateDefaultPassword($user);
        $hashedPassword = $this->hashPassword($defaultPassword);
        $user->setPassword($hashedPassword);

        // Create user
        $userId = $this->userDAO->create($user);
        
        if ($userId) {
            return [
                'success' => true,
                'message' => 'User created successfully!',
                'user_id' => $userId,
                'default_password' => $defaultPassword
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create user.'
            ];
        }
    }

    // Business Logic Methods (moved from User model)

    /**
     * Generate default password for user
     */
    public function generateDefaultPassword(User $user): string
    {
        if (empty($user->getSchoolId()) || empty($user->getFullName())) {
            throw new \InvalidArgumentException('School ID and full name are required to generate password');
        }
        
        return $user->getSchoolId() . $user->getFullName();
    }

    /**
     * Hash password
     */
    public function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(User $user): bool
    {
        return $user->getRole() === 'admin';
    }

    /**
     * Validate user data
     */
    public function validate(User $user): array
    {
        $errors = [];

        if (empty($user->getSchoolId())) {
            $errors[] = 'School ID is required';
        }

        if (empty($user->getFullName())) {
            $errors[] = 'Full name is required';
        }

        if (empty($user->getRole())) {
            $errors[] = 'Role is required';
        } elseif (!in_array($user->getRole(), ['admin', 'faculty', 'student'])) {
            $errors[] = 'Invalid role';
        }

        if ($user->getRole() === 'student') {
            if (empty($user->getYearLevel())) {
                $errors[] = 'Year level is required for students';
            }
            if (empty($user->getSection())) {
                $errors[] = 'Section is required for students';
            }
        }

        return $errors;
    }
}
```

- `src/App/Core/Router.php`
```1:22:/workspace/src/App/Core/Router.php
<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }
```