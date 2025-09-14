<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Examination System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#800000',
                            600: '#7a0000',
                            700: '#6b0000',
                            800: '#5c0000',
                            900: '#4d0000',
                        },
                        secondary: {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827',
                        },
                        accent: {
                            50: '#ffffff',
                            100: '#ffffff',
                            200: '#ffffff',
                            300: '#ffffff',
                            400: '#ffffff',
                            500: '#ffffff',
                            600: '#f3f4f6',
                            700: '#e5e7eb',
                            800: '#d1d5db',
                            900: '#9ca3af',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-secondary-50 font-sans text-lg">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-800 text-accent-500 py-8 mb-8">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold mb-2">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Admin Dashboard
                    </h1>
                    <p class="text-xl opacity-90">
                        Welcome back, <?= htmlspecialchars($admin['full_name'] ?? 'Admin') ?>
                    </p>
                </div>
                <div>
                    <button onclick="showLogoutConfirmation()" class="bg-transparent border-2 border-accent-500 text-accent-500 px-8 py-3 rounded-full hover:bg-accent-500 hover:text-primary-600 transition-all duration-300 text-lg font-semibold">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4">
        <!-- Session Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex justify-between items-center" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <button type="button" class="text-green-700 hover:text-green-900" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex justify-between items-center" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <button type="button" class="text-red-700 hover:text-red-900" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Fixed Tab Navigation -->
        <div class="sticky top-0 z-40 bg-secondary-50 border-b-2 border-secondary-200 mb-0">
            <div class="flex space-x-1">
                <button class="bg-accent-500 text-primary-600 font-semibold px-8 py-5 rounded-t-lg border-b-2 border-primary-600 hover:bg-secondary-100 transition-all duration-300 text-lg" id="users-tab" onclick="showTab('users')">
                    <i class="fas fa-users mr-3"></i>
                    Manage Users
                </button>
                <button class="text-secondary-600 font-semibold px-8 py-5 rounded-t-lg hover:bg-accent-500 hover:text-primary-600 transition-all duration-300 text-lg" id="subjects-tab" onclick="showTab('subjects')">
                    <i class="fas fa-book mr-3"></i>
                    Manage Subjects
                </button>
                <button class="text-secondary-600 font-semibold px-8 py-5 rounded-t-lg hover:bg-accent-500 hover:text-primary-600 transition-all duration-300 text-lg" id="assignments-tab" onclick="showTab('assignments')">
                    <i class="fas fa-link mr-3"></i>
                    Subject Assignments
                </button>
                <button class="text-secondary-600 font-semibold px-8 py-5 rounded-t-lg hover:bg-accent-500 hover:text-primary-600 transition-all duration-300 text-lg" id="reports-tab" onclick="showTab('reports')">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Reports
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="bg-accent-500 rounded-b-lg p-8 shadow-lg">
            <!-- Tab 1: Manage Users -->
            <div id="users" class="tab-content active">
                <?php include 'manage-users.php'; ?>
            </div>

            <!-- Tab 2: Manage Subjects -->
            <div id="subjects" class="tab-content hidden">
                <?php include 'manage-subjects.php'; ?>
            </div>

            <!-- Tab 3: Subject Assignments -->
            <div id="assignments" class="tab-content hidden">
                <div class="text-center py-12">
                    <i class="fas fa-link text-6xl text-grey-400 mb-4"></i>
                    <h4 class="text-xl font-semibold text-grey-700 mb-2">Subject Assignments</h4>
                    <p class="text-grey-500">Enhanced assignment system is ready!</p>
                    <div class="mt-4">
                        <p class="text-sm text-grey-600 mb-2">Available variables:</p>
                        <ul class="text-xs text-grey-500 text-left max-w-md mx-auto">
                            <li>• Academic Years: <?= isset($academicYears) ? count($academicYears) : 'Not set' ?></li>
                            <li>• Assignment Sections: <?= isset($assignmentSections) ? count($assignmentSections) : 'Not set' ?></li>
                            <li>• Assignment Semesters: <?= isset($assignmentSemesters) ? count($assignmentSemesters) : 'Not set' ?></li>
                            <li>• Assignment Statuses: <?= isset($assignmentStatuses) ? count($assignmentStatuses) : 'Not set' ?></li>
                            <li>• Subjects: <?= isset($subjects) ? count($subjects) : 'Not set' ?></li>
                            <li>• Faculty: <?= isset($faculty) ? count($faculty) : 'Not set' ?></li>
                        </ul>
                    </div>
                    <button onclick="alert('Assignment system is ready! Check console for debug info.')" class="mt-4 bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg">
                        Test Assignment System
                    </button>
                </div>
            </div>

            <!-- Tab 4: Reports -->
            <div id="reports" class="tab-content hidden">
                <div class="text-center py-12">
                    <i class="fas fa-chart-bar text-6xl text-grey-400 mb-4"></i>
                    <h4 class="text-xl font-semibold text-grey-700 mb-2">Reports & Analytics</h4>
                    <p class="text-grey-500">Reporting functionality coming soon...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
        <div class="bg-accent-500 rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-8 text-center">
                <div class="mb-6">
                    <i class="fas fa-sign-out-alt text-6xl text-primary-600 mb-4"></i>
                    <h3 class="text-2xl font-bold text-secondary-800 mb-2">Confirm Logout</h3>
                    <p class="text-secondary-600 text-lg">Are you sure you want to logout from the admin panel?</p>
                </div>
                
                <div class="flex justify-center space-x-4">
                    <button onclick="hideLogoutConfirmation()" 
                            class="px-6 py-3 bg-secondary-500 hover:bg-secondary-600 text-accent-500 rounded-lg font-semibold transition-all duration-300 text-lg">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </button>
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/logout" 
                       class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-accent-500 rounded-lg font-semibold transition-all duration-300 text-lg">
                        <i class="fas fa-check mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Logout Confirmation Functions
        function showLogoutConfirmation() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }

        function hideLogoutConfirmation() {
            document.getElementById('logoutModal').classList.add('hidden');
        }

        // Close logout modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const logoutModal = document.getElementById('logoutModal');
            logoutModal.addEventListener('click', function(e) {
                if (e.target === logoutModal) {
                    hideLogoutConfirmation();
                }
            });
        });

        // Tab Switching
        function showTab(tabName) {
            console.log('showTab called with:', tabName); // Debug log
            
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('[id$="-tab"]').forEach(tab => {
                tab.classList.remove('bg-accent-500', 'text-primary-600', 'border-primary-600');
                tab.classList.add('text-secondary-600');
            });
            
            // Show selected tab content
            const tabContent = document.getElementById(tabName);
            if (tabContent) {
                tabContent.classList.remove('hidden');
                tabContent.classList.add('active');
                console.log('Tab content shown:', tabName); // Debug log
            } else {
                console.error('Tab content not found:', tabName); // Debug log
            }
            
            // Add active class to selected tab
            const activeTab = document.getElementById(tabName + '-tab');
            if (activeTab) {
                activeTab.classList.remove('text-secondary-600');
                activeTab.classList.add('bg-accent-500', 'text-primary-600', 'border-primary-600');
                console.log('Tab button activated:', tabName + '-tab'); // Debug log
            } else {
                console.error('Tab button not found:', tabName + '-tab'); // Debug log
            }
            
            // Save current tab to localStorage
            localStorage.setItem('adminCurrentTab', tabName);
        }

        // Year-Section Tab Switching
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, setting up tabs...'); // Debug log
            
            const yearSectionTabs = document.querySelectorAll('.year-section-tab');
            const studentSections = document.querySelectorAll('.student-section');

            yearSectionTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetSection = this.getAttribute('data-section');
                    
                    // Update active tab
                    yearSectionTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show/hide sections
                    studentSections.forEach(section => {
                        if (section.id === targetSection) {
                            section.style.display = 'block';
                        } else {
                            section.style.display = 'none';
                        }
                    });
                });
            });

            // Show first section by default
            if (yearSectionTabs.length > 0) {
                yearSectionTabs[0].click();
            }
            
            // Restore saved tab if available
            const savedTab = localStorage.getItem('adminCurrentTab');
            if (savedTab && document.getElementById(savedTab + '-tab')) {
                console.log('Restoring saved tab:', savedTab); // Debug log
                showTab(savedTab);
            }
            
            // Test tab functionality
            console.log('Testing tab elements...'); // Debug log
            const tabElements = ['users', 'subjects', 'assignments', 'reports'];
            tabElements.forEach(tabName => {
                const tabButton = document.getElementById(tabName + '-tab');
                const tabContent = document.getElementById(tabName);
                console.log(`${tabName}: Button=${!!tabButton}, Content=${!!tabContent}`); // Debug log
            });
        });
    </script>
</body>
</html>