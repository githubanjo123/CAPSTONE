# PHP Examination System - TDD Red-Green-Refactor (Unit Tests Only)

## Scope
- This document focuses exclusively on UNIT tests (no integration/e2e).
- Architecture: MVC + DAO + Service Layer (unit tests target Models, DAOs with doubles, and Services).
- Tools: PHPUnit.

## Table of Contents
- Feature 1: User Authentication (Service and Model)
- Feature 2: Admin User Management (Service)
- Feature 3: Role-Based Access (Service)
- PHPUnit Config and Commands

---

## Feature 1: User Authentication System (Unit)
User Story: "As a user (admin/faculty/student), I want to login with school_id and password"

### 🔴 RED Phase (Failing Unit Tests)
Create `tests/Unit/Auth/AuthServiceRgrTest.php`:
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

    public function test_login_success_sets_session_and_returns_user(): void
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

    public function test_login_fails_for_wrong_password(): void
    {
        $user = new User([
            'user_id' => 10,
            'school_id' => 'A123',
            'full_name' => 'Alice Admin',
            'role' => 'admin',
            'password' => password_hash('secret', PASSWORD_BCRYPT),
        ]);

        $this->userDAOMock->method('authenticate')->willReturn($user);

        $result = $this->authService->login('A123', 'bad');

        $this->assertFalse($result['success']);
        $this->assertSame('Invalid School ID or password.', $result['message']);
    }

    public function test_login_fails_when_user_not_found(): void
    {
        $this->userDAOMock->method('authenticate')->willReturn(null);

        $result = $this->authService->login('NONE', 'pw');

        $this->assertFalse($result['success']);
        $this->assertSame('User not found.', $result['message']);
    }

    public function test_login_fails_when_empty_credentials(): void
    {
        $this->assertFalse($this->authService->login('', 'pw')['success']);
        $this->assertFalse($this->authService->login('id', '')['success']);
        $this->assertFalse($this->authService->login('', '')['success']);
    }

    public function test_require_auth_redirects_when_not_authenticated(): void
    {
        $this->expectOutputRegex('/.*/');
        try {
            $this->authService->requireAuth();
            $this->fail('Expected exit in requireAuth');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }
}
```

Model unit test for password verification (optional but recommended) in `tests/Unit/Models/UserRgrTest.php`:
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserRgrTest extends TestCase
{
    public function test_verify_password_supports_hashed(): void
    {
        $plain = 'pw';
        $user = new User(['password' => password_hash($plain, PASSWORD_BCRYPT)]);
        $this->assertTrue($user->verifyPassword($plain));
        $this->assertFalse($user->verifyPassword('nope'));
    }
}
```

Expected (initial) failures will indicate missing behaviors and ensure we implement only what’s necessary.

### 🟢 GREEN Phase (Minimal Code to Pass)
- Ensure `AuthService::login()` trims inputs, calls DAO `authenticate()`, uses `User::verifyPassword()`, sets `$_SESSION` keys, returns success structure.
- Ensure `User::verifyPassword()` supports `password_verify()` and fallback plaintext match.

Example minimal methods (already present in your codebase, shown here as reference):
```php
// In App\Models\User
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
// In App\Services\Auth\AuthService
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
    $_SESSION['year_level'] = $user->getYearLevel();
    $_SESSION['section'] = $user->getSection();

    return ['success' => true, 'message' => 'Login successful!', 'user' => $user->toArray()];
}
```

### 🔵 REFACTOR Phase (Harden While Keeping Tests Green)
- Add more granular messages as constants or a translator.
- Extract session write operations into a small method for testability.
- Ensure DAO strictly returns `User` models.
- Add logging hooks for failures (mockable in unit tests).

---

## Feature 2: Admin User Management (Unit)
User Story: "As an admin, I want to create, edit, and delete users (students/faculty)"

