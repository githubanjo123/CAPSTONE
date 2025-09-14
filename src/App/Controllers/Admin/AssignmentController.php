<?php

namespace App\Controllers\Admin;

use App\Services\Auth\AuthService;
use App\Services\Assignment\AssignmentService;
use App\Core\View;
use App\Models\SubjectAssignment;

class AssignmentController
{
    private $authService;
    private $assignmentService;
    private $view;

    public function __construct(
        AuthService $authService = null,
        AssignmentService $assignmentService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->assignmentService = $assignmentService ?? new AssignmentService();
        $this->view = $view ?? new View();
        
        // Ensure user is authenticated and is admin
        $this->authService->requireAuth();
        $this->authService->requireRole('admin');
    }

    /**
     * Handle add assignment request
     */
    public function addAssignment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->assignmentService->createAssignment($_POST);
        
        // Return JSON response for AJAX requests
        if ($result['success']) {
            $this->showSuccess($result['data'], $result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle edit assignment request
     */
    public function editAssignment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $assignmentId = $_POST['assignment_id'] ?? null;
        if (!$assignmentId) {
            $this->showError('Assignment ID is required.');
            return;
        }

        $result = $this->assignmentService->updateAssignment($assignmentId, $_POST);
        
        // Return JSON response for AJAX requests
        if ($result['success']) {
            $this->showSuccess($result['data'], $result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle delete assignment request
     */
    public function deleteAssignment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $assignmentId = $_POST['assignment_id'] ?? null;
        if (!$assignmentId) {
            $this->showError('Assignment ID is required.');
            return;
        }

        $result = $this->assignmentService->deleteAssignment($assignmentId);
        
        // Return JSON response for AJAX requests
        if ($result['success']) {
            $this->showSuccess(null, $result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Get assignment by ID for AJAX requests
     */
    public function getAssignment($assignmentId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $assignment = $this->assignmentService->getAssignmentById($assignmentId);
        
        if ($assignment) {
            $this->showSuccess($assignment->toArray());
        } else {
            $this->showError('Assignment not found.');
        }
    }

    /**
     * Get assignments by filters for AJAX requests
     */
    public function getAssignmentsByFilters()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $filters = $_GET;
        $assignments = $this->assignmentService->getAssignmentsByFilters($filters);
        $assignmentsArray = $this->assignmentService->assignmentsToArray($assignments);
        
        $this->showSuccess($assignmentsArray);
    }

    /**
     * Get faculty workload for AJAX requests
     */
    public function getFacultyWorkload()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $facultyId = $_GET['faculty_id'] ?? null;
        $academicYear = $_GET['academic_year'] ?? null;

        if (!$facultyId) {
            $this->showError('Faculty ID is required.');
            return;
        }

        $workload = $this->assignmentService->getFacultyWorkload($facultyId, $academicYear);
        $workloadArray = $this->assignmentService->assignmentsToArray($workload);
        
        $this->showSuccess($workloadArray);
    }

    /**
     * Get unassigned subjects for AJAX requests
     */
    public function getUnassignedSubjects()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $academicYear = $_GET['academic_year'] ?? null;
        $semester = $_GET['semester'] ?? null;

        if (!$academicYear || !$semester) {
            $this->showError('Academic year and semester are required.');
            return;
        }

        $subjects = $this->assignmentService->getUnassignedSubjects($academicYear, $semester);
        
        $this->showSuccess($subjects);
    }

    /**
     * Refresh assignments for AJAX requests
     */
    public function refreshAssignments()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $assignments = $this->assignmentService->getAllAssignments();
        $assignmentsArray = $this->assignmentService->assignmentsToArray($assignments);
        
        $this->showSuccess($assignmentsArray);
    }

    /**
     * Get assignment statistics for AJAX requests
     */
    public function getAssignmentStats()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $academicYear = $_GET['academic_year'] ?? null;
        $stats = $this->assignmentService->getAssignmentStats($academicYear);
        
        $this->showSuccess($stats);
    }

    /**
     * Show success response
     */
    private function showSuccess($data = null, $message = 'Success')
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Show error response
     */
    private function showError($message = 'An error occurred')
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }
}