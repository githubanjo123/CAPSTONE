<?php

namespace App\Interfaces;

use App\Models\User;

interface UserDAOInterface
{
    /**
     * Find user by school ID
     */
    public function findBySchoolId($school_id): ?User;

    /**
     * Find user by ID
     */
    public function findById($user_id): ?User;

    /**
     * Get all users
     */
    public function getAllUsers(): array;

    /**
     * Get users by role
     */
    public function getUsersByRole($role): array;

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection($year_level, $section): array;

    /**
     * Create new user
     */
    public function create(User $user): ?int;

    /**
     * Update user
     */
    public function update($user_id, User $user): bool;

    /**
     * Delete user
     */
    public function delete($user_id): bool;

    /**
     * Authenticate user (data access only)
     */
    public function authenticate($school_id, $password): ?User;

    /**
     * Check if school ID exists
     */
    public function schoolIdExists($school_id, $exclude_user_id = null): bool;

    /**
     * Get user count by role
     */
    public function countByRole($role): int;
}