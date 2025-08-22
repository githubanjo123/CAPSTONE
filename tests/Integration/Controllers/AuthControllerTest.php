<?php

namespace Tests\Integration\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\Auth\AuthController;

class AuthControllerTest extends TestCase
{
    public function testLoginEndpointWithValidCredentials()
    {
        // Arrange
        $controller = new AuthController();
        $_POST = [
            'school_id' => 'ADMIN001',
            'password' => 'password'
        ];

        // Act
        ob_start();
        $controller->login();
        $output = ob_get_clean();

        // Assert
        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Login successful', $response['message']);
    }

    public function testLoginEndpointWithInvalidMethod()
    {
        // Arrange
        $controller = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Act
        ob_start();
        $controller->login();
        $output = ob_get_clean();

        // Assert
        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertStringContains('POST', $response['message']);
    }
}