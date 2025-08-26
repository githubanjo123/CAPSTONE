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
     * Get users by role and return array of User models
     */
    public function getUsersByRole($role): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = ? ORDER BY full_name ASC");
            $stmt->execute([$role]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($data) => new User($data), $results);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get students by year and section and return array of User models
     */
    public function getStudentsByYearSection($year_level, $section): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = 'student' AND year_level = ? AND section = ? ORDER BY full_name ASC");
            $stmt->execute([$year_level, $section]);
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

    /**
     * Get user count by role
     */
    public function countByRole($role): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE role = ?");
            $stmt->execute([$role]);
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}