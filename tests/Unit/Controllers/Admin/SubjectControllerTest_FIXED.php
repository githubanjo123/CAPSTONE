<?php

namespace Tests\Unit\Controllers\Admin;

use PHPUnit\Framework\TestCase;
use App\Controllers\Admin\SubjectController;
use App\Services\Auth\AuthService;
use App\Services\Subject\SubjectService;
use App\Core\View;
use App\Models\Subject;

class SubjectControllerTest extends TestCase
{
    private SubjectController $subjectController;
    private AuthService $mockAuthService;
    private SubjectService $mockSubjectService;
    private View $mockView;

    protected function setUp(): void
    {
        $this->mockAuthService = $this->createMock(AuthService::class);
        $this->mockSubjectService = $this->createMock(SubjectService::class);
        $this->mockView = $this->createMock(View::class);

        // Mock the authentication methods to prevent actual calls
        $this->mockAuthService->method('requireAuth')->willReturn(null);
        $this->mockAuthService->method('requireRole')->willReturn(null);

        $this->subjectController = new SubjectController(
            $this->mockAuthService,
            $this->mockSubjectService,
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

        new SubjectController($mockAuthService);
    }

    /**
     * @test
     */
    public function it_should_display_subject_management_page()
    {
        $currentUser = ['user_id' => 1, 'full_name' => 'Admin User'];
        $subjects = [
            [
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science'
            ]
        ];
        $yearLevels = ['1st Year' => '1st Year', '2nd Year' => '2nd Year'];
        $semesters = ['1st Semester' => '1st Semester', '2nd Semester' => '2nd Semester'];

        $this->mockAuthService->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($currentUser);

        $this->mockSubjectService->expects($this->once())
            ->method('getAllSubjects')
            ->willReturn($subjects);

        $this->mockSubjectService->expects($this->once())
            ->method('getYearLevels')
            ->willReturn($yearLevels);

        $this->mockSubjectService->expects($this->once())
            ->method('getSemesters')
            ->willReturn($semesters);

        $this->mockView->expects($this->once())
            ->method('display')
            ->with('admin.subjects', [
                'admin' => $currentUser,
                'subjects' => $subjects,
                'yearLevels' => $yearLevels,
                'semesters' => $semesters
            ]);

        $this->subjectController->index();
    }

    /**
     * @test
     */
    public function it_should_add_subject_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $expectedResult = [
            'success' => true,
            'message' => 'Subject created successfully',
            'subject_id' => 5
        ];

        $this->mockSubjectService->expects($this->once())
            ->method('createSubject')
            ->with($_POST)
            ->willReturn($expectedResult);

        // Capture the session message
        ob_start();
        $this->subjectController->addSubject();
        ob_end_clean();

        $this->assertEquals('Subject created successfully', $_SESSION['success_message']);
    }

