<?php

namespace Tests\Unit\Services\Assignment;

use PHPUnit\Framework\TestCase;
use App\Services\Assignment\AssignmentService;
use App\DAO\AssignmentDAO;
use App\DAO\SubjectDAO;
use App\DAO\Auth\UserDAO;
use App\Models\SubjectAssignment;
use App\Models\Subject;
use App\Models\User;

class AssignmentServiceTest extends TestCase
{
    private AssignmentService $assignmentService;
    private AssignmentDAO $mockAssignmentDAO;
    private SubjectDAO $mockSubjectDAO;
    private UserDAO $mockUserDAO;

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

    /**
     * @test
     */
    public function it_should_get_all_assignments()
    {
        $expectedAssignments = [
            new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]),
            new SubjectAssignment(['id' => 2, 'subject_id' => 2, 'faculty_id' => 3])
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getAll')
            ->willReturn($expectedAssignments);

        $result = $this->assignmentService->getAllAssignments();

        $this->assertEquals($expectedAssignments, $result);
    }

    /**
     * @test
     */
    public function it_should_get_assignment_by_id()
    {
        $expectedAssignment = new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($expectedAssignment);

        $result = $this->assignmentService->getAssignmentById(1);

        $this->assertEquals($expectedAssignment, $result);
    }

    /**
     * @test
     */
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

        $subject = new Subject(['subject_id' => 1, 'subject_code' => 'CS101']);
        $faculty = new User(['user_id' => 2, 'role' => 'faculty']);
        $createdAssignment = new SubjectAssignment(array_merge($assignmentData, ['id' => 5]));

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->with(1, '1st Year', 'A', '2024-2025', '1st Semester')
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($subject);

        $this->mockUserDAO->expects($this->once())
            ->method('findById')
            ->with(2)
            ->willReturn($faculty);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('create')
            ->willReturn($createdAssignment);

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Assignment created successfully.', $result['message']);
        $this->assertEquals($createdAssignment, $result['data']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_assignment_with_validation_errors()
    {
        $assignmentData = [
            'subject_id' => '', // Invalid - empty
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ];

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertFalse($result['success']);
        $this->assertStringContains('Validation failed', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_assignment_when_already_exists()
    {
        $assignmentData = [
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->with(1, '1st Year', 'A', '2024-2025', '1st Semester')
            ->willReturn(true);

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertFalse($result['success']);
        $this->assertStringContains('Assignment already exists', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_assignment_when_subject_not_found()
    {
        $assignmentData = [
            'subject_id' => 999,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willReturn(null);

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Subject not found.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_assignment_when_faculty_not_found()
    {
        $assignmentData = [
            'subject_id' => 1,
            'faculty_id' => 999,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ];

        $subject = new Subject(['subject_id' => 1, 'subject_code' => 'CS101']);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($subject);

        $this->mockUserDAO->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Faculty not found or invalid faculty member.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_assignment_when_faculty_is_not_faculty_role()
    {
        $assignmentData = [
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ];

        $subject = new Subject(['subject_id' => 1, 'subject_code' => 'CS101']);
        $user = new User(['user_id' => 2, 'role' => 'student']); // Wrong role

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($subject);

        $this->mockUserDAO->expects($this->once())
            ->method('findById')
            ->with(2)
            ->willReturn($user);

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Faculty not found or invalid faculty member.', $result['message']);
    }

    /**
     * @test
     */
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
            'status' => 'inactive',
            'notes' => 'Updated assignment'
        ];

        $existingAssignment = new SubjectAssignment(array_merge($assignmentData, ['id' => 1]));
        $subject = new Subject(['subject_id' => 1, 'subject_code' => 'CS101']);
        $faculty = new User(['user_id' => 2, 'role' => 'faculty']);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($existingAssignment);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->with(1, '1st Year', 'A', '2024-2025', '1st Semester', 1)
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($subject);

        $this->mockUserDAO->expects($this->once())
            ->method('findById')
            ->with(2)
            ->willReturn($faculty);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('update')
            ->willReturn(true);

        $result = $this->assignmentService->updateAssignment($assignmentId, $assignmentData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Assignment updated successfully.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_update_assignment_when_not_found()
    {
        $assignmentId = 999;
        $assignmentData = [
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willReturn(null);

        $result = $this->assignmentService->updateAssignment($assignmentId, $assignmentData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Assignment not found.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_delete_assignment_successfully()
    {
        $assignmentId = 1;
        $existingAssignment = new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($existingAssignment);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $result = $this->assignmentService->deleteAssignment($assignmentId);

        $this->assertTrue($result['success']);
        $this->assertEquals('Assignment deleted successfully.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_delete_assignment_when_not_found()
    {
        $assignmentId = 999;

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willReturn(null);

        $result = $this->assignmentService->deleteAssignment($assignmentId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Assignment not found.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_get_assignments_by_filters()
    {
        $filters = ['year_level' => '1st Year', 'status' => 'active'];
        $expectedAssignments = [
            new SubjectAssignment(['id' => 1, 'year_level' => '1st Year', 'status' => 'active'])
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getByFilters')
            ->with($filters)
            ->willReturn($expectedAssignments);

        $result = $this->assignmentService->getAssignmentsByFilters($filters);

        $this->assertEquals($expectedAssignments, $result);
    }

    /**
     * @test
     */
    public function it_should_get_faculty_workload()
    {
        $facultyId = 2;
        $academicYear = '2024-2025';
        $expectedAssignments = [
            new SubjectAssignment(['id' => 1, 'faculty_id' => 2, 'status' => 'active'])
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getFacultyWorkload')
            ->with($facultyId, $academicYear)
            ->willReturn($expectedAssignments);

        $result = $this->assignmentService->getFacultyWorkload($facultyId, $academicYear);

        $this->assertEquals($expectedAssignments, $result);
    }

    /**
     * @test
     */
    public function it_should_get_unassigned_subjects()
    {
        $academicYear = '2024-2025';
        $semester = '1st Semester';
        $expectedSubjects = [
            ['subject_id' => 3, 'subject_code' => 'PHYS101', 'subject_name' => 'Physics']
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getUnassignedSubjects')
            ->with($academicYear, $semester)
            ->willReturn($expectedSubjects);

        $result = $this->assignmentService->getUnassignedSubjects($academicYear, $semester);

        $this->assertEquals($expectedSubjects, $result);
    }

    /**
     * @test
     */
    public function it_should_get_assignment_stats()
    {
        $academicYear = '2024-2025';
        $expectedStats = [
            'total_assignments' => 10,
            'active_assignments' => 8,
            'pending_assignments' => 2
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getAssignmentStats')
            ->with($academicYear)
            ->willReturn($expectedStats);

        $result = $this->assignmentService->getAssignmentStats($academicYear);

        $this->assertEquals($expectedStats, $result);
    }

    /**
     * @test
     */
    public function it_should_get_all_faculty()
    {
        $expectedFaculty = [
            new User(['user_id' => 1, 'role' => 'faculty', 'full_name' => 'John Doe']),
            new User(['user_id' => 2, 'role' => 'faculty', 'full_name' => 'Jane Smith'])
        ];

        $this->mockUserDAO->expects($this->once())
            ->method('getUsersByRole')
            ->with('faculty')
            ->willReturn($expectedFaculty);

        $result = $this->assignmentService->getAllFaculty();

        $this->assertEquals($expectedFaculty, $result);
    }

    /**
     * @test
     */
    public function it_should_get_all_subjects()
    {
        $expectedSubjects = [
            new Subject(['subject_id' => 1, 'subject_code' => 'CS101']),
            new Subject(['subject_id' => 2, 'subject_code' => 'MATH101'])
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getAll')
            ->willReturn($expectedSubjects);

        $result = $this->assignmentService->getAllSubjects();

        $this->assertEquals($expectedSubjects, $result);
    }

    /**
     * @test
     */
    public function it_should_get_year_levels()
    {
        $result = $this->assignmentService->getYearLevels();

        $expected = [
            '1st Year' => '1st Year',
            '2nd Year' => '2nd Year',
            '3rd Year' => '3rd Year',
            '4th Year' => '4th Year'
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_should_get_sections()
    {
        $result = $this->assignmentService->getSections();

        $expected = [
            'A' => 'Section A',
            'B' => 'Section B',
            'C' => 'Section C',
            'D' => 'Section D',
            'E' => 'Section E',
            'F' => 'Section F'
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_should_get_academic_years()
    {
        $result = $this->assignmentService->getAcademicYears();

        $this->assertIsArray($result);
        $this->assertCount(5, $result); // Current year + 4 previous years
        
        // Check that it contains current academic year
        $currentYear = date('Y');
        $currentAcademicYear = $currentYear . '-' . ($currentYear + 1);
        $this->assertArrayHasKey($currentAcademicYear, $result);
    }

    /**
     * @test
     */
    public function it_should_get_semesters()
    {
        $result = $this->assignmentService->getSemesters();

        $expected = [
            '1st Semester' => '1st Semester',
            '2nd Semester' => '2nd Semester',
            'Summer' => 'Summer'
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_should_get_assignment_statuses()
    {
        $result = $this->assignmentService->getAssignmentStatuses();

        $expected = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending' => 'Pending'
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
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