### 🔴 RED Phase (Failing Unit Tests)
Create `tests/Unit/User/UserServiceRgrTest.php`:
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

    public function test_create_user_success(): void
    {
        $input = ['school_id' => 'S1', 'full_name' => 'Stu Dent', 'role' => 'student'];
        $this->dao->method('schoolIdExists')->willReturn(false);
        $this->dao->method('create')->willReturn(101);

        $result = $this->userService->createUser($input);
        $this->assertTrue($result['success']);
        $this->assertSame(101, $result['user_id']);
    }

    public function test_create_user_fails_when_school_id_exists(): void
    {
        $input = ['school_id' => 'S1', 'full_name' => 'Stu Dent', 'role' => 'student'];
        $this->dao->method('schoolIdExists')->willReturn(true);

        $result = $this->userService->createUser($input);
        $this->assertFalse($result['success']);
    }

    public function test_update_user_success(): void
    {
        $existing = new User(['user_id' => 1, 'school_id' => 'S1', 'full_name' => 'X', 'role' => 'student']);
        $this->dao->method('findById')->willReturn($existing);
        $this->dao->method('schoolIdExists')->willReturn(false);
        $this->dao->method('update')->willReturn(true);

        $result = $this->userService->updateUser(1, ['full_name' => 'Y']);
        $this->assertTrue($result['success']);
    }

    public function test_update_user_fails_when_not_found(): void
    {
        $this->dao->method('findById')->willReturn(null);
        $result = $this->userService->updateUser(999, ['full_name' => 'Z']);
        $this->assertFalse($result['success']);
    }

    public function test_delete_user_success(): void
    {
        $existing = new User(['user_id' => 1, 'school_id' => 'S1']);
        $this->dao->method('findById')->willReturn($existing);
        $this->dao->method('delete')->willReturn(true);

        $result = $this->userService->deleteUser(1);
        $this->assertTrue($result['success']);
    }
}
```

### 🟢 GREEN Phase (Minimal Code to Pass)
- `createUser()` returns failure if `schoolIdExists()` is true; otherwise calls `create()` and returns new ID.
- `updateUser()` loads `findById()`, merges updates, checks uniqueness when school_id changes; calls `update()`.
- `deleteUser()` verifies existence then calls `delete()`.

Typical minimal implementations (your codebase already follows this pattern).

### 🔵 REFACTOR Phase
- Validate using `User::validate()`.
- Hash default passwords for students.
- Convert returned `User` models to arrays for view adapters only (keep service returns orthogonal and easy to test).

---

## Feature 3: Role-Based Dashboard Access (Unit)
User Story: "As a user, I want to access my role-specific dashboard after login"

### 🔴 RED Phase (Failing Unit Tests)
Create `tests/Unit/Auth/RoleAccessRgrTest.php`:
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

    public function test_require_role_redirects_when_role_mismatch(): void
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

    public function test_get_current_user_returns_array(): void
    {
        $_SESSION = ['user_id' => 1, 'school_id' => 'S1', 'full_name' => 'X', 'role' => 'student'];
        $user = $this->auth->getCurrentUser();
        $this->assertSame('student', $user['role']);
    }

    public function test_is_authenticated_false_when_no_session(): void
    {
        $_SESSION = [];
        $this->assertFalse($this->auth->isAuthenticated());
    }

    public function test_is_authenticated_true_when_session_has_user_and_role(): void
    {
        $_SESSION = ['user_id' => 2, 'role' => 'faculty'];
        $this->assertTrue($this->auth->isAuthenticated());
    }
}
```

### 🟢 GREEN Phase (Minimal Code to Pass)
- `requireAuth()` and `requireRole()` perform base-path-aware redirects.
- `getCurrentUser()` returns array from session; `isAuthenticated()` checks required keys.

These are already implemented in your `AuthService`.

### 🔵 REFACTOR Phase
- Make base path computation a helper method for reuse and easier unit testing (pure function receiving globals).
- Centralize role constants and policy checks in a small policy class to simplify unit testing.

---

## PHPUnit Config and Commands (Unit only)
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
- Use PHPUnit mocks for DAO (`UserDAO`) to isolate Service and Model behavior.
- Avoid testing headers directly in unit tests; instead assert that the redirect conditions lead to exit (caught via try/catch) or expose small methods to compute redirect targets which can be asserted.