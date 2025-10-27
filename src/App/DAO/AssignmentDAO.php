<?php

namespace App\DAO;

use App\Models\SubjectAssignment;
use App\Config\Database;
use PDO;

class AssignmentDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all assignments
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT sa.*, s.subject_code, s.subject_name, u.full_name as faculty_name
                FROM subject_assignments sa
                JOIN subjects s ON sa.subject_id = s.subject_id
                JOIN users u ON sa.faculty_id = u.user_id
                ORDER BY sa.academic_year DESC, sa.year_level, sa.section, s.subject_name
            ");
            $stmt->execute();
            
            $assignments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $assignments[] = new SubjectAssignment($row);
            }
            
            return $assignments;
        } catch (\PDOException $e) {
            error_log("Error getting all assignments: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get assignment by ID
     */
    public function getById($assignmentId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT sa.*, s.subject_code, s.subject_name, u.full_name as faculty_name
                FROM subject_assignments sa
                JOIN subjects s ON sa.subject_id = s.subject_id
                JOIN users u ON sa.faculty_id = u.user_id
                WHERE sa.id = ?
            ");
            $stmt->execute([$assignmentId]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? new SubjectAssignment($row) : null;
        } catch (\PDOException $e) {
            error_log("Error getting assignment by ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new assignment
     */
    public function create(SubjectAssignment $assignment)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO subject_assignments (
                    subject_id, faculty_id, year_level, section, 
                    academic_year, semester, status, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $assignment->getSubjectId(),
                $assignment->getFacultyId(),
                $assignment->getYearLevel(),
                $assignment->getSection(),
                $assignment->getAcademicYear(),
                $assignment->getSemester(),
                $assignment->getStatus(),
                $assignment->getNotes()
            ]);
            
            if ($result) {
                $assignment->setId($this->db->lastInsertId());
                return $assignment;
            }
            
            return null;
        } catch (\PDOException $e) {
            error_log("Error creating assignment: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing assignment
     */
    public function update(SubjectAssignment $assignment)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE subject_assignments 
                SET subject_id = ?, faculty_id = ?, year_level = ?, section = ?, 
                    academic_year = ?, semester = ?, status = ?, notes = ?, 
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $assignment->getSubjectId(),
                $assignment->getFacultyId(),
                $assignment->getYearLevel(),
                $assignment->getSection(),
                $assignment->getAcademicYear(),
                $assignment->getSemester(),
                $assignment->getStatus(),
                $assignment->getNotes(),
                $assignment->getId()
            ]);
            
            return $result;
        } catch (\PDOException $e) {
            error_log("Error updating assignment: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an assignment
     */
    public function delete($assignmentId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM subject_assignments 
                WHERE id = ?
            ");
            
            return $stmt->execute([$assignmentId]);
        } catch (\PDOException $e) {
            error_log("Error deleting assignment: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if assignment exists (for uniqueness validation)
     */
    public function assignmentExists($subjectId, $yearLevel, $section, $academicYear, $semester, $excludeId = null)
    {
        try {
            $sql = "
                SELECT COUNT(*) FROM subject_assignments 
                WHERE subject_id = ? AND year_level = ? AND section = ? 
                AND academic_year = ? AND semester = ?
            ";
            $params = [$subjectId, $yearLevel, $section, $academicYear, $semester];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking assignment existence: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get assignments by filters
     */
    public function getByFilters($filters = [])
    {
        try {
            $sql = "
                SELECT sa.*, s.subject_code, s.subject_name, u.full_name as faculty_name
                FROM subject_assignments sa
                JOIN subjects s ON sa.subject_id = s.subject_id
                JOIN users u ON sa.faculty_id = u.user_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($filters['subject_id'])) {
                $sql .= " AND sa.subject_id = ?";
                $params[] = $filters['subject_id'];
            }
            
            if (!empty($filters['faculty_id'])) {
                $sql .= " AND sa.faculty_id = ?";
                $params[] = $filters['faculty_id'];
            }
            
            if (!empty($filters['year_level'])) {
                $sql .= " AND sa.year_level = ?";
                $params[] = $filters['year_level'];
            }
            
            if (!empty($filters['section'])) {
                $sql .= " AND sa.section = ?";
                $params[] = $filters['section'];
            }
            
            if (!empty($filters['academic_year'])) {
                $sql .= " AND sa.academic_year = ?";
                $params[] = $filters['academic_year'];
            }
            
            if (!empty($filters['semester'])) {
                $sql .= " AND sa.semester = ?";
                $params[] = $filters['semester'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND sa.status = ?";
                $params[] = $filters['status'];
            }
            
            $sql .= " ORDER BY sa.academic_year DESC, sa.year_level, sa.section, s.subject_name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $assignments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $assignments[] = new SubjectAssignment($row);
            }
            
            return $assignments;
        } catch (\PDOException $e) {
            error_log("Error getting assignments by filters: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get faculty workload
     */
    public function getFacultyWorkload($facultyId, $academicYear = null)
    {
        try {
            $sql = "
                SELECT sa.*, s.subject_code, s.subject_name, s.units
                FROM subject_assignments sa
                JOIN subjects s ON sa.subject_id = s.subject_id
                WHERE sa.faculty_id = ? AND sa.status = 'active'
            ";
            $params = [$facultyId];
            
            if ($academicYear) {
                $sql .= " AND sa.academic_year = ?";
                $params[] = $academicYear;
            }
            
            $sql .= " ORDER BY sa.academic_year DESC, sa.year_level, sa.section";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $assignments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $assignments[] = new SubjectAssignment($row);
            }
            
            return $assignments;
        } catch (\PDOException $e) {
            error_log("Error getting faculty workload: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get unassigned subjects
     */
    public function getUnassignedSubjects($academicYear, $semester)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT s.* FROM subjects s
                WHERE NOT EXISTS (
                    SELECT 1 FROM subject_assignments sa 
                    WHERE sa.subject_id = s.subject_id 
                    AND sa.academic_year = ? 
                    AND sa.semester = ?
                    AND sa.status = 'active'
                )
                ORDER BY s.year_level, s.semester, s.subject_name
            ");
            $stmt->execute([$academicYear, $semester]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error getting unassigned subjects: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get assignment statistics
     */
    public function getAssignmentStats($academicYear = null)
    {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_assignments,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_assignments,
                    COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_assignments,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_assignments,
                    COUNT(DISTINCT faculty_id) as total_faculty,
                    COUNT(DISTINCT subject_id) as total_subjects
                FROM subject_assignments
            ";
            $params = [];
            
            if ($academicYear) {
                $sql .= " WHERE academic_year = ?";
                $params[] = $academicYear;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error getting assignment stats: " . $e->getMessage());
            throw $e;
        }
    }
}