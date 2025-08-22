<?php

namespace Tests\Unit\DAO;

use PHPUnit\Framework\TestCase;
use App\DAO\Auth\UserDAO;

class UserDAOTest extends TestCase
{
    private $userDAO;

    protected function setUp(): void
    {
        $this->userDAO = new UserDAO();
    }

    public function testCanAuthenticateValidUser()
    {
        // Arrange
        $schoolId = 'ADMIN001';
        $password = 'password'; // Known test password

        // Act
        $user = $this->userDAO->authenticate($schoolId, $password);

        // Assert
        $this->assertNotNull($user);
        $this->assertEquals('ADMIN001', $user['school_id']);
        $this->assertEquals('admin', $user['role']);
    }

    public function testReturnsNullForInvalidCredentials()
    {
        // Act
        $user = $this->userDAO->authenticate('INVALID', 'wrong_password');

        // Assert
        $this->assertNull($user);
    }
}