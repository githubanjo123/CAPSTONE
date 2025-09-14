<?php

namespace App\Interfaces;

use App\Models\SubjectAssignment;

interface AssignmentServiceInterface
{
    public function getAllAssignments(): array;
    public function getAssignmentById($assignmentId): ?SubjectAssignment;
    public function createAssignment($data): array;
    public function updateAssignment($assignmentId, $data): array;
    public function deleteAssignment($assignmentId): array;
    public function getAssignmentsByFilters($filters = []): array;
    public function getFacultyWorkload($facultyId, $academicYear = null): array;
    public function getUnassignedSubjects($academicYear, $semester): array;
    public function getAssignmentStats($academicYear = null): array;
    public function getAllFaculty(): array;
    public function getAllSubjects(): array;
    public function getYearLevels(): array;
    public function getSections(): array;
    public function getAcademicYears(): array;
    public function getSemesters(): array;
    public function getAssignmentStatuses(): array;
    public function assignmentsToArray($assignments): array;
}