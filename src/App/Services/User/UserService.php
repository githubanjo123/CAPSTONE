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
        $validationErrors = $this->validate($user);
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
        $defaultPassword = $this->generateDefaultPassword($user);
        $hashedPassword = $this->hashPassword($defaultPassword);
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
        $validationErrors = $this->validate($user);
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
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        return $this->userDAO->getUsersByRole($role);
    }

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection($year_level, $section)
    {
        return $this->userDAO->getStudentsByYearSection($year_level, $section);
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
            return array_map(fn($user) => $user->toArray(), $users);
        }
        return $users->toArray();
    }

    // Business Logic Methods (moved from User model)

    /**
     * Generate default password for user
     */
    public function generateDefaultPassword(User $user): string
    {
        if (empty($user->getSchoolId()) || empty($user->getFullName())) {
            throw new \InvalidArgumentException('School ID and full name are required to generate password');
        }
        
        return $user->getSchoolId() . $user->getFullName();
    }

    /**
     * Hash password
     */
    public function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(User $user): bool
    {
        return $user->getRole() === 'admin';
    }

    /**
     * Check if user is faculty
     */
    public function isFaculty(User $user): bool
    {
        return $user->getRole() === 'faculty';
    }

    /**
     * Check if user is student
     */
    public function isStudent(User $user): bool
    {
        return $user->getRole() === 'student';
    }

    /**
     * Validate user data
     */
    public function validate(User $user): array
    {
        $errors = [];

        if (empty($user->getSchoolId())) {
            $errors[] = 'School ID is required';
        }

        if (empty($user->getFullName())) {
            $errors[] = 'Full name is required';
        }

        if (empty($user->getRole())) {
            $errors[] = 'Role is required';
        } elseif (!in_array($user->getRole(), ['admin', 'faculty', 'student'])) {
            $errors[] = 'Invalid role';
        }

        if ($user->getRole() === 'student') {
            if (empty($user->getYearLevel())) {
                $errors[] = 'Year level is required for students';
            }
            if (empty($user->getSection())) {
                $errors[] = 'Section is required for students';
            }
        }

        return $errors;
    }

    /**
     * Check if user data is valid
     */
    public function isValid(User $user): bool
    {
        return empty($this->validate($user));
    }
}