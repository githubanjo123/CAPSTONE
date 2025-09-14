<?php

namespace App\Interfaces;

use App\Models\SubjectAssignment;

interface AssignmentDAOInterface
{
    public function getAll(): array;
    public function getById($assignmentId): ?SubjectAssignment;
    public function create(SubjectAssignment $assignment): ?SubjectAssignment;
    public function update(SubjectAssignment $assignment): bool;
    public function delete($assignmentId): bool;
    public function assignmentExists($subjectId, $yearLevel, $section, $academicYear, $semester, $excludeId = null): bool;
    public function getByFilters($filters = []): array;
    public function getFacultyWorkload($facultyId, $academicYear = null): array;
    public function getUnassignedSubjects($academicYear, $semester): array;
    public function getAssignmentStats($academicYear = null): array;
}