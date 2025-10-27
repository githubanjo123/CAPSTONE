<?php

namespace App\Controllers\Admin;

use App\Services\Auth\AuthService;
use App\Services\Assignment\AssignmentService;
use App\Core\View;

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
        header('Content-Type: application/json');
        echo json_encode($result);
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
        header('Content-Type: application/json');
        echo json_encode($result);
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
        header('Content-Type: application/json');
        echo json_encode($result);
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
            $this->showSuccess($assignment);
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
        $this->showSuccess($assignments);
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
        $this->showSuccess($workload);
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

        $academicYear = $_GET['academic_year'] ?? '2024-2025';
        $semester = $_GET['semester'] ?? '1st Semester';

        $subjects = $this->assignmentService->getUnassignedSubjects($academicYear, $semester);
        $this->showSuccess($subjects);
    }

    /**
     * Refresh assignments data for AJAX requests
     */
    public function refreshAssignments()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $assignments = $this->assignmentService->getAllAssignments();
        $this->showSuccess($assignments);
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
     * Show success message
     */
    private function showSuccess($data)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Show error message
     */
    private function showError($message)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }
}