    /**
     * @test
     */
    public function it_should_handle_add_subject_failure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_code' => '',
            'subject_name' => ''
        ];

        $expectedResult = [
            'success' => false,
            'message' => 'Subject code and name are required'
        ];

        $this->mockSubjectService->expects($this->once())
            ->method('createSubject')
            ->with($_POST)
            ->willReturn($expectedResult);

        // Capture the session message
        ob_start();
        $this->subjectController->addSubject();
        ob_end_clean();

        $this->assertEquals('Subject code and name are required', $_SESSION['error_message']);
    }

    /**
     * @test
     */
    public function it_should_reject_add_subject_with_invalid_request_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->mockSubjectService->expects($this->never())
            ->method('createSubject');

        // Capture the JSON response
        ob_start();
        $this->subjectController->addSubject();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request method.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_edit_subject_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Updated Computer Science',
            'description' => 'Updated concepts',
            'units' => 4,
            'year_level' => '2nd Year',
            'semester' => '2nd Semester'
        ];

        $expectedResult = [
            'success' => true,
            'message' => 'Subject updated successfully'
        ];

        $this->mockSubjectService->expects($this->once())
            ->method('updateSubject')
            ->with(1, $_POST)
            ->willReturn($expectedResult);

        // Capture the session message
        ob_start();
        $this->subjectController->editSubject();
        ob_end_clean();

        $this->assertEquals('Subject updated successfully', $_SESSION['success_message']);
    }

    /**
     * @test
     */
    public function it_should_handle_edit_subject_failure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_id' => 999,
            'subject_code' => 'CS101',
            'subject_name' => 'Updated Computer Science'
        ];

        $expectedResult = [
            'success' => false,
            'message' => 'Subject not found'
        ];

        $this->mockSubjectService->expects($this->once())
            ->method('updateSubject')
            ->with(999, $_POST)
            ->willReturn($expectedResult);

        // Capture the session message
        ob_start();
        $this->subjectController->editSubject();
        ob_end_clean();

        $this->assertEquals('Subject not found', $_SESSION['error_message']);
    }

    /**
     * @test
     */
    public function it_should_reject_edit_subject_with_missing_subject_id()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_code' => 'CS101',
            'subject_name' => 'Updated Computer Science'
        ];

        $this->mockSubjectService->expects($this->never())
            ->method('updateSubject');

        // Capture the JSON response
        ob_start();
        $this->subjectController->editSubject();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Subject ID is required.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_delete_subject_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject_id' => 1];

        $expectedResult = [
            'success' => true,
            'message' => 'Subject deleted successfully'
        ];

        $this->mockSubjectService->expects($this->once())
            ->method('deleteSubject')
            ->with(1)
            ->willReturn($expectedResult);

        // Capture the session message
        ob_start();
        $this->subjectController->deleteSubject();
        ob_end_clean();

        $this->assertEquals('Subject deleted successfully', $_SESSION['success_message']);
    }

    /**
     * @test
     */
    public function it_should_handle_delete_subject_failure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject_id' => 1];

        $expectedResult = [
            'success' => false,
            'message' => 'Cannot delete subject. It is currently assigned to faculty members.'
        ];

        $this->mockSubjectService->expects($this->once())
            ->method('deleteSubject')
            ->with(1)
            ->willReturn($expectedResult);

        // Capture the session message
        ob_start();
        $this->subjectController->deleteSubject();
        ob_end_clean();

        $this->assertEquals('Cannot delete subject. It is currently assigned to faculty members.', $_SESSION['error_message']);
    }

    /**
     * @test
     */
    public function it_should_reject_delete_subject_with_missing_subject_id()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];

        $this->mockSubjectService->expects($this->never())
            ->method('deleteSubject');

        // Capture the JSON response
        ob_start();
        $this->subjectController->deleteSubject();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Subject ID is required.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_get_subject_by_id_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $subjectId = 1;

        $subjectData = [
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $this->mockSubjectService->expects($this->once())
            ->method('getSubjectById')
            ->with(1)
            ->willReturn($subjectData);

        // Capture the JSON response
        ob_start();
        $this->subjectController->getSubject($subjectId);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals($subjectData, $response['data']);
    }

    /**
     * @test
     */
    public function it_should_return_error_when_subject_not_found_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $subjectId = 999;

        $this->mockSubjectService->expects($this->once())
            ->method('getSubjectById')
            ->with(999)
            ->willReturn(null);

        // Capture the JSON response
        ob_start();
        $this->subjectController->getSubject($subjectId);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Subject not found.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_search_subjects_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['q' => 'computer'];

        $searchResults = [
            [
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science'
            ]
        ];

        $this->mockSubjectService->expects($this->once())
            ->method('searchSubjects')
            ->with('computer')
            ->willReturn($searchResults);

        // Capture the JSON response
        ob_start();
        $this->subjectController->searchSubjects();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals($searchResults, $response['data']);
    }

    /**
     * @test
     */
    public function it_should_return_error_when_search_query_missing()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [];

        $this->mockSubjectService->expects($this->never())
            ->method('searchSubjects');

        // Capture the JSON response
        ob_start();
        $this->subjectController->searchSubjects();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Search query is required.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_get_subjects_by_year_level_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['year_level' => '1st Year'];

        $filterResults = [
            [
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'year_level' => '1st Year'
            ]
        ];

        $this->mockSubjectService->expects($this->once())
            ->method('getSubjectsByYearLevel')
            ->with('1st Year')
            ->willReturn($filterResults);

        // Capture the JSON response
        ob_start();
        $this->subjectController->getSubjectsByYearLevel();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals($filterResults, $response['data']);
    }

    /**
     * @test
     */
    public function it_should_return_error_when_year_level_missing()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [];

        $this->mockSubjectService->expects($this->never())
            ->method('getSubjectsByYearLevel');

        // Capture the JSON response
        ob_start();
        $this->subjectController->getSubjectsByYearLevel();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Year level is required.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_get_subjects_by_semester_for_ajax()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['semester' => '1st Semester'];

        $filterResults = [
            [
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'semester' => '1st Semester'
            ]
        ];

        $this->mockSubjectService->expects($this->once())
            ->method('getSubjectsBySemester')
            ->with('1st Semester')
            ->willReturn($filterResults);

        // Capture the JSON response
        ob_start();
        $this->subjectController->getSubjectsBySemester();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals($filterResults, $response['data']);
    }

    /**
     * @test
     */
    public function it_should_return_error_when_semester_missing()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [];

        $this->mockSubjectService->expects($this->never())
            ->method('getSubjectsBySemester');

        // Capture the JSON response
        ob_start();
        $this->subjectController->getSubjectsBySemester();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Semester is required.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_reject_ajax_requests_with_invalid_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->mockSubjectService->expects($this->never())
            ->method('getSubjectById');

        // Capture the JSON response
        ob_start();
        $this->subjectController->getSubject(1);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request method.', $response['message']);
    }

    protected function tearDown(): void
    {
        // Clean up session data
        if (isset($_SESSION['success_message'])) {
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            unset($_SESSION['error_message']);
        }
        
        // Clean up global variables
        unset($_SERVER['REQUEST_METHOD']);
        unset($_POST);
        unset($_GET);
    }
}