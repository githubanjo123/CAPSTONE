<?php

namespace Tests\Unit\Controllers\Admin;

use PHPUnit\Framework\TestCase;
use App\Controllers\Admin\AssignmentController;
use App\Services\Auth\AuthService;
use App\Services\Assignment\AssignmentService;
use App\Core\View;
use App\Models\SubjectAssignment;

class AssignmentControllerTest extends TestCase
{
    private AssignmentController $assignmentController;
    private AuthService $mockAuthService;
    private AssignmentService $mockAssignmentService;
    private View $mockView;

    protected function setUp(): void
    {
        $this->mockAuthService = $this->createMock(AuthService::class);
        $this->mockAssignmentService = $this->createMock(AssignmentService::class);
        $this->mockView = $this->createMock(View::class);

        // Mock the authentication methods to prevent actual calls
        $this->mockAuthService->method('requireAuth')->willReturn(null);
        $this->mockAuthService->method('requireRole')->willReturn(null);

        $this->assignmentController = new AssignmentController(
            $this->mockAuthService,
            $this->mockAssignmentService,
            $this->mockView
        );
    }

    /**
     * @test
     */
    public function it_should_require_authentication_and_admin_role()
    {
        $mockAuthService = $this->createMock(AuthService::class);
        $mockAuthService->expects($this->once())
            ->method('requireAuth');

        $mockAuthService->expects($this->once())
            ->method('requireRole')
            ->with('admin');

        new AssignmentController($mockAuthService);
    }

    /**
     * @test
     */
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

        $expectedResult = [
            'success' => true,
            'message' => 'Assignment created successfully.',
            'data' => new SubjectAssignment(['id' => 5])
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('createAssignment')
            ->with($_POST)
            ->willReturn($expectedResult);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->addAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Assignment created successfully.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_handle_add_assignment_failure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_id' => '',
            'faculty_id' => 2
        ];

        $expectedResult = [
            'success' => false,
            'message' => 'Validation failed: Subject is required'
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('createAssignment')
            ->with($_POST)
            ->willReturn($expectedResult);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->addAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Validation failed: Subject is required', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_reject_add_assignment_with_invalid_request_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->mockAssignmentService->expects($this->never())
            ->method('createAssignment');

        // Capture the JSON response
        ob_start();
        $this->assignmentController->addAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request method.', $response['message']);
    }

