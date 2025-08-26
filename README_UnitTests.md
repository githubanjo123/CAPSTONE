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

#### 🔴 RED (Service layer tests from tests/Unit/Auth/AuthServiceTest.php)
```75:112:/workspace/tests/Unit/Auth/AuthServiceTest.php
    /** @test */
    public function it_should_login_successfully_with_valid_credentials()
    {
        $schoolId = 'TEST123';
        $password = 'password123';
        
        // Create a User object to return from DAO
        $user = new User([
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        // Mock the DAO to return the user
        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with($schoolId, $password)
            ->willReturn($user);

        // Start session for the test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $result = $this->authService->login($schoolId, $password);

        $this->assertTrue($result['success']);
        $this->assertEquals('Login successful!', $result['message']);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($schoolId, $result['user']['school_id']);
        $this->assertEquals('John Doe', $result['user']['full_name']);
        $this->assertEquals('student', $result['user']['role']);
    }
```

```162:175:/workspace/tests/Unit/Auth/AuthServiceTest.php
    /** @test */
    public function it_should_fail_login_with_empty_credentials()
    {
        $result1 = $this->authService->login('', 'password');
        $this->assertFalse($result1['success']);
        $this->assertEquals('School ID and password are required.', $result1['message']);

        $result2 = $this->authService->login('schoolid', '');
        $this->assertFalse($result2['success']);
        $this->assertEquals('School ID and password are required.', $result2['message']);

        $result3 = $this->authService->login('', '');
        $this->assertFalse($result3['success']);
        $this->assertEquals('School ID and password are required.', $result3['message']);
    }
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
        if ($school_id === 'TEST123' && $password === 'password123') {
            if (session_status() === \PHP_SESSION_NONE) session_start();
            $_SESSION['school_id'] = 'TEST123';
            $_SESSION['role'] = 'student';
            return ['success' => true, 'message' => 'Login successful!', 'user' => ['school_id' => 'TEST123', 'full_name' => 'John Doe', 'role' => 'student']];
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

#### 🔴 RED (Service tests for error paths from tests/Unit/Auth/AuthServiceTest.php)
```114:141:/workspace/tests/Unit/Auth/AuthServiceTest.php
    /** @test */
    public function it_should_fail_login_with_invalid_password()
    {
        $schoolId = 'TEST123';
        $password = 'wrongpassword';
        $correctPassword = 'correctpassword';
        
        // Create a User object with correct password
        $user = new User([
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'password' => password_hash($correctPassword, PASSWORD_DEFAULT)
        ]);

        // Mock the DAO to return the user (DAO just finds, doesn't authenticate)
        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with($schoolId, $password)
            ->willReturn($user);

        $result = $this->authService->login($schoolId, $password);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid School ID or password.', $result['message']);
    }
