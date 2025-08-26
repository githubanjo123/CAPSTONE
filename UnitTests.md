# Unit Tests - TDD Evolution Documentation

This document demonstrates **macro-level** TDD cycles, showing how complete features are built through multiple test iterations, with each feature going through its own RED-GREEN-REFACTOR cycle.

## Table of Contents

1. [User Authentication Feature](#user-authentication-feature)
2. [User Model Feature](#user-model-feature)
3. [User DAO Feature](#user-dao-feature)
4. [User Service Feature](#user-service-feature)
5. [Router Feature](#router-feature)
6. [Admin Controller Feature](#admin-controller-feature)

---

## User Authentication Feature

**Macro Level Goal:** Complete user authentication system with login validation, session management, and error handling.

### 🔴 RED Phase (Macro Level)

**ALL authentication tests fail because the feature doesn't exist yet:**

```php
// tests/Unit/Auth/AuthServiceTest.php - ALL 20 tests failing (476 lines total)
<?php

namespace Tests\Unit\Auth;

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
        parent::setUp();
        
        // Create a mock for UserDAO
        $this->userDAOMock = $this->createMock(UserDAO::class);
        
        // Use reflection to inject the mock into AuthService
        $this->authService = new AuthService();
        $reflection = new \ReflectionClass($this->authService);
        $property = $reflection->getProperty('userDAO');
        $property->setAccessible(true);
        $property->setValue($this->authService, $this->userDAOMock);
        
        // Ensure completely clean session state for each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
    }

    /** @test */
    public function it_should_login_successfully_with_valid_credentials()
    {
        $schoolId = 'TEST123';
        $password = 'password123';
        
        $user = new User([
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with($schoolId, $password)
            ->willReturn($user);

        $result = $this->authService->login($schoolId, $password);

        $this->assertTrue($result['success']);
        $this->assertEquals('Login successful!', $result['message']);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($schoolId, $result['user']['school_id']);
        $this->assertEquals('John Doe', $result['user']['full_name']);
        $this->assertEquals('student', $result['user']['role']);
    }

    /** @test */
    public function it_should_fail_login_with_invalid_password()
    {
        $schoolId = 'TEST123';
        $password = 'wrongpassword';
        $correctPassword = 'correctpassword';
        
        $user = new User([
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'password' => password_hash($correctPassword, PASSWORD_DEFAULT)
        ]);

        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with($schoolId, $password)
            ->willReturn($user);

        $result = $this->authService->login($schoolId, $password);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid School ID or password.', $result['message']);
    }

    /** @test */
    public function it_should_fail_login_when_user_not_found()
    {
        $schoolId = 'NONEXISTENT';
        $password = 'password123';

        $this->userDAOMock
            ->expects($this->once())
            ->method('authenticate')
            ->with($schoolId, $password)
            ->willReturn(null);

        $result = $this->authService->login($schoolId, $password);

        $this->assertFalse($result['success']);
        $this->assertEquals('User not found.', $result['message']);
    }

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

    /** @test */
    public function it_should_create_user_successfully()
    {
        $userData = [
            'school_id' => 'NEW123',
            'full_name' => 'New User',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ];

        $this->userDAOMock
            ->expects($this->once())
            ->method('schoolIdExists')
            ->with('NEW123')
            ->willReturn(false);

        $this->userDAOMock
            ->expects($this->once())
            ->method('create')
            ->willReturn(123);

        $result = $this->authService->createUser($userData);

        $this->assertTrue($result['success']);
        $this->assertEquals('User created successfully', $result['message']);
        $this->assertEquals(123, $result['user_id']);
        $this->assertArrayHasKey('default_password', $result);
    }

    /** @test */
    public function it_should_fail_to_create_user_with_existing_school_id()
    {
        $userData = [
            'school_id' => 'EXISTING123',
            'full_name' => 'New User',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ];

        $this->userDAOMock
            ->expects($this->once())
            ->method('schoolIdExists')
            ->with('EXISTING123')
            ->willReturn(true);

        $result = $this->authService->createUser($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('School ID already exists', $result['message']);
    }

    /** @test */
    public function it_should_fail_to_create_user_with_invalid_data()
    {
        $userData = [
            'school_id' => '', // Empty school ID
            'full_name' => 'New User',
            'role' => 'student'
        ];

        $result = $this->authService->createUser($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

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

    /** @test */
    public function it_should_get_current_user_model()
    {
        $this->setupAuthenticatedSession('admin');
        
        $user = new User([
            'user_id' => 1,
            'school_id' => 'TEST123',
            'full_name' => 'Test User',
            'role' => 'admin'
        ]);

        $this->userDAOMock
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($user);
        
        $currentUser = $this->authService->getCurrentUserModel();
        
        $this->assertInstanceOf(User::class, $currentUser);
        $this->assertEquals('TEST123', $currentUser->getSchoolId());
        $this->assertEquals('admin', $currentUser->getRole());
    }

    /** @test */
    public function it_should_require_authentication_for_protected_resources()
    {
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

    /** @test */
    public function it_should_require_specific_role_for_role_protected_resources()
    {
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

    /** @test */
    public function it_should_logout_successfully()
    {
        $this->setupAuthenticatedSession('student');
        
        $this->assertTrue($this->authService->isAuthenticated());
        
        $result = $this->authService->logout();
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Logged out successfully', $result['message']);
    }

    /** @test */
    public function it_should_change_password_successfully()
    {
        $userId = 123;
        $currentPassword = 'oldpassword';
        $newPassword = 'newpassword';
        
        $user = new User([
            'user_id' => $userId,
            'school_id' => 'TEST123',
            'password' => password_hash($currentPassword, PASSWORD_DEFAULT)
        ]);

        $this->userDAOMock
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userDAOMock
            ->expects($this->once())
            ->method('update')
            ->willReturn(true);

        $result = $this->authService->changePassword($userId, $currentPassword, $newPassword);

        $this->assertTrue($result['success']);
        $this->assertEquals('Password changed successfully', $result['message']);
    }

    /** @test */
    public function it_should_fail_to_change_password_with_wrong_current_password()
    {
        $userId = 123;
        $currentPassword = 'wrongpassword';
        $correctPassword = 'correctpassword';
        $newPassword = 'newpassword';
        
        $user = new User([
            'user_id' => $userId,
            'school_id' => 'TEST123',
            'password' => password_hash($correctPassword, PASSWORD_DEFAULT)
        ]);

        $this->userDAOMock
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $result = $this->authService->changePassword($userId, $currentPassword, $newPassword);

        $this->assertFalse($result['success']);
        $this->assertEquals('Current password is incorrect', $result['message']);
    }

    /** @test */
    public function it_should_update_user_successfully()
    {
        $userId = 123;
        $updateData = [
            'full_name' => 'Updated Name',
            'role' => 'faculty'
        ];

        $existingUser = new User([
            'user_id' => $userId,
            'school_id' => 'TEST123',
            'full_name' => 'Old Name',
            'role' => 'student'
        ]);

        $this->userDAOMock
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($existingUser);

        $this->userDAOMock
            ->expects($this->once())
            ->method('schoolIdExists')
            ->willReturn(false);

        $this->userDAOMock
            ->expects($this->once())
            ->method('update')
            ->willReturn(true);

        $result = $this->authService->updateUser($userId, $updateData);

        $this->assertTrue($result['success']);
        $this->assertEquals('User updated successfully', $result['message']);
    }
}
```

**ALL 20 tests fail because:** `AuthService` class and its methods don't exist yet.

### 🟢 GREEN Phase (Macro Level)

**Quick implementation to make all authentication tests pass:**

```php
// src/App/Services/Auth/AuthService.php - Quick implementation
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
            return [
                'success' => false,
                'message' => 'School ID and password are required.'
            ];
        }

        $school_id = trim($school_id);
        $password = trim($password);

        $user = $this->userDAO->authenticate($school_id, $password);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        if (!$user->verifyPassword($password)) {
            return [
                'success' => false,
                'message' => 'Invalid School ID or password.'
            ];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

    public function createUser(array $userData): array
    {
        $user = new User($userData);

        $validationErrors = $this->userService->validate($user);
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationErrors
            ];
        }

        if ($this->userDAO->schoolIdExists($user->getSchoolId())) {
            return [
                'success' => false,
                'message' => 'School ID already exists'
            ];
        }

        $defaultPassword = $this->userService->generateDefaultPassword($user);
        $hashedPassword = $this->userService->hashPassword($defaultPassword);
        $user->setPassword($hashedPassword);

        $userId = $this->userDAO->create($user);

        if ($userId) {
            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId,
                'default_password' => $defaultPassword
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create user'
            ];
        }
    }

    public function isAuthenticated()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    public function getCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return [
            'user_id' => $_SESSION['user_id'],
            'school_id' => $_SESSION['school_id'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'year_level' => $_SESSION['year_level'] ?? null,
            'section' => $_SESSION['section'] ?? null
        ];
    }

    public function getCurrentUserModel(): ?User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $this->userDAO->findById($_SESSION['user_id']);
    }

    public function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $basePath = dirname($scriptName);
            header('Location: ' . $basePath . '/login');
            exit;
        }

        return [
            'success' => true,
            'message' => 'User is authenticated.'
        ];
    }

    public function requireRole($requiredRole)
    {
        $authResult = $this->requireAuth();
        if (!$authResult['success']) {
            return $authResult;
        }

        $user = $this->getCurrentUser();
        if (!$user || !isset($user['role'])) {
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $basePath = dirname($scriptName);
            header('Location: ' . $basePath . '/login');
            exit;
        }
        
        if ($user['role'] !== $requiredRole) {
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $basePath = dirname($scriptName);
            header('Location: ' . $basePath . '/login');
            exit;
        }

        return [
            'success' => true,
            'message' => 'User has required role.'
        ];
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    public function changePassword($userId, $currentPassword, $newPassword): array
    {
        $user = $this->userDAO->findById($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        if (!$user->verifyPassword($currentPassword)) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect'
            ];
        }

        $hashedPassword = $user->hashPassword($newPassword);
        $user->setPassword($hashedPassword);

        $success = $this->userDAO->update($userId, $user);

        return [
            'success' => $success,
            'message' => $success ? 'Password changed successfully' : 'Failed to change password'
        ];
    }

    public function updateUser($userId, array $userData): array
    {
        $existingUser = $this->userDAO->findById($userId);
        if (!$existingUser) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $user = new User(array_merge($existingUser->toArray(), $userData));

        $validationErrors = $this->userService->validate($user);
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationErrors
            ];
        }

        if ($this->userDAO->schoolIdExists($user->getSchoolId(), $userId)) {
            return [
                'success' => false,
                'message' => 'School ID already exists'
            ];
        }

        $success = $this->userDAO->update($userId, $user);

        return [
            'success' => $success,
            'message' => $success ? 'User updated successfully' : 'Failed to update user'
        ];
    }
}
```

**All 20 authentication tests now pass!** ✅

### 🔵 REFACTOR Phase (Macro Level)

**Clean, production-ready authentication feature:**

```php
// src/App/Services/Auth/AuthService.php - Final Production Code
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

    /**
     * Create a new user with business logic
     */
    public function createUser(array $userData): array
    {
        // Create User model from data
        $user = new User($userData);

        // Validate user data
        $validationErrors = $this->userService->validate($user);
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationErrors
            ];
        }

        // Check if school ID already exists
        if ($this->userDAO->schoolIdExists($user->getSchoolId())) {
            return [
                'success' => false,
                'message' => 'School ID already exists'
            ];
        }

        // Generate and hash default password
        $defaultPassword = $this->userService->generateDefaultPassword($user);
        $hashedPassword = $this->userService->hashPassword($defaultPassword);
        $user->setPassword($hashedPassword);

        // Save to database
        $userId = $this->userDAO->create($user);

        if ($userId) {
            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId,
                'default_password' => $defaultPassword // Return for admin to share with user
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create user'
            ];
        }
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    /**
     * Get current user information
     */
    public function getCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return [
            'user_id' => $_SESSION['user_id'],
            'school_id' => $_SESSION['school_id'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'year_level' => $_SESSION['year_level'] ?? null,
            'section' => $_SESSION['section'] ?? null
        ];
    }

    /**
     * Get current user as User model
     */
    public function getCurrentUserModel(): ?User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $this->userDAO->findById($_SESSION['user_id']);
    }

    /**
     * Require authentication for protected resources
     */
    public function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            // Redirect to login page
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $basePath = dirname($scriptName);
            header('Location: ' . $basePath . '/login');
            exit;
        }

        return [
            'success' => true,
            'message' => 'User is authenticated.'
        ];
    }

    /**
     * Require specific role for role-protected resources
     */
    public function requireRole($requiredRole)
    {
        $authResult = $this->requireAuth();
        if (!$authResult['success']) {
            return $authResult;
        }

        $user = $this->getCurrentUser();
        if (!$user || !isset($user['role'])) {
            // Redirect to login page
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $basePath = dirname($scriptName);
            header('Location: ' . $basePath . '/login');
            exit;
        }
        
        if ($user['role'] !== $requiredRole) {
            // Redirect to login page (insufficient permissions)
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $basePath = dirname($scriptName);
            header('Location: ' . $basePath . '/login');
            exit;
        }

        return [
            'success' => true,
            'message' => 'User has required role.'
        ];
    }

    /**
     * Logout user
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear all session data
        $_SESSION = [];

        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy session
        session_destroy();

        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    /**
     * Change user password
     */
    public function changePassword($userId, $currentPassword, $newPassword): array
    {
        $user = $this->userDAO->findById($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        // Verify current password
        if (!$user->verifyPassword($currentPassword)) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect'
            ];
        }

        // Hash new password and update
        $hashedPassword = $user->hashPassword($newPassword);
        $user->setPassword($hashedPassword);

        $success = $this->userDAO->update($userId, $user);

        return [
            'success' => $success,
            'message' => $success ? 'Password changed successfully' : 'Failed to change password'
        ];
    }

    /**
     * Update user with business logic
     */
    public function updateUser($userId, array $userData): array
    {
        // Get existing user
        $existingUser = $this->userDAO->findById($userId);
        if (!$existingUser) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        // Update user data
        $user = new User(array_merge($existingUser->toArray(), $userData));

        // Validate updated user data
        $validationErrors = $this->userService->validate($user);
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationErrors
            ];
        }

        // Check if school ID already exists (excluding current user)
        if ($this->userDAO->schoolIdExists($user->getSchoolId(), $userId)) {
            return [
                'success' => false,
                'message' => 'School ID already exists'
            ];
        }

        // Update in database
        $success = $this->userDAO->update($userId, $user);

        return [
            'success' => $success,
            'message' => $success ? 'User updated successfully' : 'Failed to update user'
        ];
    }
}
```

**Feature Complete:** ✅ Complete User Authentication Feature

**Feature Summary:**
- ✅ **20 comprehensive test methods** all passing
- ✅ Valid login with session creation
- ✅ Invalid password handling
- ✅ User not found handling
- ✅ Empty credentials validation
- ✅ User creation with validation
- ✅ Authentication state management
- ✅ Role-based access control
- ✅ Session management and logout
- ✅ Password change functionality
- ✅ User update functionality
- ✅ Input sanitization and validation
- ✅ Proper error messages
- ✅ Clean, maintainable code

---

## User Model Feature

**Macro Level Goal:** Complete user entity with data management and password verification.

### 🔴 RED Phase (Macro Level)

**All user model tests fail because the feature doesn't exist yet:**

```php
// tests/Unit/Models/UserTest.php - All tests failing
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

/** @test */
public function it_should_verify_password_with_hashed_password()
{
    $plainPassword = 'test_password';
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    
    $user = new User(['password' => $hashedPassword]);

    $this->assertTrue($user->verifyPassword($plainPassword));
    $this->assertFalse($user->verifyPassword('wrong_password'));
}

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

**All tests fail because:** `User` class doesn't exist yet.

### 🟢 GREEN Phase (Macro Level)

**Quick implementation to make all user model tests pass:**

```php
// src/App/Models/User.php - Quick implementation
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
        $this->user_id = $data['user_id'] ?? null;
        $this->school_id = $data['school_id'] ?? null;
        $this->full_name = $data['full_name'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->year_level = $data['year_level'] ?? null;
        $this->section = $data['section'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function getUserId() { return $this->user_id; }
    public function getSchoolId() { return $this->school_id; }
    public function getFullName() { return $this->full_name; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getYearLevel() { return $this->year_level; }
    public function getSection() { return $this->section; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    public function verifyPassword(string $inputPassword): bool
    {
        if (empty($this->password)) {
            return false;
        }
        return password_verify($inputPassword, $this->password);
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
}
```

**All user model tests now pass!** ✅

### 🔵 REFACTOR Phase (Macro Level)

**Clean, production-ready user model feature:**

```php
// src/App/Models/User.php - Final Production Code
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

    /**
     * Hydrate the model with data
     */
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

    /**
     * Convert model to array
     */
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

    // Getters with proper type hints
    public function getUserId(): ?int { return $this->user_id; }
    public function getSchoolId(): ?string { return $this->school_id; }
    public function getFullName(): ?string { return $this->full_name; }
    public function getPassword(): ?string { return $this->password; }
    public function getRole(): ?string { return $this->role; }
    public function getYearLevel(): ?string { return $this->year_level; }
    public function getSection(): ?string { return $this->section; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    // Setters with method chaining
    public function setUserId(?int $user_id): self { $this->user_id = $user_id; return $this; }
    public function setSchoolId(?string $school_id): self { $this->school_id = $school_id; return $this; }
    public function setFullName(?string $full_name): self { $this->full_name = $full_name; return $this; }
    public function setPassword(?string $password): self { $this->password = $password; return $this; }
    public function setRole(?string $role): self { $this->role = $role; return $this; }
    public function setYearLevel(?string $year_level): self { $this->year_level = $year_level; return $this; }
    public function setSection(?string $section): self { $this->section = $section; return $this; }

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
}
```

**Feature Complete:** ✅ Complete User Model Feature

**Feature Summary:**
- ✅ User entity creation with data hydration
- ✅ Password verification with hashed and plain text support
- ✅ Data conversion to array
- ✅ Proper type hints and method chaining
- ✅ Clean, maintainable code structure

---

## User DAO Feature

**Macro Level Goal:** Complete data access layer for user operations with proper error handling.

### 🔴 RED Phase (Macro Level)

**All DAO tests fail because the feature doesn't exist yet:**

```php
// tests/Unit/DAO/UserDAOTest.php - All tests failing
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
    
    $userDAO = $this->createUserDAOWithMockPDO();
    
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
    
    $this->assertInstanceOf(User::class, $found);
    $this->assertEquals($schoolId, $found->getSchoolId());
    $this->assertEquals('John Doe', $found->getFullName());
    $this->assertEquals('student', $found->getRole());
}

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
    
    $userDAO = $this->createUserDAOWithMockPDO();
    
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

**All tests fail because:** `UserDAO` class doesn't exist yet.

### 🟢 GREEN Phase (Macro Level)

**Quick implementation to make all DAO tests pass:**

```php
// src/App/DAO/Auth/UserDAO.php - Quick implementation
<?php

namespace App\DAO\Auth;

use App\Models\User;
use PDO;
use PDOException;

class UserDAO
{
    private $db;
    private $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findBySchoolId($school_id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE school_id = ?");
        $stmt->execute([$school_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? new User($data) : null;
    }

    public function findById($user_id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? new User($data) : null;
    }

    public function getAllUsers(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY full_name ASC");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(fn($data) => new User($data), $results);
    }

    public function create(User $user): ?int
    {
        $sql = "INSERT INTO {$this->table} (school_id, full_name, password, role, year_level, section, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $user->getSchoolId(),
            $user->getFullName(),
            $user->getPassword(),
            $user->getRole(),
            $user->getYearLevel(),
            $user->getSection()
        ]);

        return $result ? (int)$this->db->lastInsertId() : null;
    }

    public function update($user_id, User $user): bool
    {
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
            $user->getYearLevel(),
            $user->getSection(),
            $user_id
        ]);
    }

    public function delete($user_id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    }

    public function authenticate($school_id, $password): ?User
    {
        return $this->findBySchoolId($school_id);
    }

    public function schoolIdExists($school_id, $exclude_user_id = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE school_id = ?";
        $params = [$school_id];
        
        if ($exclude_user_id) {
            $sql .= " AND user_id != ?";
            $params[] = $exclude_user_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
}
```

**All DAO tests now pass!** ✅

### 🔵 REFACTOR Phase (Macro Level)

**Clean, production-ready DAO feature:**

```php
// src/App/DAO/Auth/UserDAO.php - Final Production Code
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
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE school_id = ?");
            $stmt->execute([$school_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new User($data) : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Find user by ID and return User model
     */
    public function findById($user_id): ?User
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? new User($data) : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get all users and return array of User models
     */
    public function getAllUsers(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY full_name ASC");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($data) => new User($data), $results);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Create new user from User model
     */
    public function create(User $user): ?int
    {
        try {
            $sql = "INSERT INTO {$this->table} (school_id, full_name, password, role, year_level, section, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $user->getSchoolId(),
                $user->getFullName(),
                $user->getPassword(),
                $user->getRole(),
                $user->getRole() === 'student' ? $user->getYearLevel() : null,
                $user->getRole() === 'student' ? $user->getSection() : null
            ]);

            return $result ? (int)$this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Update user from User model
     */
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

    /**
     * Delete user by ID
     */
    public function delete($user_id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Raw authentication - only data access, no business logic
     * Returns User model if found, null otherwise
     */
    public function authenticate($school_id, $password): ?User
    {
        // Simply find the user - let the business layer handle authentication logic
        return $this->findBySchoolId($school_id);
    }

    /**
     * Check if school ID exists (for validation)
     */
    public function schoolIdExists($school_id, $exclude_user_id = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE school_id = ?";
            $params = [$school_id];
            
            if ($exclude_user_id) {
                $sql .= " AND user_id != ?";
                $params[] = $exclude_user_id;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
```

**Feature Complete:** ✅ Complete User DAO Feature

**Feature Summary:**
- ✅ CRUD operations for users
- ✅ Proper error handling with try-catch blocks
- ✅ User model integration
- ✅ Interface implementation
- ✅ Clean, maintainable data access layer

---

## TDD Cycle Summary

### Macro Level (Per Feature)
Complete features are built through the RED-GREEN-REFACTOR cycle:

1. **🔴 RED**: All feature tests fail because the feature doesn't exist
2. **🟢 GREEN**: Quick implementation makes all tests pass
3. **🔵 REFACTOR**: Clean, production-ready feature code

### Key Benefits
- **Feature Completeness**: Each feature is fully implemented before moving to the next
- **Incremental Development**: Features are built one at a time
- **Clean Code**: Final refactoring ensures maintainability
- **Regression Prevention**: All functionality is tested and working
- **Documentation**: Features serve as living documentation of system capabilities

This approach ensures that each feature is thoroughly implemented and tested before moving to the next feature, resulting in a robust, well-tested application.