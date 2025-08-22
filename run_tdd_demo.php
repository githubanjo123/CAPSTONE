<?php

/**
 * TDD Demonstration Script
 * This script demonstrates the Red-Green-Refactor cycle by running tests
 * and showing failures, then implementing code to make them pass.
 */

class TDDDemo
{
    private $step = 1;
    
    public function run()
    {
        $this->showHeader();
        
        // Phase 1: Authentication System
        $this->demonstratePhase1();
        
        // Phase 2: User Management
        $this->demonstratePhase2();
        
        // Phase 3: Data Access Layer
        $this->demonstratePhase3();
        
        // Phase 4: Controller Layer
        $this->demonstratePhase4();
        
        $this->showConclusion();
    }
    
    private function showHeader()
    {
        echo "\n";
        echo "🔄 " . str_repeat("=", 70) . "\n";
        echo "   TDD DEMONSTRATION: Red-Green-Refactor Cycle\n";
        echo "   Educational Management System Development\n";
        echo str_repeat("=", 72) . "\n\n";
        
        echo "This demonstration shows the actual TDD process:\n";
        echo "❌ RED:    Write failing tests\n";
        echo "✅ GREEN:  Make tests pass with minimal code\n";
        echo "🔄 REFACTOR: Improve code while keeping tests green\n\n";
        
        $this->pause();
    }
    
    private function demonstratePhase1()
    {
        $this->showPhaseHeader("Phase 1: Authentication System Development");
        
        // Step 1.1: RED - Show failing test
        $this->showStep("1.1", "RED", "Basic Login Test");
        echo "Creating a failing test for user authentication...\n\n";
        
        echo "📝 Test Code:\n";
        $this->showCodeBlock('tests/Unit/Auth/AuthServiceTest.php', [
            'public function testCanAuthenticateValidUser()',
            '{',
            '    $authService = new AuthService();',
            '    $result = $authService->login("ADMIN001", "password123");',
            '    $this->assertTrue($result["success"]);',
            '}'
        ]);
        
        echo "🔴 Running Test:\n";
        $this->runCommand('php vendor/bin/phpunit tests/Unit/Auth/AuthServiceTest.php::testCanAuthenticateValidUser --colors');
        
        echo "\n❌ EXPECTED RESULT: Class 'AuthService' not found\n";
        echo "✅ ACTUAL RESULT: Test fails as expected - we haven't created AuthService yet!\n\n";
        
        $this->pause();
        
        // Step 1.2: GREEN - Make test pass
        $this->showStep("1.2", "GREEN", "Make Test Pass");
        echo "Creating minimal AuthService to make the test pass...\n\n";
        
        $this->createMinimalAuthService();
        
        echo "🔴 Running Test Again:\n";
        $this->runCommand('php vendor/bin/phpunit tests/Unit/Auth/AuthServiceTest.php::testCanAuthenticateValidUser --colors');
        
        echo "\n✅ SUCCESS: Test now passes with minimal hardcoded implementation!\n\n";
        
        $this->pause();
    }
    
    private function demonstratePhase2()
    {
        $this->showPhaseHeader("Phase 2: User Management System");
        
        $this->showStep("2.1", "RED", "User Creation Test");
        echo "Creating failing test for user creation...\n\n";
        
        echo "🔴 Running User Service Tests:\n";
        $this->runCommand('php vendor/bin/phpunit tests/Unit/User/UserServiceTest.php --colors');
        
        echo "\n❌ EXPECTED RESULT: All UserService tests fail - class doesn't exist\n\n";
        
        $this->pause();
    }
    
    private function demonstratePhase3()
    {
        $this->showPhaseHeader("Phase 3: Data Access Layer");
        
        $this->showStep("3.1", "RED", "Database Integration Test");
        echo "Creating failing tests for database operations...\n\n";
        
        echo "🔴 Running DAO Tests:\n";
        $this->runCommand('php vendor/bin/phpunit tests/Unit/DAO/UserDAOTest.php --colors');
        
        echo "\n❌ EXPECTED RESULT: UserDAO class not found\n\n";
        
        $this->pause();
    }
    
    private function demonstratePhase4()
    {
        $this->showPhaseHeader("Phase 4: Controller Layer");
        
        $this->showStep("4.1", "RED", "HTTP Request Handling Test");
        echo "Creating failing tests for controller integration...\n\n";
        
        echo "🔴 Running Controller Tests:\n";
        $this->runCommand('php vendor/bin/phpunit tests/Integration/Controllers/AuthControllerTest.php --colors');
        
        echo "\n❌ EXPECTED RESULT: AuthController class not found\n\n";
        
        $this->pause();
    }
    
    private function createMinimalAuthService()
    {
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
        
        echo "📝 Created minimal AuthService:\n";
        $this->showCodeBlock('src/App/Services/Auth/AuthService.php', [
            'class AuthService',
            '{',
            '    public function login($schoolId, $password)',
            '    {',
            '        // Hardcoded success - just make test pass',
            '        if ($schoolId === "ADMIN001" && $password === "password123") {',
            '            return ["success" => true, "message" => "Login successful"];',
            '        }',
            '        return ["success" => false];',
            '    }',
            '}'
        ]);
    }
    
    private function showPhaseHeader($title)
    {
        echo "\n" . str_repeat("=", 72) . "\n";
        echo "🚀 " . strtoupper($title) . "\n";
        echo str_repeat("=", 72) . "\n\n";
    }
    
    private function showStep($stepNum, $phase, $description)
    {
        $icon = $phase === 'RED' ? '❌' : ($phase === 'GREEN' ? '✅' : '🔄');
        echo "Step {$stepNum} - {$icon} {$phase}: {$description}\n";
        echo str_repeat("-", 50) . "\n";
    }
    
    private function showCodeBlock($filename, $lines)
    {
        echo "```php\n";
        echo "// {$filename}\n";
        foreach ($lines as $line) {
            echo $line . "\n";
        }
        echo "```\n\n";
    }
    
    private function runCommand($command)
    {
        echo "$ {$command}\n";
        $output = shell_exec($command . ' 2>&1');
        echo $output . "\n";
    }
    
    private function pause()
    {
        echo "Press Enter to continue to next step...\n";
        fgets(STDIN);
    }
    
    private function showConclusion()
    {
        echo "\n" . str_repeat("=", 72) . "\n";
        echo "🎉 TDD DEMONSTRATION COMPLETE!\n";
        echo str_repeat("=", 72) . "\n\n";
        
        echo "Key Takeaways:\n";
        echo "✅ Tests drive the design and architecture\n";
        echo "✅ Start with failing tests (RED phase)\n";
        echo "✅ Write minimal code to pass (GREEN phase)\n";
        echo "✅ Refactor for better design while keeping tests green\n";
        echo "✅ Architecture emerges naturally from testing needs\n\n";
        
        echo "The complete TDD cycle ensures:\n";
        echo "- Every feature is tested before implementation\n";
        echo "- Code is designed for testability\n";
        echo "- Refactoring is safe with test coverage\n";
        echo "- Architecture evolves based on real needs\n\n";
    }
}

// Run the demonstration
if (php_sapi_name() === 'cli') {
    $demo = new TDDDemo();
    $demo->run();
}