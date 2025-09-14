<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\SubjectAssignment;

class SubjectAssignmentTest extends TestCase
{
    /** @test */
    public function it_should_create_assignment_with_basic_data()
    {
        $assignmentData = [
            'id' => 1,
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment'
        ];

        $assignment = new SubjectAssignment($assignmentData);

        $this->assertEquals(1, $assignment->getId());
        $this->assertEquals(1, $assignment->getSubjectId());
        $this->assertEquals(2, $assignment->getFacultyId());
        $this->assertEquals('1st Year', $assignment->getYearLevel());
        $this->assertEquals('A', $assignment->getSection());
        $this->assertEquals('2024-2025', $assignment->getAcademicYear());
        $this->assertEquals('1st Semester', $assignment->getSemester());
        $this->assertEquals('active', $assignment->getStatus());
        $this->assertEquals('Test assignment', $assignment->getNotes());
    }

    /** @test */
    public function it_should_create_assignment_with_joined_data()
    {
        $assignmentData = [
            'id' => 1,
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment',
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'faculty_name' => 'John Doe'
        ];

        $assignment = new SubjectAssignment($assignmentData);

        $this->assertEquals('CS101', $assignment->getSubjectCode());
        $this->assertEquals('Introduction to Computer Science', $assignment->getSubjectName());
        $this->assertEquals('John Doe', $assignment->getFacultyName());
    }

    /** @test */
    public function it_should_handle_empty_data()
    {
        $assignment = new SubjectAssignment([]);

        $this->assertNull($assignment->getId());
        $this->assertNull($assignment->getSubjectId());
        $this->assertNull($assignment->getFacultyId());
        $this->assertEquals('', $assignment->getYearLevel());
        $this->assertEquals('', $assignment->getSection());
        $this->assertEquals('', $assignment->getAcademicYear());
        $this->assertEquals('', $assignment->getSemester());
        $this->assertEquals('active', $assignment->getStatus());
        $this->assertEquals('', $assignment->getNotes());
        $this->assertEquals('', $assignment->getSubjectCode());
        $this->assertEquals('', $assignment->getSubjectName());
        $this->assertEquals('', $assignment->getFacultyName());
    }

    /** @test */
    public function it_should_convert_to_array()
    {
        $assignmentData = [
            'id' => 1,
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment',
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'faculty_name' => 'John Doe',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ];

        $assignment = new SubjectAssignment($assignmentData);
        $array = $assignment->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(1, $array['id']);
        $this->assertEquals(1, $array['subject_id']);
        $this->assertEquals(2, $array['faculty_id']);
        $this->assertEquals('1st Year', $array['year_level']);
        $this->assertEquals('A', $array['section']);
        $this->assertEquals('2024-2025', $array['academic_year']);
        $this->assertEquals('1st Semester', $array['semester']);
        $this->assertEquals('active', $array['status']);
        $this->assertEquals('Test assignment', $array['notes']);
        $this->assertEquals('CS101', $array['subject_code']);
        $this->assertEquals('Introduction to Computer Science', $array['subject_name']);
        $this->assertEquals('John Doe', $array['faculty_name']);
        $this->assertEquals('2024-01-01 10:00:00', $array['created_at']);
        $this->assertEquals('2024-01-01 10:00:00', $array['updated_at']);
    }

    /** @test */
    public function it_should_validate_assignment_data()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $errors = $assignment->validate();

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    /** @test */
    public function it_should_validate_required_fields()
    {
        $assignment = new SubjectAssignment([]);
        $errors = $assignment->validate();

        $this->assertIsArray($errors);
        $this->assertContains('Subject ID is required', $errors);
        $this->assertContains('Faculty ID is required', $errors);
        $this->assertContains('Year level is required', $errors);
        $this->assertContains('Section is required', $errors);
        $this->assertContains('Academic year is required', $errors);
        $this->assertContains('Semester is required', $errors);
    }

    /** @test */
    public function it_should_validate_year_level_values()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => 'Invalid Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $errors = $assignment->validate();

        $this->assertContains('Invalid year level', $errors);
    }

    /** @test */
    public function it_should_validate_semester_values()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => 'Invalid Semester'
        ]);

        $errors = $assignment->validate();

        $this->assertContains('Invalid semester', $errors);
    }

    /** @test */
    public function it_should_validate_status_values()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'invalid_status'
        ]);

        $errors = $assignment->validate();

        $this->assertContains('Invalid status', $errors);
    }
}