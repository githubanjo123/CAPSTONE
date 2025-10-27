<?php

namespace Tests\Unit\Services\Subject;

use PHPUnit\Framework\TestCase;
use App\Services\Subject\SubjectService;
use App\Services\Auth\AuthService;
use App\DAO\SubjectDAO;
use App\Models\Subject;

class SubjectServiceTest extends TestCase
{
    private SubjectService $subjectService;
    private SubjectDAO $mockSubjectDAO;

    protected function setUp(): void
    {
        $this->mockSubjectDAO = $this->createMock(SubjectDAO::class);
        $this->subjectService = new SubjectService($this->mockSubjectDAO);
    }

    /**
     * @test
     */
    public function it_should_get_all_subjects()
    {
        $mockSubjects = [
            new Subject([
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'description' => 'Basic concepts',
                'units' => 3,
                'year_level' => '1st Year',
                'semester' => '1st Semester'
            ]),
            new Subject([
                'subject_id' => 2,
                'subject_code' => 'MATH101',
                'subject_name' => 'College Algebra',
                'description' => 'Algebraic concepts',
                'units' => 3,
                'year_level' => '1st Year',
                'semester' => '1st Semester'
            ])
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getAll')
            ->willReturn($mockSubjects);

        $result = $this->subjectService->getAllSubjects();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('CS101', $result[0]['subject_code']);
        $this->assertEquals('MATH101', $result[1]['subject_code']);
    }

    /**
     * @test
     */
    public function it_should_get_subject_by_id()
    {
        $mockSubject = new Subject([
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ]);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($mockSubject);

        $result = $this->subjectService->getSubjectById(1);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['subject_id']);
        $this->assertEquals('CS101', $result['subject_code']);
    }

