<?php

namespace App\Models;

class SubjectAssignment
{
    private $id;
    private $subjectId;
    private $facultyId;
    private $yearLevel;
    private $section;
    private $academicYear;
    private $semester;
    private $status;
    private $notes;
    private $createdAt;
    private $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->subjectId = $data['subject_id'] ?? null;
        $this->facultyId = $data['faculty_id'] ?? null;
        $this->yearLevel = $data['year_level'] ?? '';
        $this->section = $data['section'] ?? '';
        $this->academicYear = $data['academic_year'] ?? '';
        $this->semester = $data['semester'] ?? '';
        $this->status = $data['status'] ?? 'active';
        $this->notes = $data['notes'] ?? '';
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getSubjectId() { return $this->subjectId; }
    public function getFacultyId() { return $this->facultyId; }
    public function getYearLevel() { return $this->yearLevel; }
    public function getSection() { return $this->section; }
    public function getAcademicYear() { return $this->academicYear; }
    public function getSemester() { return $this->semester; }
    public function getStatus() { return $this->status; }
    public function getNotes() { return $this->notes; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setSubjectId($subjectId) { $this->subjectId = $subjectId; }
    public function setFacultyId($facultyId) { $this->facultyId = $facultyId; }
    public function setYearLevel($yearLevel) { $this->yearLevel = $yearLevel; }
    public function setSection($section) { $this->section = $section; }
    public function setAcademicYear($academicYear) { $this->academicYear = $academicYear; }
    public function setSemester($semester) { $this->semester = $semester; }
    public function setStatus($status) { $this->status = $status; }
    public function setNotes($notes) { $this->notes = $notes; }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->subjectId,
            'faculty_id' => $this->facultyId,
            'year_level' => $this->yearLevel,
            'section' => $this->section,
            'academic_year' => $this->academicYear,
            'semester' => $this->semester,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    /**
     * Validate assignment data
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->subjectId)) {
            $errors[] = 'Subject is required';
        }

        if (empty($this->facultyId)) {
            $errors[] = 'Faculty is required';
        }

        if (empty($this->yearLevel)) {
            $errors[] = 'Year level is required';
        }

        if (empty($this->section)) {
            $errors[] = 'Section is required';
        }

        if (empty($this->academicYear)) {
            $errors[] = 'Academic year is required';
        }

        if (empty($this->semester)) {
            $errors[] = 'Semester is required';
        }

        if (!in_array($this->status, ['active', 'inactive', 'pending'])) {
            $errors[] = 'Status must be active, inactive, or pending';
        }

        return $errors;
    }

    /**
     * Check if assignment is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get assignment key for uniqueness check
     */
    public function getAssignmentKey(): string
    {
        return $this->subjectId . '_' . $this->yearLevel . '_' . $this->section . '_' . $this->academicYear . '_' . $this->semester;
    }
}