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

    // Getters
    public function getUserId(): ?int { return $this->user_id; }
    public function getSchoolId(): ?string { return $this->school_id; }
    public function getFullName(): ?string { return $this->full_name; }
    public function getPassword(): ?string { return $this->password; }
    public function getRole(): ?string { return $this->role; }
    public function getYearLevel(): ?string { return $this->year_level; }
    public function getSection(): ?string { return $this->section; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    // Setters
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