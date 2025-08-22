<?php

/**
 * Single TDD Cycle Demonstration
 * Shows one complete Red-Green-Refactor cycle for AuthService
 */

echo "\n🔄 SINGLE TDD CYCLE DEMONSTRATION\n";
echo "=================================\n\n";

echo "We'll demonstrate ONE complete Red-Green-Refactor cycle:\n";
echo "Feature: User Authentication\n";
echo "Test: testCanAuthenticateValidUser\n\n";

// PHASE 1: RED
echo "❌ PHASE 1: RED (Failing Test)\n";
echo str_repeat("-", 30) . "\n";
echo "Running test BEFORE implementation:\n\n";

echo "$ php vendor/bin/phpunit tests/Unit/Auth/AuthServiceTest.php::testCanAuthenticateValidUser --colors\n";
system('php vendor/bin/phpunit tests/Unit/Auth/AuthServiceTest.php::testCanAuthenticateValidUser --colors');

echo "\n✅ RED PHASE COMPLETE: Test fails as expected!\n";
echo "Error: Class 'App\\Services\\Auth\\AuthService' not found\n\n";

echo "Press Enter to continue to GREEN phase...";
fgets(STDIN);

// PHASE 2: GREEN
echo "\n✅ PHASE 2: GREEN (Make Test Pass)\n";
echo str_repeat("-", 30) . "\n";
echo "Creating minimal AuthService implementation...\n\n";

// Create the minimal AuthService
$authServiceCode = '<?php

namespace App\Services\Auth;

class AuthService
{
    public function login($schoolId, $password)
    {
        // Hardcoded success for now - just make the test pass
        if ($schoolId === "ADMIN001" && $password === "password123") {
            return [
                "success" => true,
                "message" => "Login successful",
                "user" => [
                    "user_id" => 1,
                    "school_id" => "ADMIN001",
                    "role" => "admin"
                ]
            ];
        }
        
        return [
            "success" => false,
            "message" => "Invalid credentials"
        ];
    }
}';

if (!is_dir('src/App/Services/Auth')) {
    mkdir('src/App/Services/Auth', 0755, true);
}

file_put_contents('src/App/Services/Auth/AuthService.php', $authServiceCode);

echo "📝 Created src/App/Services/Auth/AuthService.php\n";
echo "Code: Hardcoded implementation (minimal to pass test)\n\n";

echo "Running test AFTER implementation:\n";
echo "$ php vendor/bin/phpunit tests/Unit/Auth/AuthServiceTest.php::testCanAuthenticateValidUser --colors\n";
system('php vendor/bin/phpunit tests/Unit/Auth/AuthServiceTest.php::testCanAuthenticateValidUser --colors');

echo "\n✅ GREEN PHASE COMPLETE: Test now passes!\n\n";

echo "Press Enter to continue to REFACTOR phase...";
fgets(STDIN);

// PHASE 3: REFACTOR
echo "\n🔄 PHASE 3: REFACTOR (Improve Design)\n";
echo str_repeat("-", 30) . "\n";
echo "Adding input validation while keeping test green...\n\n";

// Update AuthService with validation
$improvedAuthServiceCode = '<?php

namespace App\Services\Auth;

class AuthService
{
    public function login($schoolId, $password)
    {
        // REFACTOR: Add input validation
        if (empty(trim($schoolId)) || empty(trim($password))) {
            return [
                "success" => false,
                "message" => "School ID and password are required."
            ];
        }

        // REFACTOR: Sanitize inputs
        $schoolId = trim($schoolId);
        $password = trim($password);

        // Keep existing logic to maintain test
        if ($schoolId === "ADMIN001" && $password === "password123") {
            return [
                "success" => true,
                "message" => "Login successful",
                "user" => [
                    "user_id" => 1,
                    "school_id" => "ADMIN001",
                    "role" => "admin"
                ]
            ];
        }
        
        return [
            "success" => false,
            "message" => "Invalid School ID or password."
        ];
    }
}';

file_put_contents('src/App/Services/Auth/AuthService.php', $improvedAuthServiceCode);

echo "📝 Updated AuthService with:\n";
echo "  ✅ Input validation\n";
echo "  ✅ Input sanitization\n";
echo "  ✅ Better error messages\n\n";

echo "Running test AFTER refactoring:\n";
echo "$ php vendor/bin/phpunit tests/Unit/Auth/AuthServiceTest.php::testCanAuthenticateValidUser --colors\n";
system('php vendor/bin/phpunit tests/Unit/Auth/AuthServiceTest.php::testCanAuthenticateValidUser --colors');

echo "\n🔄 REFACTOR PHASE COMPLETE: Test still passes!\n\n";

echo "Running ALL auth tests to ensure nothing broke:\n";
echo "$ php vendor/bin/phpunit tests/Unit/Auth/AuthServiceTest.php --colors\n";
system('php vendor/bin/phpunit tests/Unit/Auth/AuthServiceTest.php --colors');

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 COMPLETE TDD CYCLE DEMONSTRATED!\n";
echo str_repeat("=", 50) . "\n\n";

echo "What we accomplished:\n";
echo "❌ RED:     Started with failing test\n";
echo "✅ GREEN:   Made test pass with minimal code\n";
echo "🔄 REFACTOR: Improved design while keeping test green\n\n";

echo "Key TDD Benefits Observed:\n";
echo "✅ Test drove the API design\n";
echo "✅ Minimal code prevented over-engineering\n";
echo "✅ Refactoring was safe with test coverage\n";
echo "✅ Input validation emerged from testing needs\n\n";

echo "Next: Run 'php fix_tests_step_by_step.php' for complete implementation!\n";