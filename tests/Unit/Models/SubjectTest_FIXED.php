<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Subject;

class SubjectTest extends TestCase
{
    private Subject $subject;

    protected function setUp(): void
    {
        $this->subject = new Subject([
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic computer science concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ]);
    }

    /**
     * @test
     */
    public function it_should_create_subject_with_valid_data()
    {
        $this->assertEquals(1, $this->subject->getSubjectId());
        $this->assertEquals('CS101', $this->subject->getSubjectCode());
        $this->assertEquals('Introduction to Computer Science', $this->subject->getSubjectName());
        $this->assertEquals('Basic computer science concepts', $this->subject->getDescription());
        $this->assertEquals(3, $this->subject->getUnits());
        $this->assertEquals('1st Year', $this->subject->getYearLevel());
        $this->assertEquals('1st Semester', $this->subject->getSemester());
    }

    /**
     * @test
     */
    public function it_should_create_subject_with_minimal_data()
    {
        $subject = new Subject([
            'subject_code' => 'MATH101',
            'subject_name' => 'College Algebra'
        ]);

        $this->assertEquals('MATH101', $subject->getSubjectCode());
        $this->assertEquals('College Algebra', $subject->getSubjectName());
        $this->assertEquals(3, $subject->getUnits()); // Default value
        $this->assertNull($subject->getSubjectId());
    }

    /**
     * @test
     */
    public function it_should_set_and_get_subject_properties()
    {
        $this->subject->setSubjectCode('CS102');
        $this->subject->setSubjectName('Advanced Programming');
        $this->subject->setDescription('Advanced programming concepts');
        $this->subject->setUnits(4);
        $this->subject->setYearLevel('2nd Year');
        $this->subject->setSemester('2nd Semester');

        $this->assertEquals('CS102', $this->subject->getSubjectCode());
        $this->assertEquals('Advanced Programming', $this->subject->getSubjectName());
        $this->assertEquals('Advanced programming concepts', $this->subject->getDescription());
        $this->assertEquals(4, $this->subject->getUnits());
        $this->assertEquals('2nd Year', $this->subject->getYearLevel());
        $this->assertEquals('2nd Semester', $this->subject->getSemester());
    }

    /**
     * @test
     */
    public function it_should_convert_to_array()
    {
        $array = $this->subject->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(1, $array['subject_id']);
        $this->assertEquals('CS101', $array['subject_code']);
        $this->assertEquals('Introduction to Computer Science', $array['subject_name']);
        $this->assertEquals('Basic computer science concepts', $array['description']);
        $this->assertEquals(3, $array['units']);
        $this->assertEquals('1st Year', $array['year_level']);
        $this->assertEquals('1st Semester', $array['semester']);
    }

    /**
     * @test
     */
    public function it_should_validate_subject_with_valid_data()
    {
        $errors = $this->subject->validate();
        $this->assertEmpty($errors);
    }

    /**
     * @test
     */
    public function it_should_validate_subject_with_missing_subject_code()
    {
        $subject = new Subject([
            'subject_name' => 'Test Subject',
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'units' => 3
        ]);

        $errors = $subject->validate();
        $this->assertContains('Subject code is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_subject_with_missing_subject_name()
    {
        $subject = new Subject([
            'subject_code' => 'TEST101',
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'units' => 3
        ]);

        $errors = $subject->validate();
        $this->assertContains('Subject name is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_subject_with_missing_year_level()
    {
        $subject = new Subject([
            'subject_code' => 'TEST101',
            'subject_name' => 'Test Subject',
            'semester' => '1st Semester',
            'units' => 3
        ]);

        $errors = $subject->validate();
        $this->assertContains('Year level is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_subject_with_missing_semester()
    {
        $subject = new Subject([
            'subject_code' => 'TEST101',
            'subject_name' => 'Test Subject',
            'year_level' => '1st Year',
            'units' => 3
        ]);

        $errors = $subject->validate();
        $this->assertContains('Semester is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_subject_with_invalid_units()
    {
        $subject = new Subject([
            'subject_code' => 'TEST101',
            'subject_name' => 'Test Subject',
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'units' => 0
        ]);

        $errors = $subject->validate();
        $this->assertContains('Units must be greater than 0', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_subject_with_negative_units()
    {
        $subject = new Subject([
            'subject_code' => 'TEST101',
            'subject_name' => 'Test Subject',
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'units' => -1
        ]);

        $errors = $subject->validate();
        $this->assertContains('Units must be greater than 0', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_subject_with_multiple_errors()
    {
        $subject = new Subject([
            'units' => 0
        ]);

        $errors = $subject->validate();
        $this->assertCount(5, $errors); // subject_code, subject_name, year_level, semester, units
        $this->assertContains('Subject code is required', $errors);
        $this->assertContains('Subject name is required', $errors);
        $this->assertContains('Year level is required', $errors);
        $this->assertContains('Semester is required', $errors);
        $this->assertContains('Units must be greater than 0', $errors);
    }

    /**
     * @test
     */
    public function it_should_handle_empty_description()
    {
        $subject = new Subject([
            'subject_code' => 'TEST101',
            'subject_name' => 'Test Subject',
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'units' => 3,
            'description' => ''
        ]);

        $errors = $subject->validate();
        $this->assertEmpty($errors); // Description is optional
        $this->assertEquals('', $subject->getDescription());
    }

    /**
     * @test
     */
    public function it_should_handle_null_values()
    {
        $subject = new Subject([
            'subject_code' => 'TEST101',
            'subject_name' => 'Test Subject',
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'units' => 3,
            'description' => null
        ]);

        $errors = $subject->validate();
        $this->assertEmpty($errors); // Null description is acceptable
        $this->assertEquals('', $subject->getDescription()); // Constructor converts null to empty string
    }
}