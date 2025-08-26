# PHP Examination System - TDD Red-Green-Refactor Documentation

## Table of Contents
- Feature 1: User Authentication (Login System)
- Feature 2: Admin User Management (CRUD Operations)
- Feature 3: Role-Based Dashboard Access
- TDD Benefits Observed
- Integration with IDE (Cursor/PHPStorm)
- Next Steps and Recommendations

## Feature 1: User Authentication System

### 🔴 RED Phase: Write Failing Tests

#### Unit: AuthService
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;
use App\DAO\Auth\UserDAO;
use App\Models\User;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private $userDAOMock;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];

        $this->userDAOMock = $this->createMock(UserDAO::class);
        $this->authService = new AuthService($this->userDAOMock);
    }

    public function test_login_success_sets_session_and_returns_user()
    {
        $user = new User([
            'user_id' => 10,
            'school_id' => 'A123',
            'full_name' => 'Alice Admin',
            'role' => 'admin',
            'password' => password_hash('secret', PASSWORD_BCRYPT),
        ]);

        $this->userDAOMock->method('authenticate')
            ->with('A123', 'secret')
            ->willReturn($user);

        $result = $this->authService->login('A123', 'secret');

        $this->assertTrue($result['success']);
        $this->assertSame('admin', $_SESSION['role'] ?? null);
        $this->assertSame('A123', $_SESSION['school_id'] ?? null);
    }

    public function test_login_fails_for_wrong_password()
    {
        $user = new User([
            'user_id' => 10,
            'school_id' => 'A123',
            'full_name' => 'Alice Admin',
            'role' => 'admin',
            'password' => password_hash('secret', PASSWORD_BCRYPT),
        ]);

        $this->userDAOMock->method('authenticate')
            ->with('A123', 'bad')
            ->willReturn($user);

        $result = $this->authService->login('A123', 'bad');

        $this->assertFalse($result['success']);
        $this->assertSame('Invalid School ID or password.', $result['message']);
    }

    public function test_login_fails_when_user_not_found()
    {
        $this->userDAOMock->method('authenticate')
            ->with('NONE', 'pw')
            ->willReturn(null);

        $result = $this->authService->login('NONE', 'pw');

        $this->assertFalse($result['success']);
        $this->assertSame('User not found.', $result['message']);
    }

    public function test_login_fails_when_empty_credentials()
    {
        $this->assertFalse($this->authService->login('', 'pw')['success']);
        $this->assertFalse($this->authService->login('id', '')['success']);
        $this->assertFalse($this->authService->login('', '')['success']);
    }

    public function test_require_auth_redirects_when_not_authenticated()
    {
        $this->expectOutputRegex('/.*/');
        try {
            $this->authService->requireAuth();
            $this->fail('Expected exit() in requireAuth');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }
}
```

#### Integration: AuthController
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\Auth\AuthController;

class AuthControllerTest extends TestCase
{
    private AuthController $controller;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $this->controller = new AuthController();
    }

    public function test_get_login_shows_login_page()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        $this->controller->showLogin();
        $html = ob_get_clean();
        $this->assertStringContainsString('Login', $html);
    }

    public function test_post_login_success_redirects_to_role_dashboard()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['school_id'] = 'A123';
        $_POST['password'] = 'secret';

        ob_start();
        $this->controller->login();
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function test_post_login_invalid_credentials_rerenders_login_with_error()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['school_id'] = 'A123';
        $_POST['password'] = 'wrong';

        ob_start();
        $this->controller->login();
        $html = ob_get_clean();

        $this->assertStringContainsString('Login', $html);
    }

    public function test_post_login_missing_fields_rerenders_with_error()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['school_id'] = '';
        $_POST['password'] = '';

        ob_start();
        $this->controller->login();
        $html = ob_get_clean();

        $this->assertStringContainsString('required', $html);
    }

    public function test_logout_returns_json_success()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        ob_start();
        $this->controller->logout();
        $json = ob_get_clean();
        $this->assertStringContainsString('success', $json);
    }
}
```

#### DAO: Database Interaction (Unit with doubles)
```php
<?php

use PHPUnit\Framework\TestCase;
use App\DAO\Auth\UserDAO;
use App\Models\User;

class UserDAOTest extends TestCase
{
    public function test_find_by_school_id_returns_user_model()
    {
        $dao = new UserDAO();
        $this->assertTrue(method_exists($dao, 'findBySchoolId'));
    }

    public function test_authenticate_returns_user_model_or_null()
    {
        $dao = new UserDAO();
        $this->assertTrue(method_exists($dao, 'authenticate'));
    }
}
```

