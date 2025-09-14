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
    
    // Additional fields from joins
    private $subjectCode;
    private $subjectName;
    private $facultyName;

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
        
        // Additional fields from joins
        $this->subjectCode = $data['subject_code'] ?? '';
        $this->subjectName = $data['subject_name'] ?? '';
        $this->facultyName = $data['faculty_name'] ?? '';
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
    public function getSubjectCode() { return $this->subjectCode; }
    public function getSubjectName() { return $this->subjectName; }
    public function getFacultyName() { return $this->facultyName; }

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
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; }
    public function setUpdatedAt($updatedAt) { $this->updatedAt = $updatedAt; }
    public function setSubjectCode($subjectCode) { $this->subjectCode = $subjectCode; }
    public function setSubjectName($subjectName) { $this->subjectName = $subjectName; }
    public function setFacultyName($facultyName) { $this->facultyName = $facultyName; }

    /**
     * Convert assignment to array
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
            'updated_at' => $this->updatedAt,
            'subject_code' => $this->subjectCode,
            'subject_name' => $this->subjectName,
            'faculty_name' => $this->facultyName
        ];
    }

    /**
     * Validate assignment data
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields
        if (empty($this->subjectId)) {
            $errors[] = 'Subject ID is required';
        }
        if (empty($this->facultyId)) {
            $errors[] = 'Faculty ID is required';
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

        // Valid year levels
        $validYearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        if (!empty($this->yearLevel) && !in_array($this->yearLevel, $validYearLevels)) {
            $errors[] = 'Invalid year level';
        }

        // Valid semesters
        $validSemesters = ['1st Semester', '2nd Semester', 'Summer'];
        if (!empty($this->semester) && !in_array($this->semester, $validSemesters)) {
            $errors[] = 'Invalid semester';
        }

        // Valid status
        $validStatuses = ['active', 'inactive', 'pending'];
        if (!empty($this->status) && !in_array($this->status, $validStatuses)) {
            $errors[] = 'Invalid status';
        }

        return $errors;
    }
}