    /**
     * @test
     */
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
            'status' => 'inactive',
            'notes' => 'Updated assignment'
        ];

        $expectedResult = [
            'success' => true,
            'message' => 'Assignment updated successfully.',
            'data' => new SubjectAssignment(['id' => 1])
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('updateAssignment')
            ->with(1, $_POST)
            ->willReturn($expectedResult);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->editAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Assignment updated successfully.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_handle_edit_assignment_failure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'assignment_id' => 999,
            'subject_id' => 1,
            'faculty_id' => 2
        ];

        $expectedResult = [
            'success' => false,
            'message' => 'Assignment not found.'
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('updateAssignment')
            ->with(999, $_POST)
            ->willReturn($expectedResult);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->editAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Assignment not found.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_reject_edit_assignment_with_missing_assignment_id()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_id' => 1,
            'faculty_id' => 2
        ];

        $this->mockAssignmentService->expects($this->never())
            ->method('updateAssignment');

        // Capture the JSON response
        ob_start();
        $this->assignmentController->editAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Assignment ID is required.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_delete_assignment_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['assignment_id' => 1];

        $expectedResult = [
            'success' => true,
            'message' => 'Assignment deleted successfully.'
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('deleteAssignment')
            ->with(1)
            ->willReturn($expectedResult);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->deleteAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Assignment deleted successfully.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_handle_delete_assignment_failure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['assignment_id' => 1];

        $expectedResult = [
            'success' => false,
            'message' => 'Assignment not found.'
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('deleteAssignment')
            ->with(1)
            ->willReturn($expectedResult);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->deleteAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Assignment not found.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_reject_delete_assignment_with_missing_assignment_id()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];

        $this->mockAssignmentService->expects($this->never())
            ->method('deleteAssignment');

        // Capture the JSON response
        ob_start();
        $this->assignmentController->deleteAssignment();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Assignment ID is required.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_get_assignment_by_id_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $assignmentId = 1;

        $assignmentData = new SubjectAssignment([
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
            ->with(1)
            ->willReturn($assignmentData);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->getAssignment($assignmentId);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertInstanceOf(SubjectAssignment::class, $response['data']);
    }

    /**
     * @test
     */
    public function it_should_return_error_when_assignment_not_found_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $assignmentId = 999;

        $this->mockAssignmentService->expects($this->once())
            ->method('getAssignmentById')
            ->with(999)
            ->willReturn(null);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->getAssignment($assignmentId);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Assignment not found.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_get_assignments_by_filters_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [
            'year_level' => '1st Year',
            'status' => 'active'
        ];

        $filterResults = [
            new SubjectAssignment(['id' => 1, 'year_level' => '1st Year', 'status' => 'active'])
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('getAssignmentsByFilters')
            ->with($_GET)
            ->willReturn($filterResults);

        $this->mockAssignmentService->expects($this->once())
            ->method('assignmentsToArray')
            ->with($filterResults)
            ->willReturn([$filterResults[0]->toArray()]);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->getAssignmentsByFilters();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertIsArray($response['data']);
    }

    /**
     * @test
     */
    public function it_should_get_faculty_workload_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [
            'faculty_id' => 2,
            'academic_year' => '2024-2025'
        ];

        $workload = [
            new SubjectAssignment(['id' => 1, 'faculty_id' => 2, 'status' => 'active'])
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('getFacultyWorkload')
            ->with(2, '2024-2025')
            ->willReturn($workload);

        $this->mockAssignmentService->expects($this->once())
            ->method('assignmentsToArray')
            ->with($workload)
            ->willReturn([$workload[0]->toArray()]);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->getFacultyWorkload();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertIsArray($response['data']);
    }

    /**
     * @test
     */
    public function it_should_return_error_when_faculty_id_missing_for_workload()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['academic_year' => '2024-2025'];

        $this->mockAssignmentService->expects($this->never())
            ->method('getFacultyWorkload');

        // Capture the JSON response
        ob_start();
        $this->assignmentController->getFacultyWorkload();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Faculty ID is required.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_get_unassigned_subjects_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ];

        $subjects = [
            ['subject_id' => 3, 'subject_code' => 'PHYS101', 'subject_name' => 'Physics']
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('getUnassignedSubjects')
            ->with('2024-2025', '1st Semester')
            ->willReturn($subjects);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->getUnassignedSubjects();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals($subjects, $response['data']);
    }

    /**
     * @test
     */
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
            ->willReturn([$assignments[0]->toArray()]);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->refreshAssignments();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertIsArray($response['data']);
    }

    /**
     * @test
     */
    public function it_should_get_assignment_stats_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['academic_year' => '2024-2025'];

        $stats = [
            'total_assignments' => 10,
            'active_assignments' => 8,
            'pending_assignments' => 2
        ];

        $this->mockAssignmentService->expects($this->once())
            ->method('getAssignmentStats')
            ->with('2024-2025')
            ->willReturn($stats);

        // Capture the JSON response
        ob_start();
        $this->assignmentController->getAssignmentStats();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals($stats, $response['data']);
    }

    /**
     * @test
     */
    public function it_should_reject_ajax_requests_with_invalid_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->mockAssignmentService->expects($this->never())
            ->method('getAssignmentById');

        // Capture the JSON response
        ob_start();
        $this->assignmentController->getAssignment(1);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request method.', $response['message']);
    }

    protected function tearDown(): void
    {
        // Clean up global variables
        unset($_SERVER['REQUEST_METHOD']);
        unset($_POST);
        unset($_GET);
    }
}