<?php

namespace App\Controllers\Admin;

use App\Services\Auth\AuthService;
use App\Services\Subject\SubjectService;
use App\Core\View;

class SubjectController
{
    private $authService;
    private $subjectService;
    private $view;

    public function __construct(
        AuthService $authService = null,
        SubjectService $subjectService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->subjectService = $subjectService ?? new SubjectService();
        $this->view = $view ?? new View();
        
        // Ensure user is authenticated and is admin
        $this->authService->requireAuth();
        $this->authService->requireRole('admin');
    }



    /**
     * Handle add subject request
     */
    public function addSubject()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->subjectService->createSubject($_POST);
        
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Handle edit subject request
     */
    public function editSubject()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $subjectId = $_POST['subject_id'] ?? null;
        if (!$subjectId) {
            $this->showError('Subject ID is required.');
            return;
        }

        $result = $this->subjectService->updateSubject($subjectId, $_POST);
        
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Handle delete subject request
     */
    public function deleteSubject()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $subjectId = $_POST['subject_id'] ?? null;
        if (!$subjectId) {
            $this->showError('Subject ID is required.');
            return;
        }

        $result = $this->subjectService->deleteSubject($subjectId);
        
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Get subject by ID for AJAX requests
     */
    public function getSubject($subjectId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $subject = $this->subjectService->getSubjectById($subjectId);
        
        if ($subject) {
            $this->showSuccess($subject);
        } else {
            $this->showError('Subject not found.');
        }
    }

    /**
     * Search subjects for AJAX requests
     */
    public function searchSubjects()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $query = $_GET['q'] ?? '';
        if (empty($query)) {
            $this->showError('Search query is required.');
            return;
        }

        $subjects = $this->subjectService->searchSubjects($query);
        $this->showSuccess($subjects);
    }

    /**
     * Get subjects by year level for AJAX requests
     */
    public function getSubjectsByYearLevel()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $yearLevel = $_GET['year_level'] ?? '';
        if (empty($yearLevel)) {
            $this->showError('Year level is required.');
            return;
        }

        $subjects = $this->subjectService->getSubjectsByYearLevel($yearLevel);
        $this->showSuccess($subjects);
    }

    /**
     * Get subjects by semester for AJAX requests
     */
    public function getSubjectsBySemester()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $semester = $_GET['semester'] ?? '';
        if (empty($semester)) {
            $this->showError('Semester is required.');
            return;
        }

        $subjects = $this->subjectService->getSubjectsBySemester($semester);
        $this->showSuccess($subjects);
    }

    /**
     * Refresh subjects data for AJAX requests
     */
    public function refreshSubjects()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->showError('Invalid request method.');
            return;
        }

        $subjects = $this->subjectService->getAllSubjects();
        $this->showSuccess($subjects);
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