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
}