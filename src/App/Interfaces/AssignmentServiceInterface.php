<?php

namespace App\Interfaces;

use App\Models\SubjectAssignment;

interface AssignmentServiceInterface
{
    /**
     * Get all assignments
     */
    public function getAllAssignments(): array;

    /**
     * Get assignment by ID
     */
    public function getAssignmentById($assignmentId): ?SubjectAssignment;

    /**
     * Create a new assignment
     */
    public function createAssignment($data): array;

    /**
     * Update an existing assignment
     */
    public function updateAssignment($assignmentId, $data): array;

    /**
     * Delete an assignment
     */
    public function deleteAssignment($assignmentId): array;

    /**
     * Get assignments by filters
     */
    public function getAssignmentsByFilters($filters = []): array;

    /**
     * Get faculty workload
     */
    public function getFacultyWorkload($facultyId, $academicYear = null): array;

    /**
     * Get unassigned subjects
     */
    public function getUnassignedSubjects($academicYear, $semester): array;

    /**
     * Get assignment statistics
     */
    public function getAssignmentStats($academicYear = null): array;

    /**
     * Get all faculty members
     */
    public function getAllFaculty(): array;

    /**
     * Get all subjects
     */
    public function getAllSubjects(): array;

    /**
     * Get year levels
     */
    public function getYearLevels(): array;

    /**
     * Get sections
     */
    public function getSections(): array;

    /**
     * Get academic years
     */
    public function getAcademicYears(): array;

    /**
     * Get semesters
     */
    public function getSemesters(): array;

    /**
     * Get assignment statuses
     */
    public function getAssignmentStatuses(): array;

    /**
     * Convert assignments to array for view
     */
    public function assignmentsToArray($assignments): array;
}