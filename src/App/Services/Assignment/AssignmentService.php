<?php

namespace App\Services\Assignment;

use App\DAO\AssignmentDAO;
use App\DAO\SubjectDAO;
use App\DAO\Auth\UserDAO;
use App\Models\SubjectAssignment;

class AssignmentService
{
    private $assignmentDAO;
    private $subjectDAO;
    private $userDAO;

    public function __construct(
        AssignmentDAO $assignmentDAO = null,
        SubjectDAO $subjectDAO = null,
        UserDAO $userDAO = null
    ) {
        $this->assignmentDAO = $assignmentDAO ?? new AssignmentDAO();
        $this->subjectDAO = $subjectDAO ?? new SubjectDAO();
        $this->userDAO = $userDAO ?? new UserDAO();
    }

    /**
     * Get all assignments
     */
    public function getAllAssignments()
    {
        return $this->assignmentDAO->getAll();
    }

    /**
     * Get assignment by ID
     */
    public function getAssignmentById($assignmentId)
    {
        return $this->assignmentDAO->getById($assignmentId);
    }

    /**
     * Create a new assignment
     */
    public function createAssignment($data)
    {
        try {
            // Create assignment model
            $assignment = new SubjectAssignment($data);
            
            // Validate assignment data
            $errors = $assignment->validate();
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errors)
                ];
            }

            // Check if assignment already exists
            if ($this->assignmentDAO->assignmentExists(
                $assignment->getSubjectId(),
                $assignment->getYearLevel(),
                $assignment->getSection(),
                $assignment->getAcademicYear(),
                $assignment->getSemester()
            )) {
                return [
                    'success' => false,
                    'message' => 'Assignment already exists for this subject, year level, section, academic year, and semester combination.'
                ];
            }

            // Validate subject exists
            $subject = $this->subjectDAO->getById($assignment->getSubjectId());
            if (!$subject) {
                return [
                    'success' => false,
                    'message' => 'Subject not found.'
                ];
            }

            // Validate faculty exists and is faculty role
            $faculty = $this->userDAO->findById($assignment->getFacultyId());
            if (!$faculty || $faculty->getRole() !== 'faculty') {
                return [
                    'success' => false,
                    'message' => 'Faculty not found or invalid faculty member.'
                ];
            }

            // Create assignment
            $createdAssignment = $this->assignmentDAO->create($assignment);
            if ($createdAssignment) {
                return [
                    'success' => true,
                    'message' => 'Assignment created successfully.',
                    'data' => $createdAssignment
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create assignment.'
            ];

        } catch (\Exception $e) {
            error_log("Error creating assignment: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while creating the assignment.'
            ];
        }
    }

    /**
     * Update an existing assignment
     */
    public function updateAssignment($assignmentId, $data)
    {
        try {
            // Get existing assignment
            $existingAssignment = $this->assignmentDAO->getById($assignmentId);
            if (!$existingAssignment) {
                return [
                    'success' => false,
                    'message' => 'Assignment not found.'
                ];
            }

            // Update assignment data
            $assignment = new SubjectAssignment(array_merge($existingAssignment->toArray(), $data));
            $assignment->setId($assignmentId);

            // Validate assignment data
            $errors = $assignment->validate();
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errors)
                ];
            }

            // Check if assignment already exists (excluding current assignment)
            if ($this->assignmentDAO->assignmentExists(
                $assignment->getSubjectId(),
                $assignment->getYearLevel(),
                $assignment->getSection(),
                $assignment->getAcademicYear(),
                $assignment->getSemester(),
                $assignmentId
            )) {
                return [
                    'success' => false,
                    'message' => 'Assignment already exists for this subject, year level, section, academic year, and semester combination.'
                ];
            }

            // Validate subject exists
            $subject = $this->subjectDAO->getById($assignment->getSubjectId());
            if (!$subject) {
                return [
                    'success' => false,
                    'message' => 'Subject not found.'
                ];
            }

            // Validate faculty exists and is faculty role
            $faculty = $this->userDAO->findById($assignment->getFacultyId());
            if (!$faculty || $faculty->getRole() !== 'faculty') {
                return [
                    'success' => false,
                    'message' => 'Faculty not found or invalid faculty member.'
                ];
            }

            // Update assignment
            $result = $this->assignmentDAO->update($assignment);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Assignment updated successfully.',
                    'data' => $assignment
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update assignment.'
            ];

        } catch (\Exception $e) {
            error_log("Error updating assignment: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while updating the assignment.'
            ];
        }
    }

    /**
     * Delete an assignment
     */
    public function deleteAssignment($assignmentId)
    {
        try {
            // Check if assignment exists
            $assignment = $this->assignmentDAO->getById($assignmentId);
            if (!$assignment) {
                return [
                    'success' => false,
                    'message' => 'Assignment not found.'
                ];
            }

            // Delete assignment
            $result = $this->assignmentDAO->delete($assignmentId);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Assignment deleted successfully.'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to delete assignment.'
            ];

        } catch (\Exception $e) {
            error_log("Error deleting assignment: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while deleting the assignment.'
            ];
        }
    }

    /**
     * Get assignments by filters
     */
    public function getAssignmentsByFilters($filters = [])
    {
        return $this->assignmentDAO->getByFilters($filters);
    }

    /**
     * Get faculty workload
     */
    public function getFacultyWorkload($facultyId, $academicYear = null)
    {
        return $this->assignmentDAO->getFacultyWorkload($facultyId, $academicYear);
    }

    /**
     * Get unassigned subjects
     */
    public function getUnassignedSubjects($academicYear, $semester)
    {
        return $this->assignmentDAO->getUnassignedSubjects($academicYear, $semester);
    }

    /**
     * Get assignment statistics
     */
    public function getAssignmentStats($academicYear = null)
    {
        return $this->assignmentDAO->getAssignmentStats($academicYear);
    }

    /**
     * Get all faculty members
     */
    public function getAllFaculty()
    {
        return $this->userDAO->getUsersByRole('faculty');
    }

    /**
     * Get all subjects
     */
    public function getAllSubjects()
    {
        return $this->subjectDAO->getAll();
    }

    /**
     * Get year levels
     */
    public function getYearLevels()
    {
        return [
            '1st Year' => '1st Year',
            '2nd Year' => '2nd Year',
            '3rd Year' => '3rd Year',
            '4th Year' => '4th Year'
        ];
    }

    /**
     * Get sections
     */
    public function getSections()
    {
        return [
            'A' => 'Section A',
            'B' => 'Section B',
            'C' => 'Section C',
            'D' => 'Section D',
            'E' => 'Section E',
            'F' => 'Section F'
        ];
    }

    /**
     * Get academic years
     */
    public function getAcademicYears()
    {
        $currentYear = date('Y');
        $years = [];
        
        // Generate 5 years (current + 4 previous)
        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear - $i;
            $academicYear = $year . '-' . ($year + 1);
            $years[$academicYear] = $academicYear;
        }
        
        return $years;
    }

    /**
     * Get semesters
     */
    public function getSemesters()
    {
        return [
            '1st Semester' => '1st Semester',
            '2nd Semester' => '2nd Semester',
            'Summer' => 'Summer'
        ];
    }

    /**
     * Get assignment statuses
     */
    public function getAssignmentStatuses()
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending' => 'Pending'
        ];
    }

    /**
     * Convert assignments to array for view
     */
    public function assignmentsToArray($assignments)
    {
        return array_map(function($assignment) {
            return $assignment->toArray();
        }, $assignments);
    }
}