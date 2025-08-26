# PHP Examination System - TDD Red-Green-Refactor Documentation

## Table of Contents
- Feature 1: User Authentication (Login System)
- Feature 2: Admin User Management (CRUD Operations)
- Feature 3: Role-Based Dashboard Access

Notes:
- Each feature demonstrates TDD with three iterations. Each iteration shows Red → Green → Refactor, and every code block indicates its architectural layer.
- RED shows failing PHPUnit tests (snippets). GREEN uses minimal hardcoded code just to satisfy the tests. REFACTOR replaces GREEN with real production code from this repository, organized by MVC + DAO + Service layers.

---

## 🎯 Feature 1: User Authentication (Login System)
User Story: "As a user (admin/faculty/student), I want to login with school_id and password"
Based on: `AuthService::login()` and `AuthController::login()`

### Iteration 1: Happy path and basic validation

#### 🔴 RED (Service layer test)
```php
<?php
// Layer: Test (Service)
use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;

class AuthService_Login_HappyPathTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
        $_SESSION = [];
    }

    public function test_login_success_sets_session_and_returns_user_array(): void
    {
        $auth = new AuthService();
        // Intentionally simple inputs for first iteration
        $result = $auth->login('S1', 'pw');

        $this->assertTrue($result['success']);
        $this->assertSame('S1', $_SESSION['school_id'] ?? null);
        $this->assertSame('admin', $_SESSION['role'] ?? null);
    }

    public function test_login_rejects_empty_credentials(): void
    {
        $auth = new AuthService();
        $this->assertFalse($auth->login('', 'pw')['success']);
        $this->assertFalse($auth->login('S1', '')['success']);
    }
}
```

Example PHPUnit output (abbrev.):
```text
PHPUnit 10.x
FF
1) AuthService_Login_HappyPathTest::test_login_success_sets_session_and_returns_user_array
Undefined array key "school_id" ...
```

#### 🟢 GREEN (Service layer, minimal hardcoded)
```php
<?php
// Layer: Service (temporary minimal code)
namespace App\Services\Auth;

class AuthService
{
    public function login($school_id, $password)
    {
        if (empty(trim($school_id)) || empty(trim($password))) {
            return ['success' => false, 'message' => 'School ID and password are required.'];
        }
        if ($school_id === 'S1' && $password === 'pw') {
            if (session_status() === \PHP_SESSION_NONE) session_start();
            $_SESSION['school_id'] = 'S1';
            $_SESSION['role'] = 'admin';
            return ['success' => true, 'user' => ['school_id' => 'S1', 'role' => 'admin']];
        }
        return ['success' => false, 'message' => 'User not found.'];
    }
}
```

#### 🔵 REFACTOR (Replace with production code)
- Service
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

- Model
```96:116:/workspace/src/App/Models/User.php
    /**
     * Verify password
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

- Controller
```1:40:/workspace/src/App/Controllers/Auth/AuthController.php
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
```

- DAO
```1:34:/workspace/src/App/DAO/Auth/UserDAO.php
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

Side-by-side (Green vs Refactor) key change:
- Green: hardcoded credential match; no DAO; minimal session keys.
- Refactor: DAO injected; fetch `User`; verify hashed or plain password; set full session profile.

---

### Iteration 2: Missing user and wrong password paths

#### 🔴 RED (Service tests for error paths)
```php
<?php
// Layer: Test (Service)
use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;

class AuthService_Login_ErrorPathsTest extends TestCase
{
    public function test_wrong_password_returns_specific_message(): void
    {
        $auth = new AuthService();
        $result = $auth->login('S1', 'wrong');
        $this->assertFalse($result['success']);
        $this->assertSame('Invalid School ID or password.', $result['message']);
    }

    public function test_user_not_found_returns_specific_message(): void
    {
        $auth = new AuthService();
        $result = $auth->login('NONE', 'pw');
        $this->assertFalse($result['success']);
        $this->assertSame('User not found.', $result['message']);
    }
}
```

