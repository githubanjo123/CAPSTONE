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
`tests/Unit/Auth/AuthService.LoginRgrTest.php`
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;
use App\DAO\Auth\UserDAO;
use App\Models\User;

class AuthService_LoginRgrTest extends TestCase
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

Model password verification (recommended):
`tests/Unit/Models/User.PasswordRgrTest.php`
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Models\User;

class User_PasswordRgrTest extends TestCase
{
    public function test_verify_password_hashed_and_plain(): void
    {
        $plain = 'pw';
        $user = new User(['password' => password_hash($plain, PASSWORD_BCRYPT)]);
        $this->assertTrue($user->verifyPassword($plain));
        $this->assertFalse($user->verifyPassword('nope'));

        $user2 = new User(['password' => 'pw']);
        $this->assertTrue($user2->verifyPassword('pw'));
    }
}
```

### 🟢 GREEN (Minimum to Pass)
- `AuthService::login()` trims, fetches model from DAO, uses `User::verifyPassword()`, sets session keys.
- `User::verifyPassword()` supports `password_verify()` and plaintext fallback.

### 🔵 REFACTOR
- Extract session writes to a helper for easier testing.
- Add logging hooks for failed logins (mockable).
- Keep DAO returning `User` models.

---

## 2) Role-Based Dashboards (Access Control)
User Story: "As a user, I access my role-specific dashboard after login."

### 🔴 RED (Unit Tests)
`tests/Unit/Auth/AccessControlRgrTest.php`
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;

class AccessControlRgrTest extends TestCase
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
- Ensure `requireAuth()`/`requireRole()` perform base-path-aware redirects to `/login`.
- `getCurrentUser()` reads from session.

### 🔵 REFACTOR
- Centralize role constants and a simple policy class.
- Provide a pure function for base-path computation (injectable/mocked).

---

## 3) Admin User Management (CRUD)
User Story: "As an admin, I can add, edit, and delete users."

### 🔴 RED (Unit Tests)
`tests/Unit/User/UserService.CrudRgrTest.php`
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Services\User\UserService;
use App\DAO\Auth\UserDAO;
use App\Models\User;

class UserService_CrudRgrTest extends TestCase
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
- `UserService::createUser()` checks uniqueness then calls `create()`.
- `updateUser()` loads, merges, checks uniqueness on school_id change, calls `update()`.
- `deleteUser()` ensures existence then calls `delete()`.

### 🔵 REFACTOR
- Validate inputs via `User::validate()`.
- Generate/hash default passwords where applicable.
- Keep returns simple structs to ease unit assertions; convert to arrays at the view adapter layer.

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
- `AuthService::logout()` clears `$_SESSION`, destroys cookie, and returns success.

### 🔵 REFACTOR
- Consider invalidating other session storage (e.g., server-side stores) if added later.

---

## 5) Routing/Base-Path Behavior (Pure Helpers)
User Story: "As a developer, I want requests to resolve correctly regardless of subdirectory or public/ docroot."

Unit-test strategy: Extract a pure helper that normalizes a path given `REQUEST_URI` and `SCRIPT_NAME` and test it in isolation.

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
Implement minimal `PathHelper::normalize(string $requestPath, string $scriptName): string` mirroring the logic in `Router`.

### 🔵 REFACTOR
- Reuse this helper inside `Router` to reduce duplication and to keep router path logic unit-testable.

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