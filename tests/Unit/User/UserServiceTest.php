<?php

namespace Tests\Unit\User;

use PHPUnit\Framework\TestCase;
use App\Services\User\UserService;
use App\Interfaces\UserDAOInterface;

class UserServiceTest extends TestCase
{
    private $mockUserDAO;
    private $userService;

    protected function setUp(): void
    {
        $this->mockUserDAO = $this->createMock(UserDAOInterface::class);
        $this->userService = new UserService($this->mockUserDAO);
    }

    public function testCanCreateValidUser()
    {
        // Arrange
        $userData = [
            'school_id' => 'STU001',
            'full_name' => 'John Doe',
            'password' => 'password123',
            'role' => 'student',
            'year_level' => 2,
            'section' => 'A'
        ];

        $this->mockUserDAO->method('findBySchoolId')->willReturn(null);
        $this->mockUserDAO->method('create')->willReturn(true);

        // Act
        $result = $this->userService->createUser($userData);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('User created successfully.', $result['message']);
    }

    public function testValidatesStudentSpecificFields()
    {
        // Arrange
        $studentData = [
            'school_id' => 'STU001',
            'full_name' => 'John Doe',
            'password' => 'password123',
            'role' => 'student'
            // Missing year_level and section
        ];

        // Act
        $result = $this->userService->createUser($studentData);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('year level and section', $result['message']);
    }

    public function testValidatesRoleEnum()
    {
        // Arrange
        $invalidRoleData = [
            'school_id' => 'USR001',
            'full_name' => 'John Doe',
            'password' => 'password123',
            'role' => 'invalid_role'
        ];

        // Act
        $result = $this->userService->createUser($invalidRoleData);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('Invalid role', $result['message']);
    }

    public function testCanSearchUsersByRole()
    {
        // Arrange
        $expectedStudents = [
            ['user_id' => 4, 'school_id' => '2020-001', 'role' => 'student'],
            ['user_id' => 5, 'school_id' => '2020-002', 'role' => 'student']
        ];
        
        $this->mockUserDAO->method('getUsersByRole')
                          ->with('student')
                          ->willReturn($expectedStudents);

        // Act
        $result = $this->userService->getUsersByRole('student');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['users']);
        $this->assertEquals('student', $result['users'][0]['role']);
    }

    public function testCanSearchStudentsByYearAndSection()
    {
        // Arrange
        $expectedStudents = [
            ['user_id' => 4, 'school_id' => '2020-001', 'year_level' => 2, 'section' => 'A']
        ];
        
        $this->mockUserDAO->method('getStudentsByYearSection')
                          ->with(2, 'A')
                          ->willReturn($expectedStudents);

        // Act
        $result = $this->userService->getStudentsByYearSection(2, 'A');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['students']);
    }

    public function testHandlesDatabaseConnectionErrors()
    {
        // Arrange
        $this->mockUserDAO->method('create')
                          ->willThrowException(new \PDOException('Connection lost'));

        $userData = [
            'school_id' => 'STU001',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => 2,
            'section' => 'A'
        ];

        // Act
        $result = $this->userService->createUser($userData);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('database error', strtolower($result['message']));
    }
}