    /**
     * @test
     */
    public function it_should_return_null_when_subject_not_found()
    {
        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willReturn(null);

        $result = $this->subjectService->getSubjectById(999);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_should_create_subject_successfully()
    {
        $subjectData = [
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getByCode')
            ->with('CS101')
            ->willReturn(null);

        $this->mockSubjectDAO->expects($this->once())
            ->method('create')
            ->willReturn(5);

        $result = $this->subjectService->createSubject($subjectData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Subject created successfully', $result['message']);
        $this->assertEquals(5, $result['subject_id']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_subject_with_missing_required_fields()
    {
        $subjectData = [
            'subject_code' => '',
            'subject_name' => '',
            'description' => 'Basic concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $result = $this->subjectService->createSubject($subjectData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Subject code and name are required', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_subject_with_duplicate_code()
    {
        $subjectData = [
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $existingSubject = new Subject($subjectData);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getByCode')
            ->with('CS101')
            ->willReturn($existingSubject);

        $result = $this->subjectService->createSubject($subjectData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Subject code already exists', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_subject_with_invalid_data()
    {
        $subjectData = [
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic concepts',
            'units' => 0, // Invalid units
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getByCode')
            ->with('CS101')
            ->willReturn(null);

        $result = $this->subjectService->createSubject($subjectData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Units must be greater than 0', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_update_subject_successfully()
    {
        $existingSubject = new Subject([
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ]);

        $updateData = [
            'subject_code' => 'CS101',
            'subject_name' => 'Updated Computer Science',
            'description' => 'Updated concepts',
            'units' => 4,
            'year_level' => '2nd Year',
            'semester' => '2nd Semester'
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($existingSubject);

        $this->mockSubjectDAO->expects($this->once())
            ->method('update')
            ->willReturn(true);

        $result = $this->subjectService->updateSubject(1, $updateData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Subject updated successfully', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_update_nonexistent_subject()
    {
        $updateData = [
            'subject_code' => 'CS101',
            'subject_name' => 'Updated Computer Science'
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willReturn(null);

        $result = $this->subjectService->updateSubject(999, $updateData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Subject not found', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_update_subject_with_duplicate_code()
    {
        $existingSubject = new Subject([
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science'
        ]);

        $updateData = [
            'subject_code' => 'CS102', // Different code
            'subject_name' => 'Updated Computer Science'
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($existingSubject);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getByCode')
            ->with('CS102')
            ->willReturn(new Subject(['subject_code' => 'CS102']));

        $result = $this->subjectService->updateSubject(1, $updateData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Subject code already exists', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_delete_subject_successfully()
    {
        $existingSubject = new Subject([
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science'
        ]);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($existingSubject);

        $this->mockSubjectDAO->expects($this->once())
            ->method('hasFacultyAssignments')
            ->with(1)
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('hasExams')
            ->with(1)
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $result = $this->subjectService->deleteSubject(1);

        $this->assertTrue($result['success']);
        $this->assertEquals('Subject deleted successfully', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_delete_nonexistent_subject()
    {
        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willReturn(null);

        $result = $this->subjectService->deleteSubject(999);

        $this->assertFalse($result['success']);
        $this->assertEquals('Subject not found', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_delete_subject_with_faculty_assignments()
    {
        $existingSubject = new Subject([
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science'
        ]);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($existingSubject);

        $this->mockSubjectDAO->expects($this->once())
            ->method('hasFacultyAssignments')
            ->with(1)
            ->willReturn(true);

        $result = $this->subjectService->deleteSubject(1);

        $this->assertFalse($result['success']);
        $this->assertEquals('Cannot delete subject. It is currently assigned to faculty members.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_delete_subject_with_exams()
    {
        $existingSubject = new Subject([
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science'
        ]);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($existingSubject);

        $this->mockSubjectDAO->expects($this->once())
            ->method('hasFacultyAssignments')
            ->with(1)
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('hasExams')
            ->with(1)
            ->willReturn(true);

        $result = $this->subjectService->deleteSubject(1);

        $this->assertFalse($result['success']);
        $this->assertEquals('Cannot delete subject. It has associated exams.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_search_subjects()
    {
        $mockSubjects = [
            new Subject([
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science'
            ])
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('search')
            ->with('computer')
            ->willReturn($mockSubjects);

        $result = $this->subjectService->searchSubjects('computer');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('CS101', $result[0]['subject_code']);
    }

    /**
     * @test
     */
    public function it_should_get_subjects_by_year_level()
    {
        $mockSubjects = [
            new Subject([
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'year_level' => '1st Year'
            ])
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getByYearLevel')
            ->with('1st Year')
            ->willReturn($mockSubjects);

        $result = $this->subjectService->getSubjectsByYearLevel('1st Year');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('1st Year', $result[0]['year_level']);
    }

    /**
     * @test
     */
    public function it_should_get_subjects_by_semester()
    {
        $mockSubjects = [
            new Subject([
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'semester' => '1st Semester'
            ])
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getBySemester')
            ->with('1st Semester')
            ->willReturn($mockSubjects);

        $result = $this->subjectService->getSubjectsBySemester('1st Semester');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('1st Semester', $result[0]['semester']);
    }

    /**
     * @test
     */
    public function it_should_get_year_levels()
    {
        $yearLevels = $this->subjectService->getYearLevels();

        $this->assertIsArray($yearLevels);
        $this->assertArrayHasKey('1st Year', $yearLevels);
        $this->assertArrayHasKey('2nd Year', $yearLevels);
        $this->assertArrayHasKey('3rd Year', $yearLevels);
        $this->assertArrayHasKey('4th Year', $yearLevels);
        $this->assertEquals('1st Year', $yearLevels['1st Year']);
    }

    /**
     * @test
     */
    public function it_should_get_semesters()
    {
        $semesters = $this->subjectService->getSemesters();

        $this->assertIsArray($semesters);
        $this->assertArrayHasKey('1st Semester', $semesters);
        $this->assertArrayHasKey('2nd Semester', $semesters);
        $this->assertArrayHasKey('Summer', $semesters);
        $this->assertEquals('1st Semester', $semesters['1st Semester']);
    }

    /**
     * @test
     */
    public function it_should_handle_database_exception_during_create()
    {
        $subjectData = [
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getByCode')
            ->willThrowException(new \Exception('Database connection failed'));

        $result = $this->subjectService->createSubject($subjectData);

        $this->assertFalse($result['success']);
        $this->assertEquals('An error occurred while creating the subject', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_handle_database_exception_during_update()
    {
        $existingSubject = new Subject([
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science'
        ]);

        $updateData = [
            'subject_code' => 'CS101',
            'subject_name' => 'Updated Computer Science'
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->willThrowException(new \Exception('Database connection failed'));

        $result = $this->subjectService->updateSubject(1, $updateData);

        $this->assertFalse($result['success']);
        $this->assertEquals('An error occurred while updating the subject', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_handle_database_exception_during_delete()
    {
        $existingSubject = new Subject([
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science'
        ]);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->willThrowException(new \Exception('Database connection failed'));

        $result = $this->subjectService->deleteSubject(1);

        $this->assertFalse($result['success']);
        $this->assertEquals('An error occurred while deleting the subject', $result['message']);
    }
}