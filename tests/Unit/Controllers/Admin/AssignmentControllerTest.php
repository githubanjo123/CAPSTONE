<?php

namespace Tests\Unit\Controllers\Admin;

use PHPUnit\Framework\TestCase;
use App\Controllers\Admin\AssignmentController;
use App\Services\Assignment\AssignmentService;
use App\Services\Auth\AuthService;
use App\Models\SubjectAssignment;

class AssignmentControllerTest extends TestCase
{
    private $mockAssignmentService;
    private $mockAuthService;
    private $assignmentController;

    protected function setUp(): void
    {
        $this->mockAssignmentService = $this->createMock(AssignmentService::class);
        $this->mockAuthService = $this->createMock(AuthService::class);
        
        $this->assignmentController = new AssignmentController(
            $this->mockAuthService,
            $this->mockAssignmentService
        );
    }

    /** @test */
    public function it_should_add_assignment_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment'
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('createAssignment')
            ->with($_POST)
            ->willReturn([
                'success' => true,
                'message' => 'Assignment created successfully.',
                'data' => ['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]
            ]);

        ob_start();
        $this->assignmentController->addAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals('Assignment created successfully.', $response['message']);
    }

    /** @test */
    public function it_should_fail_to_add_assignment_with_invalid_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $this->assignmentController->addAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request method.', $response['message']);
    }

    /** @test */
    public function it_should_edit_assignment_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'assignment_id' => 1,
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Updated assignment'
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('updateAssignment')
            ->with(1, $_POST)
            ->willReturn([
                'success' => true,
                'message' => 'Assignment updated successfully.',
                'data' => ['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]
            ]);

        ob_start();
        $this->assignmentController->editAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals('Assignment updated successfully.', $response['message']);
    }

    /** @test */
    public function it_should_fail_to_edit_assignment_without_id()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Updated assignment'
        ];

        ob_start();
        $this->assignmentController->editAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Assignment ID is required.', $response['message']);
    }

    /** @test */
    public function it_should_delete_assignment_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['assignment_id' => 1];

        $this->mockAssignmentService->expects($this->once())
            ->method('deleteAssignment')
            ->with(1)
            ->willReturn([
                'success' => true,
                'message' => 'Assignment deleted successfully.'
            ]);

        ob_start();
        $this->assignmentController->deleteAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals('Assignment deleted successfully.', $response['message']);
    }

    /** @test */
    public function it_should_fail_to_delete_assignment_without_id()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];

        ob_start();
        $this->assignmentController->deleteAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Assignment ID is required.', $response['message']);
    }

    /** @test */
    public function it_should_get_assignment_by_id()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $assignmentId = 1;
        $mockAssignment = new SubjectAssignment([
            'id' => 1,
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment'
        ]);

        $this->mockAssignmentService->expects($this->once())
            ->method('getAssignmentById')
            ->with($assignmentId)
            ->willReturn($mockAssignment);

        ob_start();
        $this->assignmentController->getAssignment($assignmentId);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertIsArray($response['data']);
        $this->assertEquals(1, $response['data']['id']);
    }

    /** @test */
    public function it_should_return_error_when_assignment_not_found()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $assignmentId = 999;

        $this->mockAssignmentService->expects($this->once())
            ->method('getAssignmentById')
            ->with($assignmentId)
            ->willReturn(null);

        ob_start();
        $this->assignmentController->getAssignment($assignmentId);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Assignment not found.', $response['message']);
    }

    /** @test */
    public function it_should_get_assignments_by_filters()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ];

        $mockAssignments = [
            new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2])
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('getAssignmentsByFilters')
            ->with($_GET)
            ->willReturn($mockAssignments);

        $this->mockAssignmentService->expects($this->once())
            ->method('assignmentsToArray')
            ->with($mockAssignments)
            ->willReturn([['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]]);

        ob_start();
        $this->assignmentController->getAssignmentsByFilters();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertIsArray($response['data']);
        $this->assertCount(1, $response['data']);
    }

    /** @test */
    public function it_should_get_faculty_workload()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['faculty_id' => 2, 'academic_year' => '2024-2025'];

        $mockWorkload = [
            new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2])
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('getFacultyWorkload')
            ->with(2, '2024-2025')
            ->willReturn($mockWorkload);

        $this->mockAssignmentService->expects($this->once())
            ->method('assignmentsToArray')
            ->with($mockWorkload)
            ->willReturn([['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]]);

        ob_start();
        $this->assignmentController->getFacultyWorkload();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertIsArray($response['data']);
        $this->assertCount(1, $response['data']);
    }

    /** @test */
    public function it_should_get_unassigned_subjects()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['academic_year' => '2024-2025', 'semester' => '1st Semester'];

        $mockSubjects = [
            ['subject_id' => 1, 'subject_code' => 'CS101', 'subject_name' => 'Computer Science']
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('getUnassignedSubjects')
            ->with('2024-2025', '1st Semester')
            ->willReturn($mockSubjects);

        ob_start();
        $this->assignmentController->getUnassignedSubjects();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertIsArray($response['data']);
        $this->assertCount(1, $response['data']);
        $this->assertEquals('CS101', $response['data'][0]['subject_code']);
    }

    /** @test */
    public function it_should_refresh_assignments_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $assignments = [
            new SubjectAssignment(['id' => 1, 'subject_id' => 1, 'faculty_id' => 2])
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('getAllAssignments')
            ->willReturn($assignments);

        $this->mockAssignmentService->expects($this->once())
            ->method('assignmentsToArray')
            ->with($assignments)
            ->willReturn([['id' => 1, 'subject_id' => 1, 'faculty_id' => 2]]);

        ob_start();
        $this->assignmentController->refreshAssignments();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertIsArray($response['data']);
    }

    /** @test */
    public function it_should_get_assignment_statistics()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['academic_year' => '2024-2025'];

        $mockStats = [
            'total_assignments' => 10,
            'active_assignments' => 8,
            'pending_assignments' => 2,
            'inactive_assignments' => 0
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('getAssignmentStats')
            ->with('2024-2025')
            ->willReturn($mockStats);

        ob_start();
        $this->assignmentController->getAssignmentStats();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertIsArray($response['data']);
        $this->assertEquals(10, $response['data']['total_assignments']);
        $this->assertEquals(8, $response['data']['active_assignments']);
    }
}