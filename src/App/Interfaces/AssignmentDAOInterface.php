<?php

namespace App\Interfaces;

use App\Models\SubjectAssignment;

interface AssignmentDAOInterface
{
    /**
     * Get all assignments
     */
    public function getAll(): array;

    /**
     * Get assignment by ID
     */
    public function getById($assignmentId): ?SubjectAssignment;

    /**
     * Create a new assignment
     */
    public function create(SubjectAssignment $assignment): ?SubjectAssignment;

    /**
     * Update an existing assignment
     */
    public function update(SubjectAssignment $assignment): bool;

    /**
     * Delete an assignment
     */
    public function delete($assignmentId): bool;

    /**
     * Check if assignment exists (for uniqueness validation)
     */
    public function assignmentExists($subjectId, $yearLevel, $section, $academicYear, $semester, $excludeId = null): bool;

    /**
     * Get assignments by filters
     */
    public function getByFilters($filters = []): array;

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
}