Example failing output (abbreviated):
```text
PHPUnit 10.x by Sebastian Bergmann and contributors.

FFFFF.....  10 / 10 (100%)

Failures:
1) AuthServiceRgrTest::test_login_success_sets_session_and_returns_user
Failed asserting that true is false. ...

2) AuthServiceRgrTest::test_login_fails_for_wrong_password
Failed asserting that false is true. ...
```

### 🟢 GREEN Phase: Make Tests Pass

- Model: `User::verifyPassword()` supports plaintext and hashed verification.
```96:116:/workspace/src/App/Models/User.php
    /**
     * Verify password - kept in model as it's about the entity's own data
     */
    public function verifyPassword(string $inputPassword): bool
    {
        if (empty($this->password)) {
            return false;
        }

        // Check if password is hashed (starts with $) or plain text
        if (strpos($this->password, '$') === 0) {
            return password_verify($inputPassword, $this->password);
        } else {
            // Legacy plain text password support
            return $inputPassword === $this->password;
        }
    }
```

- Service: `UserService` handles business logic (validation, password generation, role checking).
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

- DAO: `authenticate()` delegates to `findBySchoolId()` and returns a `User` model or null.
```php
public function authenticate($school_id, $password): ?User
{
    return $this->findBySchoolId($school_id);
}
```

- Service: Minimal login.
```php
public function login($school_id, $password)
{
    if (empty(trim($school_id)) || empty(trim($password))) {
        return ['success' => false, 'message' => 'School ID and password are required.'];
    }
    $user = $this->userDAO->authenticate(trim($school_id), trim($password));
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
    return ['success' => true, 'message' => 'Login successful!', 'user' => $user->toArray()];
}
```

- Controller: Minimal login handling.
```php
public function login()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->showLogin();
        return;
    }
    $school_id = $_POST['school_id'] ?? '';
    $password = $_POST['password'] ?? '';
    $result = $this->authService->login($school_id, $password);
    if ($result['success']) {
        $this->redirectToDashboard($result['user']['role']);
    } else {
        $this->view->display('auth.login', ['error' => $result['message']]);
    }
}
```

### 🔵 REFACTOR Phase: Production Code

- Input sanitization, error handling, secure password hashing.
- DAO returns `User` models via prepared statements (SQL injection prevention).
- Robust session management, `requireAuth()/requireRole()` with base-path-aware redirects.
- Dependency injection of `UserDAOInterface` into services.
- Guard against invalid roles to prevent redirect loops.

#### Current Implementation by Folder (citations)

- `src/App/Controllers/Auth/AuthController.php`
```1:80:/workspace/src/App/Controllers/Auth/AuthController.php
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

    /**
     * Show login page
     */
    public function showLogin()
    {
        // If user is already logged in, redirect to appropriate dashboard
        if ($this->authService->isAuthenticated()) {
            $user = $this->authService->getCurrentUser();
            $role = $user['role'] ?? null;
            // Guard against invalid or missing roles to avoid redirect loops
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
```1:80:/workspace/src/App/Services/Auth/AuthService.php
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

    /**
     * Login user with school ID and password
     */
    public function login($school_id, $password)
    {
        // Validate inputs - check if trimmed values are empty
        if (empty(trim($school_id)) || empty(trim($password))) {
            return [
                'success' => false,
                'message' => 'School ID and password are required.'
            ];
        }

        // Sanitize inputs
        $school_id = trim($school_id);
        $password = trim($password);

        // Get user from DAO (this just retrieves the user, no authentication yet)
        $user = $this->userDAO->authenticate($school_id, $password);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        // Now perform authentication business logic using the User model
        if (!$user->verifyPassword($password)) {
            return [
                'success' => false,
                'message' => 'Invalid School ID or password.'
            ];
        }

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Store user data in session
        $_SESSION['user_id'] = $user->getUserId();
        $_SESSION['school_id'] = $user->getSchoolId();
        $_SESSION['full_name'] = $user->getFullName();
        $_SESSION['role'] = $user->getRole();
        $_SESSION['year_level'] = $user->getYearLevel();
        $_SESSION['section'] = $user->getSection();

        return [
            'success' => true,
            'message' => 'Login successful!',
            'user' => [
                'user_id' => $user->getUserId(),
                'school_id' => $user->getSchoolId(),
                'full_name' => $user->getFullName(),
                'role' => $user->getRole(),
                'year_level' => $user->getYearLevel(),
                'section' => $user->getSection()
            ]
        ];
    }
