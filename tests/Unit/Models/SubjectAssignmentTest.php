<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\SubjectAssignment;

class SubjectAssignmentTest extends TestCase
{
    private SubjectAssignment $assignment;

    protected function setUp(): void
    {
        $this->assignment = new SubjectAssignment([
            'id' => 1,
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00',
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'faculty_name' => 'John Doe'
        ]);
    }

    /**
     * @test
     */
    public function it_should_create_assignment_with_valid_data()
    {
        $this->assertEquals(1, $this->assignment->getId());
        $this->assertEquals(1, $this->assignment->getSubjectId());
        $this->assertEquals(2, $this->assignment->getFacultyId());
        $this->assertEquals('1st Year', $this->assignment->getYearLevel());
        $this->assertEquals('A', $this->assignment->getSection());
        $this->assertEquals('2024-2025', $this->assignment->getAcademicYear());
        $this->assertEquals('1st Semester', $this->assignment->getSemester());
        $this->assertEquals('active', $this->assignment->getStatus());
        $this->assertEquals('Test assignment', $this->assignment->getNotes());
        $this->assertEquals('CS101', $this->assignment->getSubjectCode());
        $this->assertEquals('Introduction to Computer Science', $this->assignment->getSubjectName());
        $this->assertEquals('John Doe', $this->assignment->getFacultyName());
    }

    /**
     * @test
     */
    public function it_should_create_assignment_with_minimal_data()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $this->assertEquals(1, $assignment->getSubjectId());
        $this->assertEquals(2, $assignment->getFacultyId());
        $this->assertEquals('1st Year', $assignment->getYearLevel());
        $this->assertEquals('A', $assignment->getSection());
        $this->assertEquals('2024-2025', $assignment->getAcademicYear());
        $this->assertEquals('1st Semester', $assignment->getSemester());
        $this->assertEquals('active', $assignment->getStatus()); // Default value
        $this->assertEquals('', $assignment->getNotes()); // Default value
    }

    /**
     * @test
     */
    public function it_should_set_and_get_assignment_properties()
    {
        $this->assignment->setSubjectId(3);
        $this->assignment->setFacultyId(4);
        $this->assignment->setYearLevel('2nd Year');
        $this->assignment->setSection('B');
        $this->assignment->setAcademicYear('2025-2026');
        $this->assignment->setSemester('2nd Semester');
        $this->assignment->setStatus('inactive');
        $this->assignment->setNotes('Updated notes');

        $this->assertEquals(3, $this->assignment->getSubjectId());
        $this->assertEquals(4, $this->assignment->getFacultyId());
        $this->assertEquals('2nd Year', $this->assignment->getYearLevel());
        $this->assertEquals('B', $this->assignment->getSection());
        $this->assertEquals('2025-2026', $this->assignment->getAcademicYear());
        $this->assertEquals('2nd Semester', $this->assignment->getSemester());
        $this->assertEquals('inactive', $this->assignment->getStatus());
        $this->assertEquals('Updated notes', $this->assignment->getNotes());
    }

    /**
     * @test
     */
    public function it_should_convert_to_array()
    {
        $array = $this->assignment->toArray();

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
    }

    /**
     * @test
     */
    public function it_should_validate_assignment_with_valid_data()
    {
        $errors = $this->assignment->validate();
        $this->assertEmpty($errors);
    }

    /**
     * @test
     */
    public function it_should_validate_assignment_with_missing_subject_id()
    {
        $assignment = new SubjectAssignment([
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Subject is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_assignment_with_missing_faculty_id()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Faculty is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_assignment_with_missing_year_level()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Year level is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_assignment_with_missing_section()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Section is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_assignment_with_missing_academic_year()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'semester' => '1st Semester'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Academic year is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_assignment_with_missing_semester()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Semester is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_assignment_with_invalid_status()
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
        $this->assertContains('Status must be active, inactive, or pending', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_assignment_with_multiple_errors()
    {
        $assignment = new SubjectAssignment([
            'status' => 'invalid_status'
        ]);

        $errors = $assignment->validate();
        $this->assertCount(7, $errors); // subject_id, faculty_id, year_level, section, academic_year, semester, status
        $this->assertContains('Subject is required', $errors);
        $this->assertContains('Faculty is required', $errors);
        $this->assertContains('Year level is required', $errors);
        $this->assertContains('Section is required', $errors);
        $this->assertContains('Academic year is required', $errors);
        $this->assertContains('Semester is required', $errors);
        $this->assertContains('Status must be active, inactive, or pending', $errors);
    }

    /**
     * @test
     */
    public function it_should_check_if_assignment_is_active()
    {
        $this->assertTrue($this->assignment->isActive());

        $this->assignment->setStatus('inactive');
        $this->assertFalse($this->assignment->isActive());

        $this->assignment->setStatus('pending');
        $this->assertFalse($this->assignment->isActive());
    }

    /**
     * @test
     */
    public function it_should_generate_assignment_key()
    {
        $key = $this->assignment->getAssignmentKey();
        $expectedKey = '1_1st Year_A_2024-2025_1st Semester';
        $this->assertEquals($expectedKey, $key);
    }

    /**
     * @test
     */
    public function it_should_handle_empty_notes()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'notes' => ''
        ]);

        $errors = $assignment->validate();
        $this->assertEmpty($errors); // Notes are optional
        $this->assertEquals('', $assignment->getNotes());
    }

    /**
     * @test
     */
    public function it_should_handle_null_values()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'notes' => null
        ]);

        $errors = $assignment->validate();
        $this->assertEmpty($errors); // Null notes are acceptable
        $this->assertEquals('', $assignment->getNotes()); // Constructor converts null to empty string
    }

    /**
     * @test
     */
    public function it_should_accept_valid_status_values()
    {
        $validStatuses = ['active', 'inactive', 'pending'];

        foreach ($validStatuses as $status) {
            $assignment = new SubjectAssignment([
                'subject_id' => 1,
                'faculty_id' => 2,
                'year_level' => '1st Year',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => $status
            ]);

            $errors = $assignment->validate();
            $this->assertEmpty($errors, "Status '{$status}' should be valid");
        }
    }
}