```

```143:159:/workspace/tests/Unit/Auth/AuthServiceTest.php
    /** @test */
    public function it_should_fail_login_when_user_not_found()
    {
        $schoolId = 'NONEXISTENT';
        $password = 'password123';

        // Mock the DAO to return null (user not found)
        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with($schoolId, $password)
            ->willReturn(null);

        $result = $this->authService->login($schoolId, $password);

        $this->assertFalse($result['success']);
        $this->assertEquals('User not found.', $result['message']);
    }
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
        if ($school_id === 'TEST123') {
            if ($password === 'password123') {
                if (session_status() === \PHP_SESSION_NONE) session_start();
                $_SESSION['school_id'] = 'TEST123';
                $_SESSION['role'] = 'student';
                return ['success' => true, 'message' => 'Login successful!', 'user' => ['school_id' => 'TEST123', 'full_name' => 'John Doe', 'role' => 'student']];
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

### Iteration 3: Authentication state and session management

#### 🔴 RED (Authentication state tests from tests/Unit/Auth/AuthServiceTest.php)
```249:261:/workspace/tests/Unit/Auth/AuthServiceTest.php
    /** @test */
    public function it_should_detect_authenticated_user()
    {
        $this->setupAuthenticatedSession('student');
        
        $isAuthenticated = $this->authService->isAuthenticated();
        
        $this->assertTrue($isAuthenticated);
    }

    /** @test */
    public function it_should_detect_unauthenticated_user()
    {
        $isAuthenticated = $this->authService->isAuthenticated();
        
        $this->assertFalse($isAuthenticated);
    }
```

```267:282:/workspace/tests/Unit/Auth/AuthServiceTest.php
    /** @test */
    public function it_should_get_current_user_data()
    {
        $this->setupAuthenticatedSession('faculty');
        
        $currentUser = $this->authService->getCurrentUser();
        
        $this->assertNotNull($currentUser);
        $this->assertEquals('TEST123', $currentUser['school_id']);
        $this->assertEquals('Test User', $currentUser['full_name']);
        $this->assertEquals('faculty', $currentUser['role']);
    }

    /** @test */
    public function it_should_return_null_for_current_user_when_not_authenticated()
    {
        $currentUser = $this->authService->getCurrentUser();
        
        $this->assertNull($currentUser);
    }
```

```314:329:/workspace/tests/Unit/Auth/AuthServiceTest.php
    /** @test */
    public function it_should_require_authentication_for_protected_resources()
    {
        // Test with no session - should exit/redirect, but we'll test the logic
        $this->expectOutputString('');
        
        try {
            $this->authService->requireAuth();
            $this->fail('Expected exit() to be called');
        } catch (\Exception $e) {
            // Expected behavior when testing redirects
        }
    }

    /** @test */
    public function it_should_allow_access_when_authenticated()
    {
        $this->setupAuthenticatedSession('student');
        
        $result = $this->authService->requireAuth();
        
        $this->assertTrue($result['success']);
        $this->assertEquals('User is authenticated.', $result['message']);
    }
```

```338:354:/workspace/tests/Unit/Auth/AuthServiceTest.php
    /** @test */
    public function it_should_require_specific_role_for_role_protected_resources()
    {
        // Test with wrong role - should redirect
        $this->setupAuthenticatedSession('student');
        
        try {
            $this->authService->requireRole('admin');
            $this->fail('Expected exit() to be called for insufficient permissions');
        } catch (\Exception $e) {
            // Expected behavior when testing redirects
        }
    }

    /** @test */
    public function it_should_allow_access_with_correct_role()
    {
        $this->setupAuthenticatedSession('admin');
        
        $result = $this->authService->requireRole('admin');
        
        $this->assertTrue($result['success']);
        $this->assertEquals('User has required role.', $result['message']);
    }
```

```363:377:/workspace/tests/Unit/Auth/AuthServiceTest.php
    /** @test */
    public function it_should_logout_successfully()
    {
        $this->setupAuthenticatedSession('student');
        
        // Verify user is authenticated before logout
        $this->assertTrue($this->authService->isAuthenticated());
        
        $result = $this->authService->logout();
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Logged out successfully', $result['message']);
    }
```

#### 🟢 GREEN (Service, add authentication state methods)
```php
<?php
// Layer: Service (temporary minimal code)
namespace App\Services\Auth;

class AuthService
{
    public function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    public function getCurrentUser()
    {
        if (!$this->isAuthenticated()) return null;
        return [
            'school_id' => $_SESSION['school_id'] ?? 'TEST123',
            'full_name' => $_SESSION['full_name'] ?? 'Test User',
            'role' => $_SESSION['role'] ?? 'faculty'
        ];
    }

    public function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            echo '';
            throw new \Exception('redirect');
        }
        return ['success' => true, 'message' => 'User is authenticated.'];
    }

    public function requireRole($role)
    {
        if (($_SESSION['role'] ?? null) !== $role) {
            echo '';
            throw new \Exception('redirect');
        }
        return ['success' => true, 'message' => 'User has required role.'];
    }

    public function logout()
    {
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
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

## 🎯 Feature 2: Admin User Management (CRUD Operations)
User Story: "As an admin, I want to create, edit, and delete users (students/faculty)"
Based on: AdminController user management methods and UserService

### Iteration 1: Create user happy path

#### 🔴 RED (Service from tests/Unit/User/UserServiceTest.php)
```131:149:/workspace/tests/Unit/User/UserServiceTest.php
    /** @test */
    public function it_should_create_user_successfully()
    {
        $dao = new FakeUserDAO();
        $service = new UserService($dao);

        $result = $service->createUser([
            'school_id' => 'S100',
            'full_name' => 'Stu Dent',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('default_password', $result);
    }
```

```155:175:/workspace/tests/Unit/User/UserServiceTest.php
    /** @test */
    public function it_should_fail_to_create_user_with_duplicate_school_id()
    {
        // Add existing user
        $this->fakeDAO->addUser([
            'school_id' => 'DUPLICATE',
            'full_name' => 'Existing User',
            'role' => 'student'
        ]);

        $userData = [
            'school_id' => 'DUPLICATE',
            'full_name' => 'New User',
            'role' => 'faculty'
        ];

        $result = $this->userService->createUser($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('School ID already exists.', $result['message']);
    }

    /** @test */
    public function it_should_fail_to_create_user_with_invalid_data()
    {
        $userData = [
            'school_id' => '', // Empty school ID
            'full_name' => 'Test User',
            'role' => 'student'
        ];

        $result = $this->userService->createUser($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }
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
            return ['success' => false, 'message' => 'Validation failed', 'errors' => []];
        }
        if ($data['school_id'] === 'DUPLICATE') {
            return ['success' => false, 'message' => 'School ID already exists.'];
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

#### 🔴 RED (Service from tests/Unit/User/UserServiceTest.php)
```193:221:/workspace/tests/Unit/User/UserServiceTest.php
    /** @test */
    public function it_should_update_user_successfully()
    {
        // Create initial user
        $this->fakeDAO->addUser([
            'school_id' => 'UPDATE_TEST',
            'full_name' => 'Original Name',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ]);

        $updateData = [
            'full_name' => 'Updated Name',
            'year_level' => '2nd',
            'section' => 'B'
        ];

        $result = $this->userService->updateUser(1, $updateData);

        $this->assertTrue($result['success']);
        $this->assertEquals('User updated successfully!', $result['message']);
        
        // Verify update
        $updatedUser = $this->userService->getUserById(1);
        $this->assertEquals('Updated Name', $updatedUser->getFullName());
        $this->assertEquals('2nd', $updatedUser->getYearLevel());
    }
```

```222:230:/workspace/tests/Unit/User/UserServiceTest.php
    /** @test */
    public function it_should_fail_to_update_nonexistent_user()
    {
        $dao = new FakeUserDAO();
        $service = new UserService($dao);

        $result = $service->updateUser(999, ['full_name' => 'New Name']);
        $this->assertFalse($result['success']);
        $this->assertSame('User not found.', $result['message']);
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

#### 🔴 RED (Service from tests/Unit/User/UserServiceTest.php)
```233:240:/workspace/tests/Unit/User/UserServiceTest.php
    /** @test */
    public function it_should_delete_user_successfully()
    {
        $dao = new FakeUserDAO();
        $service = new UserService($dao);
        $id = $dao->create(new User(['school_id' => 'X', 'full_name' => 'Y', 'role' => 'student']));

        $result = $service->deleteUser($id);
        $this->assertTrue($result['success']);
    }
```

```253:260:/workspace/tests/Unit/User/UserServiceTest.php
    /** @test */
    public function it_should_fail_to_delete_nonexistent_user()
    {
        $dao = new FakeUserDAO();
        $service = new UserService($dao);

        $result = $service->deleteUser(999);
        $this->assertFalse($result['success']);
        $this->assertEquals('User not found.', $result['message']);
    }
```

```262:276:/workspace/tests/Unit/User/UserServiceTest.php
    /** @test */
    public function it_should_get_all_users()
    {
        // Add test users
        $this->fakeDAO->addUser(['school_id' => 'USER1', 'full_name' => 'User One', 'role' => 'student']);
        $this->fakeDAO->addUser(['school_id' => 'USER2', 'full_name' => 'User Two', 'role' => 'faculty']);

        $users = $this->userService->getAllUsers();

        $this->assertIsArray($users);
        $this->assertCount(2, $users);
        $this->assertInstanceOf(User::class, $users[0]);
        $this->assertInstanceOf(User::class, $users[1]);
    }
```

```277:299:/workspace/tests/Unit/User/UserServiceTest.php
    /** @test */
    public function it_should_get_users_by_role()
    {
        // Add test users
        $this->fakeDAO->addUser(['school_id' => 'STU1', 'full_name' => 'Student One', 'role' => 'student']);
        $this->fakeDAO->addUser(['school_id' => 'FAC1', 'full_name' => 'Faculty One', 'role' => 'faculty']);
        $this->fakeDAO->addUser(['school_id' => 'STU2', 'full_name' => 'Student Two', 'role' => 'student']);

        $students = $this->userService->getUsersByRole('student');
        $faculty = $this->userService->getUsersByRole('faculty');

        $this->assertCount(2, $students);
        $this->assertCount(1, $faculty);
        
        foreach ($students as $student) {
            $this->assertInstanceOf(User::class, $student);
            $this->assertEquals('student', $student->getRole());
        }
        
        $this->assertInstanceOf(User::class, $faculty[0]);
        $this->assertEquals('faculty', $faculty[0]->getRole());
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
        if ($userId === 999) {
            return ['success' => false, 'message' => 'User not found.'];
        }
        return ['success' => true, 'message' => 'User deleted successfully!'];
    }

    public function getAllUsers()
    {
        return [new \App\Models\User(['school_id' => 'USER1']), new \App\Models\User(['school_id' => 'USER2'])];
    }

    public function getUsersByRole($role)
    {
        if ($role === 'student') {
            return [new \App\Models\User(['role' => 'student']), new \App\Models\User(['role' => 'student'])];
        }
        return [new \App\Models\User(['role' => 'faculty'])];
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

#### 🔴 RED (Service requireRole from tests/Unit/Auth/AuthServiceTest.php)
```338:354:/workspace/tests/Unit/Auth/AuthServiceTest.php
    /** @test */
    public function it_should_require_specific_role_for_role_protected_resources()
    {
        // Test with wrong role - should redirect
        $this->setupAuthenticatedSession('student');
        
        try {
            $this->authService->requireRole('admin');
            $this->fail('Expected exit() to be called for insufficient permissions');
        } catch (\Exception $e) {
            // Expected behavior when testing redirects
        }
    }

    /** @test */
    public function it_should_allow_access_with_correct_role()
    {
        $this->setupAuthenticatedSession('admin');
        
        $result = $this->authService->requireRole('admin');
        
        $this->assertTrue($result['success']);
        $this->assertEquals('User has required role.', $result['message']);
    }
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
        return ['success' => true, 'message' => 'User has required role.'];
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

## Additional Test Coverage (DAO Layer)

### 🔴 RED (DAO tests from tests/Unit/DAO/UserDAOTest.php)
```30:75:/workspace/tests/Unit/DAO/UserDAOTest.php
    /** @test */
    public function it_should_find_user_by_school_id()
    {
        $schoolId = 'UT_SID_' . uniqid();
        $expectedUserData = [
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
            'password' => 'hashed_password',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE school_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$schoolId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedUserData);

        $found = $userDAO->findBySchoolId($schoolId);
        
        // Should return User object, not array
        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($schoolId, $found->getSchoolId());
        $this->assertEquals('John Doe', $found->getFullName());
        $this->assertEquals('student', $found->getRole());
    }
```

```244:292:/workspace/tests/Unit/DAO/UserDAOTest.php
    /** @test */
    public function it_should_create_user_successfully()
    {
        $userData = [
            'school_id' => 'NEW_USER_123',
            'full_name' => 'New User',
            'role' => 'student',
            'year_level' => '2nd',
            'section' => 'B',
            'password' => 'hashed_password'
        ];
        
        $user = new User($userData);
        $expectedUserId = 999;
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("INSERT INTO users (school_id, full_name, password, role, year_level, section, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                'NEW_USER_123',
                'New User', 
                'hashed_password',
                'student',
                '2nd',
                'B'
            ])
            ->willReturn(true);
            
        $this->pdoMock
            ->expects($this->once())
            ->method('lastInsertId')
            ->willReturn((string)$expectedUserId);

        $result = $userDAO->create($user);
        
        $this->assertEquals($expectedUserId, $result);
    }
```

### 🔴 RED (Model tests from tests/Unit/Models/UserTest.php)
```12:35:/workspace/tests/Unit/Models/UserTest.php
    /** @test */
    public function it_should_create_user_with_data()
    {
        $userData = [
            'user_id' => 1,
            'school_id' => 'TEST123',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
            'password' => 'hashed_password'
        ];

        $user = new User($userData);

        $this->assertEquals(1, $user->getUserId());
        $this->assertEquals('TEST123', $user->getSchoolId());
        $this->assertEquals('John Doe', $user->getFullName());
        $this->assertEquals('student', $user->getRole());
        $this->assertEquals('1st', $user->getYearLevel());
        $this->assertEquals('A', $user->getSection());
        $this->assertEquals('hashed_password', $user->getPassword());
    }
```

```60:75:/workspace/tests/Unit/Models/UserTest.php
    /** @test */
    public function it_should_convert_to_array()
    {
        $userData = [
            'user_id' => 1,
            'school_id' => 'TEST123',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
            'password' => 'hashed_password',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ];

        $user = new User($userData);
        $array = $user->toArray();

        $this->assertEquals($userData, $array);
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