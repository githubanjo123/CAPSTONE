<?php

namespace Tests\Unit\Services\Assignment;

use PHPUnit\Framework\TestCase;
use App\Services\Assignment\AssignmentService;
use App\Models\SubjectAssignment;
use App\DAO\AssignmentDAO;
use App\DAO\SubjectDAO;
use App\DAO\UserDAO;

class AssignmentServiceTest extends TestCase
{
    private $mockAssignmentDAO;
    private $mockSubjectDAO;
    private $mockUserDAO;
    private $assignmentService;

    protected function setUp(): void
    {
        $this->mockAssignmentDAO = $this->createMock(AssignmentDAO::class);
        $this->mockSubjectDAO = $this->createMock(SubjectDAO::class);
        $this->mockUserDAO = $this->createMock(UserDAO::class);
        
        $this->assignmentService = new AssignmentService(
            $this->mockAssignmentDAO,
            $this->mockSubjectDAO,
            $this->mockUserDAO
        );
    }

    /** @test */
    public function it_should_get_all_assignments()
    {
        $mockAssignments = [
            new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]),
            new SubjectAssignment(['id' => 2, 'subject_id' => 2, 'faculty_id' => 3])
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getAll')
            ->willReturn($mockAssignments);

        $result = $this->assignmentService->getAllAssignments();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(SubjectAssignment::class, $result[0]);
    }

    /** @test */
    public function it_should_get_assignment_by_id()
    {
        $mockAssignment = new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($mockAssignment);

        $result = $this->assignmentService->getAssignmentById(1);

        $this->assertInstanceOf(SubjectAssignment::class, $result);
        $this->assertEquals(1, $result->getId());
    }

    /** @test */
    public function it_should_create_assignment_successfully()
    {
        $assignmentData = [
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment'
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->with(1, '1st Year', 'A', '2024-2025', '1st Semester')
            ->willReturn(false);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('create')
            ->willReturn(new SubjectAssignment(array_merge($assignmentData, ['id' => 1])));

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Assignment created successfully.', $result['message']);
    }

    /** @test */
    public function it_should_fail_to_create_duplicate_assignment()
    {
        $assignmentData = [
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment'
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->with(1, '1st Year', 'A', '2024-2025', '1st Semester')
            ->willReturn(true);

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertStringContains('already exists', $result['message']);
    }

    /** @test */
    public function it_should_fail_to_create_assignment_with_invalid_data()
    {
        $assignmentData = [
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => 'Invalid Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment'
        ];

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertStringContains('Validation failed', $result['message']);
    }

    /** @test */
    public function it_should_update_assignment_successfully()
    {
        $assignmentId = 1;
        $assignmentData = [
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Updated assignment'
        ];

        $existingAssignment = new SubjectAssignment(array_merge($assignmentData, ['id' => 1]));

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with($assignmentId)
            ->willReturn($existingAssignment);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->with(1, '1st Year', 'A', '2024-2025', '1st Semester', 1)
            ->willReturn(false);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('update')
            ->willReturn(true);

        $result = $this->assignmentService->updateAssignment($assignmentId, $assignmentData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Assignment updated successfully.', $result['message']);
    }

    /** @test */
    public function it_should_fail_to_update_nonexistent_assignment()
    {
        $assignmentId = 999;
        $assignmentData = [
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Updated assignment'
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with($assignmentId)
            ->willReturn(null);

        $result = $this->assignmentService->updateAssignment($assignmentId, $assignmentData);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('Assignment not found.', $result['message']);
    }

    /** @test */
    public function it_should_delete_assignment_successfully()
    {
        $assignmentId = 1;
        $existingAssignment = new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with($assignmentId)
            ->willReturn($existingAssignment);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('delete')
            ->with($assignmentId)
            ->willReturn(true);

        $result = $this->assignmentService->deleteAssignment($assignmentId);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Assignment deleted successfully.', $result['message']);
    }

    /** @test */
    public function it_should_fail_to_delete_nonexistent_assignment()
    {
        $assignmentId = 999;

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with($assignmentId)
            ->willReturn(null);

        $result = $this->assignmentService->deleteAssignment($assignmentId);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('Assignment not found.', $result['message']);
    }

    /** @test */
    public function it_should_get_assignments_by_filters()
    {
        $filters = ['academic_year' => '2024-2025', 'status' => 'active'];
        $mockAssignments = [
            new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2])
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getByFilters')
            ->with($filters)
            ->willReturn($mockAssignments);

        $result = $this->assignmentService->getAssignmentsByFilters($filters);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(SubjectAssignment::class, $result[0]);
    }

    /** @test */
    public function it_should_get_faculty_workload()
    {
        $facultyId = 2;
        $academicYear = '2024-2025';
        $mockWorkload = [
            new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2])
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getFacultyWorkload')
            ->with($facultyId, $academicYear)
            ->willReturn($mockWorkload);

        $result = $this->assignmentService->getFacultyWorkload($facultyId, $academicYear);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(SubjectAssignment::class, $result[0]);
    }

    /** @test */
    public function it_should_get_unassigned_subjects()
    {
        $academicYear = '2024-2025';
        $semester = '1st Semester';
        $mockSubjects = [
            ['subject_id' => 1, 'subject_code' => 'CS101', 'subject_name' => 'Computer Science']
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getUnassignedSubjects')
            ->with($academicYear, $semester)
            ->willReturn($mockSubjects);

        $result = $this->assignmentService->getUnassignedSubjects($academicYear, $semester);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('CS101', $result[0]['subject_code']);
    }

    /** @test */
    public function it_should_get_assignment_statistics()
    {
        $academicYear = '2024-2025';
        $mockStats = [
            'total_assignments' => 10,
            'active_assignments' => 8,
            'pending_assignments' => 2,
            'inactive_assignments' => 0
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getAssignmentStats')
            ->with($academicYear)
            ->willReturn($mockStats);

        $result = $this->assignmentService->getAssignmentStats($academicYear);

        $this->assertIsArray($result);
        $this->assertEquals(10, $result['total_assignments']);
        $this->assertEquals(8, $result['active_assignments']);
    }

    /** @test */
    public function it_should_get_all_faculty()
    {
        $mockFaculty = [
            ['user_id' => 1, 'full_name' => 'John Doe'],
            ['user_id' => 2, 'full_name' => 'Jane Smith']
        ];

        $this->mockUserDAO->expects($this->once())
            ->method('getUsersByRole')
            ->with('faculty')
            ->willReturn($mockFaculty);

        $result = $this->assignmentService->getAllFaculty();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_should_get_all_subjects()
    {
        $mockSubjects = [
            ['subject_id' => 1, 'subject_code' => 'CS101'],
            ['subject_id' => 2, 'subject_code' => 'CS102']
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getAll')
            ->willReturn($mockSubjects);

        $result = $this->assignmentService->getAllSubjects();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_should_get_year_levels()
    {
        $result = $this->assignmentService->getYearLevels();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('1st Year', $result);
        $this->assertArrayHasKey('2nd Year', $result);
        $this->assertArrayHasKey('3rd Year', $result);
        $this->assertArrayHasKey('4th Year', $result);
    }

    /** @test */
    public function it_should_get_sections()
    {
        $result = $this->assignmentService->getSections();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('A', $result);
        $this->assertArrayHasKey('B', $result);
        $this->assertArrayHasKey('C', $result);
    }

    /** @test */
    public function it_should_get_academic_years()
    {
        $result = $this->assignmentService->getAcademicYears();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_should_get_semesters()
    {
        $result = $this->assignmentService->getSemesters();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('1st Semester', $result);
        $this->assertArrayHasKey('2nd Semester', $result);
        $this->assertArrayHasKey('Summer', $result);
    }

    /** @test */
    public function it_should_get_assignment_statuses()
    {
        $result = $this->assignmentService->getAssignmentStatuses();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('active', $result);
        $this->assertArrayHasKey('inactive', $result);
        $this->assertArrayHasKey('pending', $result);
    }

    /** @test */
    public function it_should_convert_assignments_to_array()
    {
        $assignments = [
            new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]),
            new SubjectAssignment(['id' => 2, 'subject_id' => 2, 'faculty_id' => 3])
        ];

        $result = $this->assignmentService->assignmentsToArray($assignments);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertIsArray($result[0]);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals(2, $result[1]['id']);
    }
}