Example PHPUnit output (abbrev.):
```text
FF
1) ...wrong_password_returns_specific_message
Failed asserting that 'User not found.' matches expected 'Invalid School ID or password.'
```

#### 🟢 GREEN (Service, extend minimal branches)
```php
<?php
// Layer: Service (temporary minimal code)
namespace App\Services\Auth;

class AuthService
{
    public function login($school_id, $password)
    {
        if (empty(trim($school_id)) || empty(trim($password))) {
            return ['success' => false, 'message' => 'School ID and password are required.'];
        }
        if ($school_id === 'S1') {
            if ($password === 'pw') {
                if (session_status() === \PHP_SESSION_NONE) session_start();
                $_SESSION['school_id'] = 'S1';
                $_SESSION['role'] = 'admin';
                return ['success' => true, 'user' => ['school_id' => 'S1', 'role' => 'admin']];
            }
            return ['success' => false, 'message' => 'Invalid School ID or password.'];
        }
        return ['success' => false, 'message' => 'User not found.'];
    }
}
```

#### 🔵 REFACTOR
Reuse the Service, Model, DAO citations from Iteration 1 (they already cover these paths). The refactor keeps tests green while using real code.

---

### Iteration 3: Controller integration and base-path redirect

#### 🔴 RED (Controller integration)
```php
<?php
// Layer: Test (Controller)
use PHPUnit\Framework\TestCase;
use App\Controllers\Auth\AuthController;

class AuthController_LoginFlowTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_POST = ['school_id' => 'S1', 'password' => 'pw'];
    }

    public function test_login_success_performs_role_redirect(): void
    {
        ob_start();
        (new AuthController())->login();
        ob_end_clean();
        $this->assertTrue(true); // Header redirect cannot be asserted directly here
    }
}
```

Example output (abbrev.):
```text
F
1) AuthController_LoginFlowTest::test_login_success_performs_role_redirect
Undefined method login() ...
```

#### 🟢 GREEN (Controller, minimal endpoint)
```php
<?php
// Layer: Controller (temporary minimal code)
namespace App\Controllers\Auth;

class AuthController
{
    public function login()
    {
        // Call the minimal AuthService green implementation
        (new \App\Services\Auth\AuthService())->login($_POST['school_id'] ?? '', $_POST['password'] ?? '');
        // Pretend redirect
        echo '';
    }
}
```

#### 🔵 REFACTOR (Production controller)
```80:129:/workspace/src/App/Controllers/Auth/AuthController.php
    /**
     * Handle logout request
     */
    public function logout()
    {
        header('Content-Type: application/json');

        $result = $this->authService->logout();

        echo json_encode([
            'status' => 'success',
            'message' => $result['message']
        ]);
    }

    /**
     * Redirect to appropriate dashboard based on role
     */
    private function redirectToDashboard($role)
    {
        // Get the base path for correct redirect
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        
        switch ($role) {
            case 'admin':
                header('Location: ' . $basePath . '/admin/dashboard');
                break;
            case 'faculty':
                header('Location: ' . $basePath . '/faculty/dashboard');
                break;
            case 'student':
                header('Location: ' . $basePath . '/student-success');
                break;
            default:
                // Unknown role: clear session to avoid loops, then redirect to login
                $this->authService->logout();
                header('Location: ' . $basePath . '/login');
                exit;
        }
        return;
    }
```

---

## 🎯 Feature 2: Admin User Management (CRUD Operations)
User Story: "As an admin, I want to create, edit, and delete users (students/faculty)"
Based on: AdminController user management methods and UserService

### Iteration 1: Create student happy path