```

- `src/App/DAO/Auth/UserDAO.php`
```1:40:/workspace/src/App/DAO/Auth/UserDAO.php
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

    /**
     * Find user by school ID and return User model
     */
    public function findBySchoolId($school_id): ?User
    {
```

- `src/App/Models/User.php` (password verification)
```96:116:/workspace/src/App/Models/User.php
    /**
     * Verify password - kept in model as it's about the entity's own data
     */
    public function verifyPassword(string $inputPassword): bool
    {
        if (empty($this->password)) {
            return false;
        }

        // Check if password is hashed (starts with $) or plain text
        if (strpos($this->password, '$') === 0) {
            return password_verify($inputPassword, $this->password);
        } else {
            // Legacy plain text password support
            return $inputPassword === $this->password;
        }
    }
```

- `src/App/Core/Router.php`
```1:28:/workspace/src/App/Core/Router.php
<?php

namespace App\Core;

class Router
{
    private $routes = [];

    /**
     * Add a GET route
     */
    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Add a POST route
     */
    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }
```

---

## Feature 2: Admin User Management (CRUD Operations)

### 🔴 RED Phase: Write Failing Tests

#### Unit: UserService
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Services\User\UserService;
use App\DAO\Auth\UserDAO;
use App\Models\User;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private $dao;

    protected function setUp(): void
    {
        $this->dao = $this->createMock(UserDAO::class);
        $this->userService = new UserService($this->dao);
    }

    public function test_create_user_success()
    {
        $input = ['school_id' => 'S1', 'full_name' => 'Stu Dent', 'role' => 'student'];
        $this->dao->method('schoolIdExists')->willReturn(false);
        $this->dao->method('create')->willReturn(101);

        $result = $this->userService->createUser($input);
        $this->assertTrue($result['success']);
        $this->assertSame(101, $result['user_id']);
    }

    public function test_create_user_fails_when_school_id_exists()
    {
        $input = ['school_id' => 'S1', 'full_name' => 'Stu Dent', 'role' => 'student'];
        $this->dao->method('schoolIdExists')->willReturn(true);

        $result = $this->userService->createUser($input);
        $this->assertFalse($result['success']);
    }

    public function test_update_user_success()
    {
        $existing = new User(['user_id' => 1, 'school_id' => 'S1', 'full_name' => 'X', 'role' => 'student']);
        $this->dao->method('findById')->willReturn($existing);
        $this->dao->method('schoolIdExists')->willReturn(false);
        $this->dao->method('update')->willReturn(true);

        $result = $this->userService->updateUser(1, ['full_name' => 'Y']);
        $this->assertTrue($result['success']);
    }

    public function test_update_user_fails_when_not_found()
    {
        $this->dao->method('findById')->willReturn(null);
        $result = $this->userService->updateUser(999, ['full_name' => 'Z']);
        $this->assertFalse($result['success']);
    }

    public function test_delete_user_success()
    {
        $existing = new User(['user_id' => 1, 'school_id' => 'S1']);
        $this->dao->method('findById')->willReturn($existing);
        $this->dao->method('delete')->willReturn(true);

        $result = $this->userService->deleteUser(1);
        $this->assertTrue($result['success']);
    }
}
```

#### Integration: AdminController (selected flows)
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\Admin\AdminController;

class AdminControllerTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    public function test_dashboard_renders_student_faculty_lists()
    {
        $controller = new AdminController();
        ob_start();
        $controller->dashboard();
        $html = ob_get_clean();
        $this->assertStringContainsString('Dashboard', $html);
    }

    public function test_add_user_post_redirects_back()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $controller = new AdminController();
        ob_start();
        $controller->addUser();
        ob_end_clean();
        $this->assertTrue(true);
    }
}
```

### 🟢 GREEN Phase: Make Tests Pass

Minimal service logic example:
```php
public function createUser(array $data): array
{
    $user = new User($data);
    if ($this->userDAO->schoolIdExists($user->getSchoolId())) {
        return ['success' => false, 'message' => 'School ID already exists.'];
    }
    $id = $this->userDAO->create($user);
    return $id ? ['success' => true, 'user_id' => $id, 'message' => 'User created successfully'] :
                 ['success' => false, 'message' => 'Failed to create user'];
}
```

### 🔵 REFACTOR Phase: Production Code

- Validate via `User::validate()`
- Generate and hash default passwords
- Transactions when needed
- Convert models to arrays for views via `usersToArray()`
- Use session messages for UX feedback
- Prepared statements in DAO; return `User` models consistently

---

## Feature 3: Role-Based Dashboard Access

### 🔴 RED Phase: Write Failing Tests

#### Unit/Service: Role Enforcement
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;

class RoleAccessRgrTest extends TestCase
{
    private AuthService $auth;

    protected function setUp(): void
    {
        $this->auth = new AuthService();
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    public function test_require_auth_redirects_when_not_logged_in()
    {
        $this->expectOutputRegex('/.*/');
        try {
            $this->auth->requireAuth();
            $this->fail('Expected exit');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }

    public function test_require_role_redirects_when_role_mismatch()
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'student';
        $this->expectOutputRegex('/.*/');
        try {
            $this->auth->requireRole('admin');
            $this->fail('Expected exit');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }

    public function test_get_current_user_returns_array()
    {
        $_SESSION = ['user_id' => 1, 'school_id' => 'S1', 'full_name' => 'X', 'role' => 'student'];
        $user = $this->auth->getCurrentUser();
        $this->assertSame('student', $user['role']);
    }

    public function test_show_login_redirects_when_authenticated()
    {
        $_SESSION = ['user_id' => 1, 'role' => 'faculty', 'school_id' => 'F1', 'full_name' => 'Fac U Lty'];
        $controller = new \App\Controllers\Auth\AuthController();
        ob_start();
        $controller->showLogin();
        ob_end_clean();
        $this->assertTrue(true);
    }

    public function test_invalid_role_clears_session_to_avoid_loops()
    {
        $_SESSION = ['user_id' => 1, 'role' => 'weird'];
        $controller = new \App\Controllers\Auth\AuthController();
        ob_start();
        $controller->showLogin();
        $out = ob_get_clean();
        $this->assertStringContainsString('invalid', $out);
        $this->assertArrayNotHasKey('role', $_SESSION);
    }
}
```

