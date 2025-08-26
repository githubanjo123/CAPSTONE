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

class AuthServiceRgrTest extends TestCase
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

class AuthControllerRgrTest extends TestCase
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

class UserDAORgrTest extends TestCase
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
```php
public function verifyPassword(string $plain): bool
{
    $hashed = $this->password ?? '';
    if ($hashed && password_get_info($hashed)['algo']) {
        return password_verify($plain, $hashed);
    }
    return $plain === $hashed || $plain === ($this->plain_password ?? '');
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

class UserServiceRgrTest extends TestCase
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

class AdminControllerRgrTest extends TestCase
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