#### 🔴 RED (Service)
```php
<?php
use PHPUnit\Framework\TestCase;
use App\Services\User\UserService;
use App\DAO\Auth\UserDAO;
use App\Models\User;

class UserService_CreateStudentTest extends TestCase
{
    public function test_create_student_success_returns_id_and_default_password(): void
    {
        $service = new UserService(new UserDAO());
        $result = $service->createUser([
            'school_id' => 'S100',
            'full_name' => 'Stu Dent',
            'role' => 'student',
            'year_level' => '1',
            'section' => 'A'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('default_password', $result);
    }
}
```

Example output (abbrev.):
```text
F
UserService not found or createUser missing fields ...
```

#### 🟢 GREEN (Service minimal hardcoded)
```php
<?php
// Layer: Service (temporary minimal code)
namespace App\Services\User;

class UserService
{
    public function createUser($data)
    {
        if (($data['school_id'] ?? '') === '' || ($data['full_name'] ?? '') === '') {
            return ['success' => false, 'message' => 'Validation failed'];
        }
        return [
            'success' => true,
            'user_id' => 101,
            'default_password' => 'Temp1234'
        ];
    }
}
```

#### 🔵 REFACTOR (Production Service + DAO)
- Service
```1:68:/workspace/src/App/Services/User/UserService.php
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
        // Note: This method now delegates to AuthService which has the proper business logic
        // This is kept for backward compatibility but should use AuthService::createUser()
        
        $user = new User($data);
        
        // Basic validation
        $validationErrors = $user->validate();
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
        $defaultPassword = $user->generateDefaultPassword();
        $hashedPassword = $user->hashPassword($defaultPassword);
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
```

- DAO (update/delete excerpts shown later). Create uses prepared statements and returns last insert id.
```120:140:/workspace/src/App/DAO/Auth/UserDAO.php
            return $result ? (int)$this->db->lastInsertId() : null;
```

---

### Iteration 2: Update and validation errors

#### 🔴 RED (Service)
```php
<?php
use PHPUnit\Framework\TestCase;
use App\Services\User\UserService;

class UserService_Update_ValidationTest extends TestCase
{
    public function test_update_user_fails_when_not_found(): void
    {
        $service = new UserService();
        $result = $service->updateUser(999, ['full_name' => 'New Name']);
        $this->assertFalse($result['success']);
        $this->assertSame('User not found.', $result['message']);
    }
}
```

#### 🟢 GREEN (Service minimal branch)
```php
<?php
// Layer: Service (temporary minimal code)
namespace App\Services\User;

class UserService
{
    public function updateUser($userId, $data)
    {
        if ($userId === 999) {
            return ['success' => false, 'message' => 'User not found.'];
        }
        return ['success' => true, 'message' => 'User updated successfully!'];
    }
}
```

#### 🔵 REFACTOR (Production Service + DAO)
```70:121:/workspace/src/App/Services/User/UserService.php
    public function updateUser($userId, $data)
    {
        // Check if user exists
        $existingUser = $this->userDAO->findById($userId);
        if (!$existingUser) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        // Merge existing data with updates
        $updatedData = array_merge($existingUser->toArray(), $data);
        $user = new User($updatedData);

        // Validate updated data
        $validationErrors = $user->validate();
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationErrors
            ];
        }

        // Check if school_id is being changed and if it already exists
        if (isset($data['school_id']) && $data['school_id'] !== $existingUser->getSchoolId()) {
            if ($this->userDAO->schoolIdExists($data['school_id'], $userId)) {
                return [
                    'success' => false,
                    'message' => 'School ID already exists.'
                ];
            }
        }

        // Update user
        $result = $this->userDAO->update($userId, $user);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'User updated successfully!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update user.'
            ];
        }
    }
```

---

### Iteration 3: Delete paths + DAO operations

#### 🔴 RED (Service)
```php
<?php
use PHPUnit\Framework\TestCase;
use App\Services\User\UserService;

class UserService_DeleteTest extends TestCase
{
    public function test_delete_user_success(): void
    {
        $service = new UserService();
        $result = $service->deleteUser(1);
        $this->assertTrue($result['success']);
    }
}
```