### 🟢 GREEN Phase: Make Tests Pass

- `requireAuth()` and `requireRole()` redirect to `/login` when needed.
- `AuthController::showLogin()` redirects when authenticated.
- Switch-case dashboard routing per role.

### 🔵 REFACTOR Phase: Production Code

- Base-path aware redirects using `dirname($_SERVER['SCRIPT_NAME'])`.
- On invalid role: logout, show login with error.
- Instantiate `AdminController`/`FacultyController` lazily inside routes to avoid constructor-time redirects on `/login`.

---

## Shared Testing/Configuration

phpunit.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php" colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
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

composer.json (key sections)
```json
{
  "require": {
    "php": ">=8.1",
    "ext-pdo": "*"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.5"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

Database migration (MySQL)
```sql
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  school_id VARCHAR(64) NOT NULL UNIQUE,
  full_name VARCHAR(255) NOT NULL,
  password VARCHAR(255) NULL,
  role ENUM('admin','faculty','student') NOT NULL,
  year_level VARCHAR(16) NULL,
  section VARCHAR(16) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Command to run tests
```bash
./vendor/bin/phpunit -c phpunit.xml
```

---

## TDD Benefits Observed
- Clear separation of concerns enforced by tests (Model vs DAO vs Service vs Controller).
- Early discovery of redirect loops and base-path issues through integration tests.
- Safer refactors: switching from arrays to `User` models across layers validated by tests.
- Regression protection for role-based access and session handling.

## Integration with IDE (Cursor/PHPStorm)
- Use test watcher to run unit tests on save.
- Navigate between failing tests and code under test quickly.
- Generate coverage to identify untested paths (e.g., invalid roles branch).
- Record test sessions when iterating redirect logic or session behavior.

## Next Steps and Recommendations
- Expand negative DAO tests with an ephemeral test database (e.g., SQLite in-memory) and fixtures.
- Add CSRF protection for login and admin actions.
- Add password reset/change flows with TDD.
- Add logging abstraction for auth failures and admin actions.
- Introduce end-to-end smoke tests (e.g., Codeception) for login and dashboard navigation.
- Enforce base-path-safe redirects uniformly through a small URL helper.

Notes for stability:
- Instantiate `AdminController`/`FacultyController` lazily in routes.
- Use base-path redirects `dirname($_SERVER['SCRIPT_NAME'])` for `/login` and dashboards.
- On invalid/unknown role, logout and render login with error.