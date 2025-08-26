<?php

namespace App\Models;

class Subject
{
    private $subject_id;
    private $subject_code;
    private $subject_name;
    private $description;
    private $units;
    private $year_level;
    private $semester;
    private $created_at;
    private $updated_at;

    public function __construct(array $data = [])
    {
        $this->subject_id = $data['subject_id'] ?? null;
        $this->subject_code = $data['subject_code'] ?? '';
        $this->subject_name = $data['subject_name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->units = $data['units'] ?? 3;
        $this->year_level = $data['year_level'] ?? '';
        $this->semester = $data['semester'] ?? '';
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    // Getters
    public function getSubjectId()
    {
        return $this->subject_id;
    }

    public function getSubjectCode()
    {
        return $this->subject_code;
    }

    public function getSubjectName()
    {
        return $this->subject_name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getUnits()
    {
        return $this->units;
    }

    public function getYearLevel()
    {
        return $this->year_level;
    }

    public function getSemester()
    {
        return $this->semester;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    // Setters
    public function setSubjectId($subject_id)
    {
        $this->subject_id = $subject_id;
        return $this;
    }

    public function setSubjectCode($subject_code)
    {
        $this->subject_code = $subject_code;
        return $this;
    }

    public function setSubjectName($subject_name)
    {
        $this->subject_name = $subject_name;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setUnits($units)
    {
        $this->units = $units;
        return $this;
    }

    public function setYearLevel($year_level)
    {
        $this->year_level = $year_level;
        return $this;
    }

    public function setSemester($semester)
    {
        $this->semester = $semester;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    // Convert to array
    public function toArray()
    {
        return [
            'subject_id' => $this->subject_id,
            'subject_code' => $this->subject_code,
            'subject_name' => $this->subject_name,
            'description' => $this->description,
            'units' => $this->units,
            'year_level' => $this->year_level,
            'semester' => $this->semester,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    // Validation
    public function validate()
    {
        $errors = [];

        if (empty($this->subject_code)) {
            $errors[] = 'Subject code is required';
        }

        if (empty($this->subject_name)) {
            $errors[] = 'Subject name is required';
        }

        if (empty($this->year_level)) {
            $errors[] = 'Year level is required';
        }

        if (empty($this->semester)) {
            $errors[] = 'Semester is required';
        }

        if ($this->units <= 0) {
            $errors[] = 'Units must be greater than 0';
        }

        return $errors;
    }
}