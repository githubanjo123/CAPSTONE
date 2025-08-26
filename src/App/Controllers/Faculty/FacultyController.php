<?php

namespace App\Controllers\Faculty;

use App\Services\Auth\AuthService;
use App\Core\View;

class FacultyController
{
    private AuthService $authService;
    private View $view;

    public function __construct(
        AuthService $authService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->view = $view ?? new View();

        // Enforce authentication and role
        $this->authService->requireAuth();
        $this->authService->requireRole('faculty');
    }

    public function dashboard(): void
    {
        $currentUser = $this->authService->getCurrentUser();
        $this->view->display('faculty.dashboard', [
            'faculty' => $currentUser,
        ]);
    }

    public function logout(): void
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
        $logoutUrl = $basePath . '/faculty/logout?confirm=true';
        $dashboardUrl = $basePath . '/faculty/dashboard';

        $data = [
            'logoutUrl' => $logoutUrl,
            'dashboardUrl' => $dashboardUrl
        ];

        $this->view->display('faculty.logout-confirmation', $data);
    }
}