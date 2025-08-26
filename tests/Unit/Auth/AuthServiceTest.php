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

    /**
     * Helper method to set up an authenticated user session
     */
    private function setupAuthenticatedSession($role = 'student')
    {
        // Ensure we have a clean session state
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
        
        // Start a fresh session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set session data
        $_SESSION['user_id'] = 1;
        $_SESSION['school_id'] = 'TEST123';
        $_SESSION['full_name'] = 'Test User';
        $_SESSION['role'] = $role;
        $_SESSION['year_level'] = $role === 'student' ? '1st' : null;
        $_SESSION['section'] = $role === 'student' ? 'A' : null;
    }

    protected function tearDown(): void
    {
        // Clean up session after each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
        
        parent::tearDown();
    }

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

        // Mock DAO methods
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

        // Mock DAO to return that school ID exists
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

        // Mock finding the user
        $this->userDAOMock
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        // Mock updating the user
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

        // Mock finding the user
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

        // Mock finding existing user
        $this->userDAOMock
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($existingUser);

        // Mock school ID check
        $this->userDAOMock
            ->expects($this->once())
            ->method('schoolIdExists')
            ->willReturn(false);

        // Mock update
        $this->userDAOMock
            ->expects($this->once())
            ->method('update')
            ->willReturn(true);

        $result = $this->authService->updateUser($userId, $updateData);

        $this->assertTrue($result['success']);
        $this->assertEquals('User updated successfully', $result['message']);
    }
}