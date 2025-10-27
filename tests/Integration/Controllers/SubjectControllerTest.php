<?php

namespace Tests\Integration\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\Admin\SubjectController;
use App\Services\Subject\SubjectService;
use App\DAO\SubjectDAO;
use App\Models\Subject;

class SubjectControllerTest extends TestCase
{
    private SubjectController $subjectController;
    private SubjectService $subjectService;
    private SubjectDAO $subjectDAO;

    protected function setUp(): void
    {
        // Set up session for admin user
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        $_SESSION['full_name'] = 'Admin User';

        $this->subjectDAO = new SubjectDAO();
        $this->subjectService = new SubjectService($this->subjectDAO);
        $this->subjectController = new SubjectController(null, $this->subjectService);
    }

    protected function tearDown(): void
    {
        // Clean up session
        session_destroy();
        
        // Clean up global variables
        unset($_SERVER['REQUEST_METHOD']);
        unset($_POST);
        unset($_GET);
    }

    /**
     * @test
     */
    public function it_should_display_subject_management_page()
    {
        ob_start();
        $this->subjectController->index();
        $output = ob_get_clean();

        // Verify the page loads without errors
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    /**
     * @test
     */
    public function it_should_add_subject_successfully()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_code' => 'TEST101',
            'subject_name' => 'Test Subject',
            'description' => 'This is a test subject',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        ob_start();
        $this->subjectController->addSubject();
        ob_end_clean();

        // Verify success message was set
        $this->assertArrayHasKey('success_message', $_SESSION);
        $this->assertEquals('Subject created successfully', $_SESSION['success_message']);

        // Verify subject was actually created in database
        $subjects = $this->subjectService->getAllSubjects();
        $testSubject = null;
        foreach ($subjects as $subject) {
            if ($subject['subject_code'] === 'TEST101') {
                $testSubject = $subject;
                break;
            }
        }

        $this->assertNotNull($testSubject);
        $this->assertEquals('Test Subject', $testSubject['subject_name']);
        $this->assertEquals('This is a test subject', $testSubject['description']);
        $this->assertEquals(3, $testSubject['units']);
        $this->assertEquals('1st Year', $testSubject['year_level']);
        $this->assertEquals('1st Semester', $testSubject['semester']);

        // Clean up - delete the test subject
        $this->subjectService->deleteSubject($testSubject['subject_id']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_add_subject_with_duplicate_code()
    {
        // First, create a subject
        $subjectData = [
            'subject_code' => 'DUPLICATE101',
            'subject_name' => 'First Subject',
            'description' => 'First subject description',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $result = $this->subjectService->createSubject($subjectData);
        $this->assertTrue($result['success']);

        // Try to create another subject with the same code
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_code' => 'DUPLICATE101',
            'subject_name' => 'Second Subject',
            'description' => 'Second subject description',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        ob_start();
        $this->subjectController->addSubject();
        ob_end_clean();

        // Verify error message was set
        $this->assertArrayHasKey('error_message', $_SESSION);
        $this->assertEquals('Subject code already exists', $_SESSION['error_message']);

        // Clean up - delete the test subject
        $subjects = $this->subjectService->getAllSubjects();
        foreach ($subjects as $subject) {
            if ($subject['subject_code'] === 'DUPLICATE101') {
                $this->subjectService->deleteSubject($subject['subject_id']);
                break;
            }
        }
    }

    /**
     * @test
     */
    public function it_should_fail_to_add_subject_with_missing_required_fields()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_code' => '',
            'subject_name' => '',
            'description' => 'This should fail',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        ob_start();
        $this->subjectController->addSubject();
        ob_end_clean();

        // Verify error message was set
        $this->assertArrayHasKey('error_message', $_SESSION);
        $this->assertEquals('Subject code and name are required', $_SESSION['error_message']);
    }

    /**
     * @test
     */
    public function it_should_edit_subject_successfully()
    {
        // First, create a subject to edit
        $subjectData = [
            'subject_code' => 'EDIT101',
            'subject_name' => 'Original Subject',
            'description' => 'Original description',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $result = $this->subjectService->createSubject($subjectData);
        $this->assertTrue($result['success']);
        $subjectId = $result['subject_id'];

        // Now edit the subject
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_id' => $subjectId,
            'subject_code' => 'EDIT101',
            'subject_name' => 'Updated Subject',
            'description' => 'Updated description',
            'units' => 4,
            'year_level' => '2nd Year',
            'semester' => '2nd Semester'
        ];

        ob_start();
        $this->subjectController->editSubject();
        ob_end_clean();

        // Verify success message was set
        $this->assertArrayHasKey('success_message', $_SESSION);
        $this->assertEquals('Subject updated successfully', $_SESSION['success_message']);

        // Verify subject was actually updated in database
        $updatedSubject = $this->subjectService->getSubjectById($subjectId);
        $this->assertNotNull($updatedSubject);
        $this->assertEquals('Updated Subject', $updatedSubject['subject_name']);
        $this->assertEquals('Updated description', $updatedSubject['description']);
        $this->assertEquals(4, $updatedSubject['units']);
        $this->assertEquals('2nd Year', $updatedSubject['year_level']);
        $this->assertEquals('2nd Semester', $updatedSubject['semester']);

        // Clean up - delete the test subject
        $this->subjectService->deleteSubject($subjectId);
    }

    /**
     * @test
     */
    public function it_should_fail_to_edit_nonexistent_subject()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_id' => 99999,
            'subject_code' => 'NONEXISTENT101',
            'subject_name' => 'Non-existent Subject',
            'description' => 'This should fail',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        ob_start();
        $this->subjectController->editSubject();
        ob_end_clean();

        // Verify error message was set
        $this->assertArrayHasKey('error_message', $_SESSION);
        $this->assertEquals('Subject not found', $_SESSION['error_message']);
    }

    /**
     * @test
     */
    public function it_should_delete_subject_successfully()
    {
        // First, create a subject to delete
        $subjectData = [
            'subject_code' => 'DELETE101',
            'subject_name' => 'Subject to Delete',
            'description' => 'This subject will be deleted',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $result = $this->subjectService->createSubject($subjectData);
        $this->assertTrue($result['success']);
        $subjectId = $result['subject_id'];

        // Verify subject exists
        $subject = $this->subjectService->getSubjectById($subjectId);
        $this->assertNotNull($subject);

        // Now delete the subject
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject_id' => $subjectId];

        ob_start();
        $this->subjectController->deleteSubject();
        ob_end_clean();

        // Verify success message was set
        $this->assertArrayHasKey('success_message', $_SESSION);
        $this->assertEquals('Subject deleted successfully', $_SESSION['success_message']);

        // Verify subject was actually deleted from database
        $deletedSubject = $this->subjectService->getSubjectById($subjectId);
        $this->assertNull($deletedSubject);
    }

    /**
     * @test
     */
    public function it_should_fail_to_delete_nonexistent_subject()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject_id' => 99999];

        ob_start();
        $this->subjectController->deleteSubject();
        ob_end_clean();

        // Verify error message was set
        $this->assertArrayHasKey('error_message', $_SESSION);
        $this->assertEquals('Subject not found', $_SESSION['error_message']);
    }

    /**
     * @test
     */
    public function it_should_get_subject_by_id_for_ajax()
    {
        // First, create a subject
        $subjectData = [
            'subject_code' => 'AJAX101',
            'subject_name' => 'AJAX Test Subject',
            'description' => 'Subject for AJAX testing',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $result = $this->subjectService->createSubject($subjectData);
        $this->assertTrue($result['success']);
        $subjectId = $result['subject_id'];

        // Test AJAX get subject
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $this->subjectController->getSubject($subjectId);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        // Verify JSON response
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals($subjectId, $response['data']['subject_id']);
        $this->assertEquals('AJAX101', $response['data']['subject_code']);
        $this->assertEquals('AJAX Test Subject', $response['data']['subject_name']);

        // Clean up - delete the test subject
        $this->subjectService->deleteSubject($subjectId);
    }

    /**
     * @test
     */
    public function it_should_search_subjects_for_ajax()
    {
        // First, create a subject to search for
        $subjectData = [
            'subject_code' => 'SEARCH101',
            'subject_name' => 'Searchable Subject',
            'description' => 'This subject can be searched',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ];

        $result = $this->subjectService->createSubject($subjectData);
        $this->assertTrue($result['success']);
        $subjectId = $result['subject_id'];

        // Test AJAX search
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['q' => 'Searchable'];

        ob_start();
        $this->subjectController->searchSubjects();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        // Verify JSON response
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertGreaterThan(0, count($response['data']));

        // Verify search results contain the expected subject
        $found = false;
        foreach ($response['data'] as $subject) {
            if ($subject['subject_code'] === 'SEARCH101') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Clean up - delete the test subject
        $this->subjectService->deleteSubject($subjectId);
    }

    /**
     * @test
     */
    public function it_should_get_subjects_by_year_level_for_ajax()
    {
        // First, create a subject with specific year level
        $subjectData = [
            'subject_code' => 'YEAR101',
            'subject_name' => 'Year Level Test Subject',
            'description' => 'Subject for year level testing',
            'units' => 3,
            'year_level' => '2nd Year',
            'semester' => '1st Semester'
        ];

        $result = $this->subjectService->createSubject($subjectData);
        $this->assertTrue($result['success']);
        $subjectId = $result['subject_id'];

        // Test AJAX filter by year level
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['year_level' => '2nd Year'];

        ob_start();
        $this->subjectController->getSubjectsByYearLevel();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        // Verify JSON response
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);

        // Verify all returned subjects have the correct year level
        foreach ($response['data'] as $subject) {
            $this->assertEquals('2nd Year', $subject['year_level']);
        }

        // Clean up - delete the test subject
        $this->subjectService->deleteSubject($subjectId);
    }

    /**
     * @test
     */
    public function it_should_get_subjects_by_semester_for_ajax()
    {
        // First, create a subject with specific semester
        $subjectData = [
            'subject_code' => 'SEMESTER101',
            'subject_name' => 'Semester Test Subject',
            'description' => 'Subject for semester testing',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '2nd Semester'
        ];

        $result = $this->subjectService->createSubject($subjectData);
        $this->assertTrue($result['success']);
        $subjectId = $result['subject_id'];

        // Test AJAX filter by semester
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['semester' => '2nd Semester'];

        ob_start();
        $this->subjectController->getSubjectsBySemester();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        // Verify JSON response
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);

        // Verify all returned subjects have the correct semester
        foreach ($response['data'] as $subject) {
            $this->assertEquals('2nd Semester', $subject['semester']);
        }

        // Clean up - delete the test subject
        $this->subjectService->deleteSubject($subjectId);
    }

    /**
     * @test
     */
    public function it_should_reject_invalid_request_methods()
    {
        // Test GET request for add subject
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $this->subjectController->addSubject();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request method.', $response['message']);

        // Test POST request for AJAX get subject
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->subjectController->getSubject(1);
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request method.', $response['message']);
    }

    /**
     * @test
     */
    public function it_should_handle_missing_required_parameters()
    {
        // Test missing subject_id for edit
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'subject_code' => 'TEST101',
            'subject_name' => 'Test Subject'
        ];

        ob_start();
        $this->subjectController->editSubject();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Subject ID is required.', $response['message']);

        // Test missing subject_id for delete
        $_POST = [];

        ob_start();
        $this->subjectController->deleteSubject();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Subject ID is required.', $response['message']);

        // Test missing search query
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [];

        ob_start();
        $this->subjectController->searchSubjects();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Search query is required.', $response['message']);
    }
}