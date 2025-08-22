<?php

namespace Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;

class AuthServiceTest extends TestCase
{
    public function testCanAuthenticateValidUser()
    {
        // Arrange
        $authService = new AuthService();
        $schoolId = 'ADMIN001';
        $password = 'password123';
        
        // Act
        $result = $authService->login($schoolId, $password);
        
        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Login successful', $result['message']);
        $this->assertArrayHasKey('user', $result);
    }

    public function testRejectsEmptyCredentials()
    {
        // Arrange
        $authService = new AuthService();
        
        // Act & Assert - Empty school ID
        $result = $authService->login('', 'password123');
        $this->assertFalse($result['success']);
        $this->assertEquals('School ID and password are required.', $result['message']);
        
        // Act & Assert - Empty password
        $result = $authService->login('ADMIN001', '');
        $this->assertFalse($result['success']);
        $this->assertEquals('School ID and password are required.', $result['message']);
    }
}