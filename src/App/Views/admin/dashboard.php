<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Examination System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    fontSize: {
                        'xs': '0.75rem',
                        'sm': '0.875rem',
                        'base': '1rem',
                        'lg': '1.125rem',
                        'xl': '1.25rem',
                        '2xl': '1.5rem',
                        '3xl': '1.875rem',
                        '4xl': '2.25rem',
                        '5xl': '3rem',
                    },
                    colors: {
                        primary: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#800000',
                            600: '#660000',
                            700: '#4d0000',
                            800: '#330000',
                            900: '#1a0000',
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
                            900: '#000000',
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
<body class="bg-accent-50 font-sans text-base">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-800 text-accent-50 py-8 mb-8">
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
                    <button onclick="showLogoutConfirmation()" class="bg-transparent border-2 border-accent-50 text-accent-50 px-8 py-3 rounded-full hover:bg-accent-50 hover:text-primary-600 transition-all duration-300 text-lg font-medium">
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
        <div class="sticky top-0 z-40 bg-accent-50 border-b-2 border-secondary-200 mb-0 shadow-sm">
            <div class="flex space-x-1">
                <button class="bg-accent-50 text-primary-600 font-semibold px-8 py-5 rounded-t-lg border-b-2 border-primary-600 hover:bg-accent-100 transition-all duration-300 text-lg" id="users-tab" onclick="showTab('users')">
                    <i class="fas fa-users mr-3"></i>
                    Manage Users
                </button>
                <button class="text-secondary-600 font-semibold px-8 py-5 rounded-t-lg hover:bg-accent-50 hover:text-primary-600 transition-all duration-300 text-lg" id="subjects-tab" onclick="showTab('subjects')">
                    <i class="fas fa-book mr-3"></i>
                    Manage Subjects
                </button>
                <button class="text-secondary-600 font-semibold px-8 py-5 rounded-t-lg hover:bg-accent-50 hover:text-primary-600 transition-all duration-300 text-lg" id="assignments-tab" onclick="showTab('assignments')">
                    <i class="fas fa-link mr-3"></i>
                    Subject Assignments
                </button>
                <button class="text-secondary-600 font-semibold px-8 py-5 rounded-t-lg hover:bg-accent-50 hover:text-primary-600 transition-all duration-300 text-lg" id="reports-tab" onclick="showTab('reports')">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Reports
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="bg-accent-50 rounded-b-lg p-8 shadow-lg">
            <!-- Tab 1: Manage Users -->
            <div id="users" class="tab-content active">
                <?php include 'manage-users.php'; ?>
            </div>

            <!-- Tab 2: Manage Subjects -->
            <div id="subjects" class="tab-content hidden">
                <div class="text-center py-12">
                    <i class="fas fa-book text-6xl text-grey-400 mb-4"></i>
                    <h4 class="text-xl font-semibold text-grey-700 mb-2">Manage Subjects</h4>
                    <p class="text-grey-500">Subject management functionality coming soon...</p>
                </div>
            </div>

            <!-- Tab 3: Subject Assignments -->
            <div id="assignments" class="tab-content hidden">
                <div class="text-center py-12">
                    <i class="fas fa-link text-6xl text-grey-400 mb-4"></i>
                    <h4 class="text-xl font-semibold text-grey-700 mb-2">Subject Assignments</h4>
                    <p class="text-grey-500">Assignment functionality coming soon...</p>
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
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-accent-50 rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-8 text-center">
                <div class="mb-6">
                    <i class="fas fa-sign-out-alt text-6xl text-primary-600 mb-4"></i>
                    <h3 class="text-2xl font-bold text-secondary-900 mb-2">Confirm Logout</h3>
                    <p class="text-lg text-secondary-600">Are you sure you want to logout from the admin panel?</p>
                </div>
                <div class="flex justify-center space-x-4">
                    <button onclick="confirmLogout()" class="bg-primary-600 hover:bg-primary-700 text-accent-50 px-8 py-3 rounded-lg font-semibold transition-all duration-300 text-lg">
                        <i class="fas fa-check mr-2"></i>
                        Logout
                    </button>
                    <button onclick="closeLogoutModal()" class="bg-secondary-600 hover:bg-secondary-700 text-accent-50 px-8 py-3 rounded-lg font-semibold transition-all duration-300 text-lg">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab Switching
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('[id$="-tab"]').forEach(tab => {
                tab.classList.remove('bg-accent-50', 'text-primary-600', 'border-primary-600');
                tab.classList.add('text-secondary-600');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.remove('hidden');
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to selected tab
            const activeTab = document.getElementById(tabName + '-tab');
            activeTab.classList.remove('text-secondary-600');
            activeTab.classList.add('bg-accent-50', 'text-primary-600', 'border-primary-600');
        }

        // Logout Confirmation Functions
        function showLogoutConfirmation() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }

        function confirmLogout() {
            window.location.href = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/logout?confirm=true';
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const logoutModal = document.getElementById('logoutModal');
            logoutModal.addEventListener('click', function(e) {
                if (e.target === logoutModal) {
                    closeLogoutModal();
                }
            });
        });

        // Year-Section Tab Switching
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>