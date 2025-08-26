<?php

namespace Tests\Unit\DAO;

use PHPUnit\Framework\TestCase;
use App\DAO\Auth\UserDAO;
use App\Models\User;
use PDO;
use PDOStatement;

class UserDAOTest extends TestCase
{
    private $pdoMock;
    private $pdoStatementMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock PDO and PDOStatement
        $this->pdoMock = $this->createMock(PDO::class);
        $this->pdoStatementMock = $this->createMock(PDOStatement::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /** @test */
    public function it_should_find_user_by_school_id()
    {
        $schoolId = 'UT_SID_' . uniqid();
        $expectedUserData = [
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
            'password' => 'hashed_password',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE school_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$schoolId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedUserData);

        $found = $userDAO->findBySchoolId($schoolId);
        
        // Should return User object, not array
        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($schoolId, $found->getSchoolId());
        $this->assertEquals('John Doe', $found->getFullName());
        $this->assertEquals('student', $found->getRole());
    }

    /** @test */
    public function it_should_return_null_when_user_not_found_by_school_id()
    {
        $schoolId = 'NON_EXIST_' . uniqid();
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE school_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$schoolId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $found = $userDAO->findBySchoolId($schoolId);
        
        $this->assertNull($found);
    }

    /** @test */
    public function it_should_find_user_by_id()
    {
        $userId = 123;
        $expectedUserData = [
            'user_id' => $userId,
            'school_id' => 'TEST_123',
            'full_name' => 'Jane Smith',
            'role' => 'faculty',
            'year_level' => null,
            'section' => null,
            'password' => 'hashed_password',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE user_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$userId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedUserData);

        $found = $userDAO->findById($userId);
        
        // Should return User object, not array
        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($userId, $found->getUserId());
        $this->assertEquals('Jane Smith', $found->getFullName());
        $this->assertEquals('faculty', $found->getRole());
    }

    /** @test */
    public function it_should_return_null_when_user_not_found_by_id()
    {
        $userId = 999;
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE user_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$userId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $found = $userDAO->findById($userId);
        
        $this->assertNull($found);
    }

    /** @test */
    public function it_should_get_all_users()
    {
        $expectedUsersData = [
            [
                'user_id' => 1,
                'school_id' => 'TEST_001',
                'full_name' => 'User One',
                'role' => 'student',
                'year_level' => '1st',
                'section' => 'A',
                'password' => 'hashed',
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00'
            ],
            [
                'user_id' => 2,
                'school_id' => 'TEST_002',
                'full_name' => 'User Two',
                'role' => 'faculty',
                'year_level' => null,
                'section' => null,
                'password' => 'hashed',
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00'
            ]
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users ORDER BY full_name ASC")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute');
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedUsersData);

        $users = $userDAO->getAllUsers();
        
        // Should return array of User objects
        $this->assertIsArray($users);
        $this->assertCount(2, $users);
        $this->assertInstanceOf(User::class, $users[0]);
        $this->assertInstanceOf(User::class, $users[1]);
        $this->assertEquals('User One', $users[0]->getFullName());
        $this->assertEquals('User Two', $users[1]->getFullName());
    }

    /** @test */
    public function it_should_create_user_successfully()
    {
        $userData = [
            'school_id' => 'NEW_USER_123',
            'full_name' => 'New User',
            'role' => 'student',
            'year_level' => '2nd',
            'section' => 'B',
            'password' => 'hashed_password'
        ];
        
        $user = new User($userData);
        $expectedUserId = 999;
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("INSERT INTO users (school_id, full_name, password, role, year_level, section, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                'NEW_USER_123',
                'New User', 
                'hashed_password',
                'student',
                '2nd',
                'B'
            ])
            ->willReturn(true);
            
        $this->pdoMock
            ->expects($this->once())
            ->method('lastInsertId')
            ->willReturn((string)$expectedUserId);

        $result = $userDAO->create($user);
        
        $this->assertEquals($expectedUserId, $result);
    }

    /** @test */
    public function it_should_return_null_when_create_fails()
    {
        $userData = [
            'school_id' => 'FAIL_USER',
            'full_name' => 'Fail User',
            'role' => 'student',
            'password' => 'hashed'
        ];
        
        $user = new User($userData);
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $result = $userDAO->create($user);
        
        $this->assertNull($result);
    }

    /** @test */
    public function it_should_update_user_successfully()
    {
        $userId = 123;
        $userData = [
            'school_id' => 'UPDATED_USER',
            'full_name' => 'Updated User',
            'role' => 'faculty',
            'password' => 'new_hashed_password'
        ];
        
        $user = new User($userData);
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("UPDATE users SET 
                    school_id = ?, 
                    full_name = ?, 
                    password = ?, 
                    role = ?, 
                    year_level = ?, 
                    section = ?, 
                    updated_at = NOW() 
                    WHERE user_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                'UPDATED_USER',
                'Updated User',
                'new_hashed_password',
                'faculty',
                null, // year_level for faculty
                null, // section for faculty
                $userId
            ])
            ->willReturn(true);

        $result = $userDAO->update($userId, $user);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_should_delete_user_successfully()
    {
        $userId = 123;
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("DELETE FROM users WHERE user_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$userId])
            ->willReturn(true);

        $result = $userDAO->delete($userId);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_should_check_if_school_id_exists()
    {
        $schoolId = 'EXISTING_ID';
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT COUNT(*) FROM users WHERE school_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$schoolId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        $result = $userDAO->schoolIdExists($schoolId);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_should_authenticate_by_returning_user()
    {
        $schoolId = 'AUTH_USER';
        $password = 'test_password';
        $userData = [
            'user_id' => 1,
            'school_id' => $schoolId,
            'full_name' => 'Auth User',
            'role' => 'student',
            'password' => 'hashed_password',
            'year_level' => '1st',
            'section' => 'A',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ];
        
        // Create UserDAO with mock PDO
        $userDAO = $this->createUserDAOWithMockPDO();
        
        // Set up mock expectations (authenticate just calls findBySchoolId)
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE school_id = ?")
            ->willReturn($this->pdoStatementMock);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('execute')
            ->with([$schoolId]);
            
        $this->pdoStatementMock
            ->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($userData);

        $result = $userDAO->authenticate($schoolId, $password);
        
        // Should return User object (business logic is now in User model)
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($schoolId, $result->getSchoolId());
    }

    /**
     * Helper method to create a UserDAO with a mock PDO
     */
    private function createUserDAOWithMockPDO()
    {
        $userDAO = new UserDAO();
        
        // Use reflection to inject the mock PDO
        $reflection = new \ReflectionClass($userDAO);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($userDAO, $this->pdoMock);
        
        return $userDAO;
    }
}