<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Examination System</title>
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
<body class="bg-gradient-to-br from-primary-600 to-primary-800 min-h-screen flex items-center justify-center font-sans">
    <div class="w-full max-w-md mx-4">
        <div class="bg-accent-50 rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-secondary-900 mb-2">Examination System</h1>
                <p class="text-xl text-secondary-600">Please sign in to continue</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/api/auth/login" method="POST">
                <div class="mb-6">
                    <label for="school_id" class="block text-base font-medium text-secondary-700 mb-3">School ID</label>
                    <input type="text" id="school_id" name="school_id" required 
                           class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-base font-medium text-secondary-700 mb-3">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required 
                               class="w-full px-4 py-3 pr-12 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                        <button type="button" onclick="togglePasswordVisibility()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary-500 hover:text-secondary-700 focus:outline-none">
                            <i id="passwordToggleIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-accent-50 font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg text-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>