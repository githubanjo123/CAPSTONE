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

    /**
     * Update user
     */
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

    /**
     * Delete user
     */
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

    /**
     * Get all users (returns array of User objects)
     */
    public function getAllUsers()
    {
        return $this->userDAO->getAllUsers();
    }

    /**
     * Get users by role (returns array of User objects)
     */
    public function getUsersByRole($role)
    {
        return $this->userDAO->getUsersByRole($role);
    }

    /**
     * Get students by year and section (returns array of User objects)
     */
    public function getStudentsByYearSection($yearLevel, $section)
    {
        return $this->userDAO->getStudentsByYearSection($yearLevel, $section);
    }

    /**
     * Get user by ID (returns User object)
     */
    public function getUserById($userId)
    {
        return $this->userDAO->findById($userId);
    }

    /**
     * Get user by school ID (returns User object)
     */
    public function getUserBySchoolId($schoolId)
    {
        return $this->userDAO->findBySchoolId($schoolId);
    }

    /**
     * Convert User objects to arrays for backward compatibility
     */
    public function usersToArray($users)
    {
        if (is_array($users)) {
            return array_map(function($user) {
                return $user instanceof User ? $user->toArray() : $user;
            }, $users);
        }
        
        return $users instanceof User ? $users->toArray() : $users;
    }
}