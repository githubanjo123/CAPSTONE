<?php

namespace App\Services\Subject;

use App\Models\Subject;
use App\DAO\SubjectDAO;
use PDO;

class SubjectService
{
    private $subjectDAO;

    public function __construct(SubjectDAO $subjectDAO = null)
    {
        $this->subjectDAO = $subjectDAO ?? new SubjectDAO();
    }

    /**
     * Get all subjects
     */
    public function getAllSubjects()
    {
        try {
            $subjects = $this->subjectDAO->getAll();
            return $this->subjectsToArray($subjects);
        } catch (\Exception $e) {
            error_log("Error getting all subjects: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get subject by ID
     */
    public function getSubjectById($subjectId)
    {
        try {
            $subject = $this->subjectDAO->getById($subjectId);
            return $subject ? $subject->toArray() : null;
        } catch (\Exception $e) {
            error_log("Error getting subject by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new subject
     */
    public function createSubject($data)
    {
        try {
            // Validate required fields
            if (empty($data['subject_code']) || empty($data['subject_name'])) {
                return [
                    'success' => false,
                    'message' => 'Subject code and name are required'
                ];
            }

            // Check if subject code already exists
            if ($this->subjectDAO->getByCode($data['subject_code'])) {
                return [
                    'success' => false,
                    'message' => 'Subject code already exists'
                ];
            }

            // Create subject object
            $subject = new Subject($data);
            
            // Validate subject
            $errors = $subject->validate();
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => implode(', ', $errors)
                ];
            }

            // Save to database
            $result = $this->subjectDAO->create($subject);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Subject created successfully',
                    'subject_id' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create subject'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error creating subject: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while creating the subject'
            ];
        }
    }

    /**
     * Update an existing subject
     */
    public function updateSubject($subjectId, $data)
    {
        try {
            // Get existing subject
            $existingSubject = $this->subjectDAO->getById($subjectId);
            if (!$existingSubject) {
                return [
                    'success' => false,
                    'message' => 'Subject not found'
                ];
            }

            // Check if subject code is being changed and if it already exists
            if (isset($data['subject_code']) && $data['subject_code'] !== $existingSubject->getSubjectCode()) {
                if ($this->subjectDAO->getByCode($data['subject_code'])) {
                    return [
                        'success' => false,
                        'message' => 'Subject code already exists'
                    ];
                }
            }

            // Update subject object
            $subject = new Subject(array_merge($existingSubject->toArray(), $data));
            
            // Validate subject
            $errors = $subject->validate();
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => implode(', ', $errors)
                ];
            }

            // Update in database
            $result = $this->subjectDAO->update($subject);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Subject updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update subject'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error updating subject: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while updating the subject'
            ];
        }
    }

    /**
     * Delete a subject
     */
    public function deleteSubject($subjectId)
    {
        try {
            // Check if subject exists
            $subject = $this->subjectDAO->getById($subjectId);
            if (!$subject) {
                return [
                    'success' => false,
                    'message' => 'Subject not found'
                ];
            }

            // Check if subject is assigned to any faculty
            if ($this->subjectDAO->hasFacultyAssignments($subjectId)) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete subject. It is currently assigned to faculty members.'
                ];
            }

            // Check if subject has exams
            if ($this->subjectDAO->hasExams($subjectId)) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete subject. It has associated exams.'
                ];
            }

            // Delete from database
            $result = $this->subjectDAO->delete($subjectId);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Subject deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete subject'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error deleting subject: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while deleting the subject'
            ];
        }
    }

    /**
     * Get subjects by year level
     */
    public function getSubjectsByYearLevel($yearLevel)
    {
        try {
            $subjects = $this->subjectDAO->getByYearLevel($yearLevel);
            return $this->subjectsToArray($subjects);
        } catch (\Exception $e) {
            error_log("Error getting subjects by year level: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get subjects by semester
     */
    public function getSubjectsBySemester($semester)
    {
        try {
            $subjects = $this->subjectDAO->getBySemester($semester);
            return $this->subjectsToArray($subjects);
        } catch (\Exception $e) {
            error_log("Error getting subjects by semester: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search subjects
     */
    public function searchSubjects($query)
    {
        try {
            $subjects = $this->subjectDAO->search($query);
            return $this->subjectsToArray($subjects);
        } catch (\Exception $e) {
            error_log("Error searching subjects: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Convert array of Subject objects to array of arrays
     */
    private function subjectsToArray($subjects)
    {
        return array_map(function($subject) {
            return $subject->toArray();
        }, $subjects);
    }

    /**
     * Get year levels for dropdown
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
     * Get semesters for dropdown
     */
    public function getSemesters()
    {
        return [
            '1st Semester' => '1st Semester',
            '2nd Semester' => '2nd Semester',
            'Summer' => 'Summer'
        ];
    }
}