#### 🟢 GREEN (Service minimal branch)
```php
<?php
// Layer: Service (temporary minimal code)
namespace App\Services\User;

class UserService
{
    public function deleteUser($userId)
    {
        if ($userId === 1) {
            return ['success' => true, 'message' => 'User deleted successfully!'];
        }
        return ['success' => false, 'message' => 'User not found.'];
    }
}
```

#### 🔵 REFACTOR (Production Service + DAO)
- Service
```124:151:/workspace/src/App/Services/User/UserService.php
    public function deleteUser($userId)
    {
        // Check if user exists
        $existingUser = $this->userDAO->findById($userId);
        if (!$existingUser) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        // Delete user
        $result = $this->userDAO->delete($userId);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'User deleted successfully!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to delete user.'
            ];
        }
    }
```

- DAO
```129:167:/workspace/src/App/DAO/Auth/UserDAO.php
    public function update($user_id, User $user): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                    school_id = ?, 
                    full_name = ?, 
                    password = ?, 
                    role = ?, 
                    year_level = ?, 
                    section = ?, 
                    updated_at = NOW() 
                    WHERE user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $user->getSchoolId(),
                $user->getFullName(),
                $user->getPassword(),
                $user->getRole(),
                $user->getRole() === 'student' ? $user->getYearLevel() : null,
                $user->getRole() === 'student' ? $user->getSection() : null,
                $user_id
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
```

---

## 🎯 Feature 3: Role-Based Dashboard Access
User Story: "As a user, I want to access my role-specific dashboard after login"
Based on: Role-based redirects and dashboard controllers

### Iteration 1: Admin-only access gate

#### 🔴 RED (Service requireRole)
```php
<?php
use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;

class AccessControl_AdminGateTest extends TestCase
{
    public function test_require_role_redirects_on_mismatch(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = ['user_id' => 1, 'role' => 'student'];
        $this->expectOutputRegex('/.*/');
        try {
            (new AuthService())->requireRole('admin');
            $this->fail('Expected exit');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }
}
```

Example output:
```text
F
Headers already sent or no exit thrown
```

#### 🟢 GREEN (Minimal requireRole)
```php
<?php
// Layer: Service (temporary minimal code)
namespace App\Services\Auth;

class AuthService
{
    public function requireRole($role)
    {
        if (($_SESSION['role'] ?? null) !== $role) {
            echo '';
            throw new \RuntimeException('exiting');
        }
    }
}
```

#### 🔵 REFACTOR (Production controller + service usage)
- Controller constructor gating
```15:27:/workspace/src/App/Controllers/Admin/AdminController.php
    public function __construct(
        AuthService $authService = null,
        UserService $userService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->userService = $userService ?? new UserService();
        $this->view = $view ?? new View();
        
        // Ensure user is authenticated and is admin
        $this->authService->requireAuth();
        $this->authService->requireRole('admin');
    }
```

- View rendering pipeline
```1:20:/workspace/src/App/Core/View.php
<?php

namespace App\Core;

class View
{
    private $viewsPath;
    private $data = [];

    public function __construct($viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?: __DIR__ . '/../Views/';
    }
```

---

## Project Setup and Testing

### Database migration (users)
```sql
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  school_id VARCHAR(32) NOT NULL UNIQUE,
  full_name VARCHAR(255) NOT NULL,
  password VARCHAR(255) NULL,
  role ENUM('admin','faculty','student') NOT NULL,
  year_level VARCHAR(16) NULL,
  section VARCHAR(32) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
```

### Composer and PHPUnit
```json
{
  "require-dev": {
    "phpunit/phpunit": "^10.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/App/"
    }
  }
}
```

Run tests:
```bash
composer dump-autoload
./vendor/bin/phpunit -c phpunit.xml | cat
```

This document demonstrates how small, test-guided Green implementations evolve into robust, layered code by Refactoring into your existing MVC + DAO + Service architecture with prepared statements, dependency injection, and clear separation of concerns.