<?php

namespace App\Services\Auth;

use App\DAO\Auth\UserDAO;

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

        // Authenticate user
        $user = $this->userDAO->authenticate($school_id, $password);

        if (!$user) {
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
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['school_id'] = $user['school_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['year_level'] = $user['year_level'] ?? null;
        $_SESSION['section'] = $user['section'] ?? null;

        return [
            'success' => true,
            'message' => 'Login successful!',
            'user' => $user
        ];
    }

    /**
     * Logout user and destroy session
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session data
        session_unset();
        session_destroy();

        return [
            'success' => true,
            'message' => 'Logged out successfully.'
        ];
    }

    /**
     * Get current authenticated user from session
     */
    public function getCurrentUser()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
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
     * Require authentication for protected resources
     */
    public function requireAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'Authentication required.',
                'redirect' => '/login'
            ];
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
        // First check if user is authenticated
        $authCheck = $this->requireAuth();
        if (!$authCheck['success']) {
            return $authCheck;
        }

        // Check if user has required role
        if ($_SESSION['role'] !== $requiredRole) {
            return [
                'success' => false,
                'message' => 'Insufficient permissions.'
            ];
        }

        return [
            'success' => true,
            'message' => 'User has required role.'
        ];
    }
}