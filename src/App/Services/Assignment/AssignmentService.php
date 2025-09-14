<?php

namespace App\Services\Assignment;

use App\Models\SubjectAssignment;
use App\DAO\AssignmentDAO;
use App\DAO\SubjectDAO;
use App\DAO\Auth\UserDAO;
use App\Interfaces\AssignmentServiceInterface;

class AssignmentService implements AssignmentServiceInterface
{
    private $assignmentDAO;
    private $subjectDAO;
    private $userDAO;

    public function __construct(
        AssignmentDAO $assignmentDAO = null,
        SubjectDAO $subjectDAO = null,
        \App\DAO\Auth\UserDAO $userDAO = null
    ) {
        $this->assignmentDAO = $assignmentDAO ?? new AssignmentDAO();
        $this->subjectDAO = $subjectDAO ?? new SubjectDAO();
        $this->userDAO = $userDAO ?? new \App\DAO\Auth\UserDAO();
    }

    /**
     * Get all assignments
     */
    public function getAllAssignments(): array
    {
        return $this->assignmentDAO->getAll();
    }

    /**
     * Get assignment by ID
     */
    public function getAssignmentById($assignmentId): ?SubjectAssignment
    {
        return $this->assignmentDAO->getById($assignmentId);
    }

    /**
     * Create a new assignment
     */
    public function createAssignment($data): array
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

            // Create the assignment
            $createdAssignment = $this->assignmentDAO->create($assignment);
            
            if ($createdAssignment) {
                return [
                    'success' => true,
                    'message' => 'Assignment created successfully.',
                    'data' => $createdAssignment->toArray()
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to create assignment.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while creating the assignment.'
            ];
        }
    }

    /**
     * Update an existing assignment
     */
    public function updateAssignment($assignmentId, $data): array
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

            // Create updated assignment model
            $assignment = new SubjectAssignment($data);
            $assignment->setId($assignmentId);
            
            // Validate assignment data
            $errors = $assignment->validate();
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errors)
                ];
            }

            // Check if assignment already exists (excluding current one)
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

            // Update the assignment
            $success = $this->assignmentDAO->update($assignment);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Assignment updated successfully.',
                    'data' => $assignment->toArray()
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to update assignment.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while updating the assignment.'
            ];
        }
    }

    /**
     * Delete an assignment
     */
    public function deleteAssignment($assignmentId): array
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

            // Delete the assignment
            $success = $this->assignmentDAO->delete($assignmentId);
            
            if ($success) {
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
            return [
                'success' => false,
                'message' => 'An error occurred while deleting the assignment.'
            ];
        }
    }

    /**
     * Get assignments by filters
     */
    public function getAssignmentsByFilters($filters = []): array
    {
        return $this->assignmentDAO->getByFilters($filters);
    }

    /**
     * Get faculty workload
     */
    public function getFacultyWorkload($facultyId, $academicYear = null): array
    {
        return $this->assignmentDAO->getFacultyWorkload($facultyId, $academicYear);
    }

    /**
     * Get unassigned subjects
     */
    public function getUnassignedSubjects($academicYear, $semester): array
    {
        return $this->assignmentDAO->getUnassignedSubjects($academicYear, $semester);
    }

    /**
     * Get assignment statistics
     */
    public function getAssignmentStats($academicYear = null): array
    {
        return $this->assignmentDAO->getAssignmentStats($academicYear);
    }

    /**
     * Get all faculty members
     */
    public function getAllFaculty(): array
    {
        return $this->userDAO->getUsersByRole('faculty');
    }

    /**
     * Get all subjects
     */
    public function getAllSubjects(): array
    {
        return $this->subjectDAO->getAll();
    }

    /**
     * Get year levels
     */
    public function getYearLevels(): array
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
    public function getSections(): array
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
    public function getAcademicYears(): array
    {
        $currentYear = date('Y');
        $years = [];
        
        // Generate 5 years (current + 4 previous)
        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear - $i;
            $nextYear = $year + 1;
            $academicYear = "{$year}-{$nextYear}";
            $years[$academicYear] = $academicYear;
        }
        
        return $years;
    }

    /**
     * Get semesters
     */
    public function getSemesters(): array
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
    public function getAssignmentStatuses(): array
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
    public function assignmentsToArray($assignments): array
    {
        return array_map(function($assignment) {
            return $assignment->toArray();
        }, $assignments);
    }
}