<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Examination System</title>
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
<body class="bg-gradient-to-br from-primary-600 to-primary-800 min-h-screen flex items-center justify-center font-sans">
    <div class="bg-accent-500 rounded-2xl shadow-2xl p-8 w-full max-w-md mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-secondary-800 mb-2">Examination System</h1>
            <p class="text-secondary-600 text-lg">Please sign in to continue</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/api/auth/login" method="POST">
            <div class="mb-6">
                <label for="school_id" class="block text-sm font-semibold text-secondary-700 mb-2 text-lg">School ID</label>
                <input type="text" id="school_id" name="school_id" required 
                       class="w-full px-4 py-3 border-2 border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-lg">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-semibold text-secondary-700 mb-2 text-lg">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required 
                           class="w-full px-4 py-3 pr-12 border-2 border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-lg">
                    <button type="button" onclick="togglePassword()" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary-500 hover:text-primary-600 transition-colors">
                        <i id="passwordToggleIcon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-accent-500 font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg text-lg">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </button>
        </form>
    </div>

    <script>
        function togglePassword() {
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
</body>
</html>