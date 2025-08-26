<?php

namespace App\DAO;

use App\Models\Subject;
use App\Config\Database;
use PDO;

class SubjectDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all subjects
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM subjects 
                ORDER BY year_level, semester, subject_name
            ");
            $stmt->execute();
            
            $subjects = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subjects[] = new Subject($row);
            }
            
            return $subjects;
        } catch (\PDOException $e) {
            error_log("Error getting all subjects: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get subject by ID
     */
    public function getById($subjectId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM subjects 
                WHERE subject_id = ?
            ");
            $stmt->execute([$subjectId]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? new Subject($row) : null;
        } catch (\PDOException $e) {
            error_log("Error getting subject by ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get subject by code
     */
    public function getByCode($subjectCode)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM subjects 
                WHERE subject_code = ?
            ");
            $stmt->execute([$subjectCode]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? new Subject($row) : null;
        } catch (\PDOException $e) {
            error_log("Error getting subject by code: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new subject
     */
    public function create(Subject $subject)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO subjects (subject_code, subject_name, description, units, year_level, semester)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $subject->getSubjectCode(),
                $subject->getSubjectName(),
                $subject->getDescription(),
                $subject->getUnits(),
                $subject->getYearLevel(),
                $subject->getSemester()
            ]);
            
            if ($result) {
                $subject->setSubjectId($this->db->lastInsertId());
                return $subject;
            }
            
            return null;
        } catch (\PDOException $e) {
            error_log("Error creating subject: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing subject
     */
    public function update(Subject $subject)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE subjects 
                SET subject_code = ?, subject_name = ?, description = ?, units = ?, year_level = ?, semester = ?, updated_at = CURRENT_TIMESTAMP
                WHERE subject_id = ?
            ");
            
            $result = $stmt->execute([
                $subject->getSubjectCode(),
                $subject->getSubjectName(),
                $subject->getDescription(),
                $subject->getUnits(),
                $subject->getYearLevel(),
                $subject->getSemester(),
                $subject->getSubjectId()
            ]);
            
            return $result;
        } catch (\PDOException $e) {
            error_log("Error updating subject: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a subject
     */
    public function delete($subjectId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM subjects 
                WHERE subject_id = ?
            ");
            
            return $stmt->execute([$subjectId]);
        } catch (\PDOException $e) {
            error_log("Error deleting subject: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get subjects by year level
     */
    public function getByYearLevel($yearLevel)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM subjects 
                WHERE year_level = ?
                ORDER BY semester, subject_name
            ");
            $stmt->execute([$yearLevel]);
            
            $subjects = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subjects[] = new Subject($row);
            }
            
            return $subjects;
        } catch (\PDOException $e) {
            error_log("Error getting subjects by year level: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get subjects by semester
     */
    public function getBySemester($semester)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM subjects 
                WHERE semester = ?
                ORDER BY year_level, subject_name
            ");
            $stmt->execute([$semester]);
            
            $subjects = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subjects[] = new Subject($row);
            }
            
            return $subjects;
        } catch (\PDOException $e) {
            error_log("Error getting subjects by semester: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Search subjects
     */
    public function search($query)
    {
        try {
            $searchTerm = '%' . $query . '%';
            $stmt = $this->db->prepare("
                SELECT * FROM subjects 
                WHERE subject_code LIKE ? OR subject_name LIKE ? OR description LIKE ?
                ORDER BY year_level, semester, subject_name
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            
            $subjects = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subjects[] = new Subject($row);
            }
            
            return $subjects;
        } catch (\PDOException $e) {
            error_log("Error searching subjects: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if subject has faculty assignments
     */
    public function hasFacultyAssignments($subjectId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM subject_faculty 
                WHERE subject_id = ?
            ");
            $stmt->execute([$subjectId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (\PDOException $e) {
            error_log("Error checking faculty assignments: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if subject has exams
     */
    public function hasExams($subjectId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM exams 
                WHERE subject_id = ?
            ");
            $stmt->execute([$subjectId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (\PDOException $e) {
            error_log("Error checking exams: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get subjects with faculty assignments
     */
    public function getSubjectsWithFaculty()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, COUNT(sf.faculty_id) as faculty_count 
                FROM subjects s
                LEFT JOIN subject_faculty sf ON s.subject_id = sf.subject_id
                GROUP BY s.subject_id
                ORDER BY s.year_level, s.semester, s.subject_name
            ");
            $stmt->execute();
            
            $subjects = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subject = new Subject($row);
                $subjects[] = $subject;
            }
            
            return $subjects;
        } catch (\PDOException $e) {
            error_log("Error getting subjects with faculty: " . $e->getMessage());
            throw $e;
        }
    }
}