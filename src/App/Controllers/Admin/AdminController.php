<?php

namespace App\Controllers\Admin;

use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use App\Services\Subject\SubjectService;
use App\Services\Assignment\AssignmentService;
use App\Core\View;

class AdminController
{
    private $authService;
    private $userService;
    private $subjectService;
    private $assignmentService;
    private $view;

    public function __construct(
        AuthService $authService = null,
        UserService $userService = null,
        SubjectService $subjectService = null,
        AssignmentService $assignmentService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->userService = $userService ?? new UserService();
        $this->subjectService = $subjectService ?? new SubjectService();
        $this->assignmentService = $assignmentService ?? new AssignmentService();
        $this->view = $view ?? new View();
        
        // Ensure user is authenticated and is admin
        $this->authService->requireAuth();
        $this->authService->requireRole('admin');
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $currentUser = $this->authService->getCurrentUser();
        
        // Get real data from database
        $students = $this->userService->getUsersByRole('student');
        $faculty = $this->userService->getUsersByRole('faculty');
        $subjects = $this->subjectService->getAllSubjects();
        $assignments = $this->assignmentService->getAllAssignments();
        
        // Convert User objects to arrays for view compatibility
        $studentsArray = $this->userService->usersToArray($students);
        $facultyArray = $this->userService->usersToArray($faculty);
        
        $data = [
            'admin' => $currentUser, // Already an array from AuthService
            'students' => $studentsArray,
            'faculty' => $facultyArray,
            'subjects' => $subjects,
            'assignments' => $assignments,
            'yearSections' => $this->getYearSections($studentsArray),
            'yearLevels' => $this->subjectService->getYearLevels(),
            'semesters' => $this->subjectService->getSemesters(),
            'assignmentYearLevels' => $this->assignmentService->getYearLevels(),
            'assignmentSections' => $this->assignmentService->getSections(),
            'academicYears' => $this->assignmentService->getAcademicYears(),
            'assignmentSemesters' => $this->assignmentService->getSemesters(),
            'assignmentStatuses' => $this->assignmentService->getAssignmentStatuses()
        ];
        
        $this->view->display('admin.dashboard', $data);
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
            $this->authService->logout();
            
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $basePath = dirname($scriptName);
            header('Location: ' . $basePath . '/login');
            return;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        $logoutUrl = $basePath . '/admin/logout?confirm=true';
        $dashboardUrl = $basePath . '/admin/dashboard';

        $data = [
            'logoutUrl' => $logoutUrl,
            'dashboardUrl' => $dashboardUrl
        ];

        $this->view->display('admin.logout-confirmation', $data);
    }





    /**
     * Get year-section combinations with counts
     */
    private function getYearSections($students)
    {
        $yearSections = [];
        
        foreach ($students as $student) {
            $key = $student['year_level'] . ' ' . $student['section'];
            if (!isset($yearSections[$key])) {
                $yearSections[$key] = 0;
            }
            $yearSections[$key]++;
        }
        
        return $yearSections;
    }

    /**
     * Handle add user request
     */
    public function addUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->createUser($_POST);
        
        if ($result['success']) {
            $this->showSuccess($result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle add student request
     */
    public function addStudent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->createUser($_POST);
        
        if ($result['success']) {
            // Store success message in session
            $_SESSION['success_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        } else {
            // Store error message in session
            $_SESSION['error_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        }
    }

    /**
     * Redirect to admin dashboard
     */
    private function redirectToDashboard()
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        header('Location: ' . $basePath . '/admin/dashboard');
        exit;
    }

    /**
     * Handle edit user request
     */
    public function editUser($userId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->updateUser($userId, $_POST);
        
        if ($result['success']) {
            $this->showSuccess($result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle edit student request
     */
    public function editStudent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            $this->showError('User ID is required.');
            return;
        }

        $result = $this->userService->updateUser($userId, $_POST);
        
        if ($result['success']) {
            // Store success message in session
            $_SESSION['success_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        } else {
            // Store error message in session
            $_SESSION['error_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        }
    }

    /**
     * Handle delete user request
     */
    public function deleteUser($userId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->deleteUser($userId);
        
        if ($result['success']) {
            $this->showSuccess($result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle delete student request
     */
    public function deleteStudent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            $this->showError('User ID is required.');
            return;
        }

        $result = $this->userService->deleteUser($userId);
        
        if ($result['success']) {
            // Store success message in session
            $_SESSION['success_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        } else {
            // Store error message in session
            $_SESSION['error_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        }
    }

    /**
     * Add a new faculty member
     */
    public function addFaculty()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method');
            return;
        }

        $data = [
            'school_id' => $_POST['school_id'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'role' => 'faculty',
            'password' => $_POST['password'] ?? ''
        ];

        // Validate required fields
        if (empty($data['school_id']) || empty($data['full_name'])) {
            $this->showError('School ID and Full Name are required');
            return;
        }

        try {
            $result = $this->userService->createUser($data);
            
            if ($result['success']) {
                $this->showSuccess('Faculty member added successfully');
                $this->redirectToDashboard();
            } else {
                $this->showError($result['message']);
                $this->redirectToDashboard();
            }
        } catch (Exception $e) {
            $this->showError('Error adding faculty member: ' . $e->getMessage());
       
       $this->redirectToDashboard(); }
    }

    /**
     * Edit an existing faculty member
     */
    public function editFaculty()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method');
            return;
        }

        $userId = $_POST['user_id'] ?? '';
        if (empty($userId)) {
            $this->showError('User ID is required');
            return;
        }

        $data = [
            'user_id' => $userId,
            'school_id' => $_POST['school_id'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'role' => 'faculty'
        ];

        // Validate required fields
        if (empty($data['school_id']) || empty($data['full_name'])) {
            $this->showError('School ID and Full Name are required');
            return;
        }

        try {
            $result = $this->userService->updateUser($data);
            
            if ($result['success']) {
                $this->showSuccess('Faculty member updated successfully');
            } else {
                $this->showError($result['message']);
            }
        } catch (Exception $e) {
            $this->showError('Error updating faculty member: ' . $e->getMessage());
        }
    }

    /**
     * Delete a faculty member
     */
    public function deleteFaculty()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method');
            return;
        }

        $userId = $_POST['user_id'] ?? '';
        if (empty($userId)) {
            $this->showError('User ID is required');
            return;
        }

        try {
            $result = $this->userService->deleteUser($userId);
            
            if ($result['success']) {
                $this->showSuccess('Faculty member deleted successfully');
                $this->redirectToDashboard();
            } else {
                $this->showError($result['message']);
                $this->redirectToDashboard();
            }
        } catch (Exception $e) {
            $this->showError('Error deleting faculty member: ' . $e->getMessage());
            $this->redirectToDashboard();
        }
    }

    /**
     * Show success message
     */
    private function showSuccess($message)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => $message
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