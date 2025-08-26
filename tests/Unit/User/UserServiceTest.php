<?php

namespace Tests\Unit\User;

use PHPUnit\Framework\TestCase;
use App\Services\User\UserService;
use App\Interfaces\UserDAOInterface;
use App\Models\User;

class FakeUserDAO implements UserDAOInterface
{
    private array $users = [];
    private int $nextId = 1;

    public function findBySchoolId($school_id): ?User
    {
        foreach ($this->users as $userData) {
            if ($userData['school_id'] === $school_id) { 
                return new User($userData); 
            }
        }
        return null;
    }

    public function findById($user_id): ?User
    {
        foreach ($this->users as $userData) {
            if ($userData['user_id'] === $user_id) { 
                return new User($userData); 
            }
        }
        return null;
    }

    public function getAllUsers(): array
    {
        return array_map(fn($userData) => new User($userData), array_values($this->users));
    }

    public function getUsersByRole($role): array
    {
        $filtered = array_filter($this->users, fn($u) => $u['role'] === $role);
        return array_map(fn($userData) => new User($userData), array_values($filtered));
    }

    public function getStudentsByYearSection($year_level, $section): array
    {
        $filtered = array_filter($this->users, function ($u) use ($year_level, $section) {
            return $u['role'] === 'student' && 
                   ($u['year_level'] ?? null) === $year_level && 
                   ($u['section'] ?? null) === $section;
        });
        return array_map(fn($userData) => new User($userData), array_values($filtered));
    }

    public function create(User $user): ?int
    {
        $userData = $user->toArray();
        $userData['user_id'] = $this->nextId++;
        $userData['created_at'] = date('Y-m-d H:i:s');
        $userData['updated_at'] = date('Y-m-d H:i:s');
        
        $this->users[$userData['user_id']] = $userData;
        return $userData['user_id'];
    }

    public function update($user_id, User $user): bool
    {
        if (!isset($this->users[$user_id])) {
            return false;
        }
        
        $userData = $user->toArray();
        $userData['user_id'] = $user_id;
        $userData['updated_at'] = date('Y-m-d H:i:s');
        
        $this->users[$user_id] = $userData;
        return true;
    }

    public function delete($user_id): bool
    {
        if (isset($this->users[$user_id])) {
            unset($this->users[$user_id]);
            return true;
        }
        return false;
    }

    public function authenticate($school_id, $password): ?User
    {
        return $this->findBySchoolId($school_id);
    }

    public function schoolIdExists($school_id, $exclude_user_id = null): bool
    {
        foreach ($this->users as $userData) {
            if ($userData['school_id'] === $school_id && 
                ($exclude_user_id === null || $userData['user_id'] !== $exclude_user_id)) {
                return true;
            }
        }
        return false;
    }

    public function countByRole($role): int
    {
        return count(array_filter($this->users, fn($u) => $u['role'] === $role));
    }

    // Helper method for tests
    public function addUser(array $userData): void
    {
        $userData['user_id'] = $this->nextId++;
        $this->users[$userData['user_id']] = $userData;
    }
}

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private FakeUserDAO $fakeDAO;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeDAO = new FakeUserDAO();
        $this->userService = new UserService($this->fakeDAO);
    }

    /** @test */
    public function it_should_create_user_successfully()
    {
        $userData = [
            'school_id' => 'TEST001',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ];

        $result = $this->userService->createUser($userData);

        $this->assertTrue($result['success']);
        $this->assertEquals('User created successfully!', $result['message']);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('default_password', $result);
        
        // Verify user was created
        $createdUser = $this->userService->getUserBySchoolId('TEST001');
        $this->assertInstanceOf(User::class, $createdUser);
        $this->assertEquals('John Doe', $createdUser->getFullName());
    }

    /** @test */
    public function it_should_fail_to_create_user_with_duplicate_school_id()
    {
        // Add existing user
        $this->fakeDAO->addUser([
            'school_id' => 'DUPLICATE',
            'full_name' => 'Existing User',
            'role' => 'student'
        ]);

        $userData = [
            'school_id' => 'DUPLICATE',
            'full_name' => 'New User',
            'role' => 'faculty'
        ];

        $result = $this->userService->createUser($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('School ID already exists.', $result['message']);
    }

    /** @test */
    public function it_should_fail_to_create_user_with_invalid_data()
    {
        $userData = [
            'school_id' => '', // Empty school ID
            'full_name' => 'Test User',
            'role' => 'student'
        ];

        $result = $this->userService->createUser($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    /** @test */
    public function it_should_update_user_successfully()
    {
        // Create initial user
        $this->fakeDAO->addUser([
            'school_id' => 'UPDATE_TEST',
            'full_name' => 'Original Name',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ]);

        $updateData = [
            'full_name' => 'Updated Name',
            'year_level' => '2nd',
            'section' => 'B'
        ];

        $result = $this->userService->updateUser(1, $updateData);

        $this->assertTrue($result['success']);
        $this->assertEquals('User updated successfully!', $result['message']);
        
        // Verify update
        $updatedUser = $this->userService->getUserById(1);
        $this->assertEquals('Updated Name', $updatedUser->getFullName());
        $this->assertEquals('2nd', $updatedUser->getYearLevel());
    }

    /** @test */
    public function it_should_fail_to_update_nonexistent_user()
    {
        $updateData = ['full_name' => 'Updated Name'];
        
        $result = $this->userService->updateUser(999, $updateData);

        $this->assertFalse($result['success']);
        $this->assertEquals('User not found.', $result['message']);
    }

    /** @test */
    public function it_should_delete_user_successfully()
    {
        // Create user to delete
        $this->fakeDAO->addUser([
            'school_id' => 'DELETE_TEST',
            'full_name' => 'To Be Deleted',
            'role' => 'student'
        ]);

        $result = $this->userService->deleteUser(1);

        $this->assertTrue($result['success']);
        $this->assertEquals('User deleted successfully!', $result['message']);
        
        // Verify deletion
        $deletedUser = $this->userService->getUserById(1);
        $this->assertNull($deletedUser);
    }

    /** @test */
    public function it_should_fail_to_delete_nonexistent_user()
    {
        $result = $this->userService->deleteUser(999);

        $this->assertFalse($result['success']);
        $this->assertEquals('User not found.', $result['message']);
    }

    /** @test */
    public function it_should_get_all_users()
    {
        // Add test users
        $this->fakeDAO->addUser(['school_id' => 'USER1', 'full_name' => 'User One', 'role' => 'student']);
        $this->fakeDAO->addUser(['school_id' => 'USER2', 'full_name' => 'User Two', 'role' => 'faculty']);

        $users = $this->userService->getAllUsers();

        $this->assertIsArray($users);
        $this->assertCount(2, $users);
        $this->assertInstanceOf(User::class, $users[0]);
        $this->assertInstanceOf(User::class, $users[1]);
    }

    /** @test */
    public function it_should_get_users_by_role()
    {
        // Add test users
        $this->fakeDAO->addUser(['school_id' => 'STU1', 'full_name' => 'Student One', 'role' => 'student']);
        $this->fakeDAO->addUser(['school_id' => 'FAC1', 'full_name' => 'Faculty One', 'role' => 'faculty']);
        $this->fakeDAO->addUser(['school_id' => 'STU2', 'full_name' => 'Student Two', 'role' => 'student']);

        $students = $this->userService->getUsersByRole('student');
        $faculty = $this->userService->getUsersByRole('faculty');

        $this->assertCount(2, $students);
        $this->assertCount(1, $faculty);
        
        foreach ($students as $student) {
            $this->assertInstanceOf(User::class, $student);
            $this->assertEquals('student', $student->getRole());
        }
        
        $this->assertInstanceOf(User::class, $faculty[0]);
        $this->assertEquals('faculty', $faculty[0]->getRole());
    }

    /** @test */
    public function it_should_get_students_by_year_and_section()
    {
        // Add test students
        $this->fakeDAO->addUser([
            'school_id' => 'STU1A', 'full_name' => 'Student 1A', 'role' => 'student', 
            'year_level' => '1st', 'section' => 'A'
        ]);
        $this->fakeDAO->addUser([
            'school_id' => 'STU1B', 'full_name' => 'Student 1B', 'role' => 'student', 
            'year_level' => '1st', 'section' => 'B'
        ]);
        $this->fakeDAO->addUser([
            'school_id' => 'STU1A2', 'full_name' => 'Student 1A2', 'role' => 'student', 
            'year_level' => '1st', 'section' => 'A'
        ]);

        $students1A = $this->userService->getStudentsByYearSection('1st', 'A');
        $students1B = $this->userService->getStudentsByYearSection('1st', 'B');

        $this->assertCount(2, $students1A);
        $this->assertCount(1, $students1B);
        
        foreach ($students1A as $student) {
            $this->assertInstanceOf(User::class, $student);
            $this->assertEquals('1st', $student->getYearLevel());
            $this->assertEquals('A', $student->getSection());
        }
    }

    /** @test */
    public function it_should_convert_users_to_array_for_backward_compatibility()
    {
        $user = new User([
            'user_id' => 1,
            'school_id' => 'TEST',
            'full_name' => 'Test User',
            'role' => 'student'
        ]);

        $userArray = $this->userService->usersToArray($user);
        
        $this->assertIsArray($userArray);
        $this->assertEquals('TEST', $userArray['school_id']);
        $this->assertEquals('Test User', $userArray['full_name']);

        // Test with array of users
        $users = [$user, $user];
        $usersArray = $this->userService->usersToArray($users);
        
        $this->assertIsArray($usersArray);
        $this->assertCount(2, $usersArray);
        $this->assertIsArray($usersArray[0]);
        $this->assertEquals('TEST', $usersArray[0]['school_id']);
    }

    // Business Logic Tests (moved from User model)

    /** @test */
    public function it_should_generate_default_password()
    {
        $user = new User([
            'school_id' => 'TEST123',
            'full_name' => 'John Doe'
        ]);

        $defaultPassword = $this->userService->generateDefaultPassword($user);

        $this->assertEquals('TEST123John Doe', $defaultPassword);
    }

    /** @test */
    public function it_should_throw_exception_when_generating_password_without_required_data()
    {
        $user = new User(['school_id' => 'TEST123']); // Missing full_name

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('School ID and full name are required to generate password');

        $this->userService->generateDefaultPassword($user);
    }

    /** @test */
    public function it_should_hash_password()
    {
        $plainPassword = 'testpassword123';

        $hashedPassword = $this->userService->hashPassword($plainPassword);

        $this->assertNotEquals($plainPassword, $hashedPassword);
        $this->assertTrue(password_verify($plainPassword, $hashedPassword));
    }

    /** @test */
    public function it_should_check_if_user_is_admin()
    {
        $adminUser = new User(['role' => 'admin']);
        $studentUser = new User(['role' => 'student']);

        $this->assertTrue($this->userService->isAdmin($adminUser));
        $this->assertFalse($this->userService->isAdmin($studentUser));
    }

    /** @test */
    public function it_should_check_if_user_is_faculty()
    {
        $facultyUser = new User(['role' => 'faculty']);
        $studentUser = new User(['role' => 'student']);

        $this->assertTrue($this->userService->isFaculty($facultyUser));
        $this->assertFalse($this->userService->isFaculty($studentUser));
    }

    /** @test */
    public function it_should_check_if_user_is_student()
    {
        $studentUser = new User(['role' => 'student']);
        $facultyUser = new User(['role' => 'faculty']);

        $this->assertTrue($this->userService->isStudent($studentUser));
        $this->assertFalse($this->userService->isStudent($facultyUser));
    }

    /** @test */
    public function it_should_validate_required_fields()
    {
        $user = new User([
            'school_id' => '',
            'full_name' => '',
            'role' => ''
        ]);

        $errors = $this->userService->validate($user);

        $this->assertContains('School ID is required', $errors);
        $this->assertContains('Full name is required', $errors);
        $this->assertContains('Role is required', $errors);
    }

    /** @test */
    public function it_should_validate_role_values()
    {
        $user = new User([
            'school_id' => 'TEST123',
            'full_name' => 'Test User',
            'role' => 'invalid_role'
        ]);

        $errors = $this->userService->validate($user);

        $this->assertContains('Invalid role', $errors);
    }

    /** @test */
    public function it_should_validate_student_specific_fields()
    {
        $user = new User([
            'school_id' => 'TEST123',
            'full_name' => 'Test Student',
            'role' => 'student',
            'year_level' => '',
            'section' => ''
        ]);

        $errors = $this->userService->validate($user);

        $this->assertContains('Year level is required for students', $errors);
        $this->assertContains('Section is required for students', $errors);
    }

    /** @test */
    public function it_should_not_require_year_level_and_section_for_non_students()
    {
        $facultyUser = new User([
            'school_id' => 'FAC123',
            'full_name' => 'Faculty Member',
            'role' => 'faculty'
        ]);

        $adminUser = new User([
            'school_id' => 'ADM123',
            'full_name' => 'Admin User',
            'role' => 'admin'
        ]);

        $this->assertEmpty($this->userService->validate($facultyUser));
        $this->assertEmpty($this->userService->validate($adminUser));
    }

    /** @test */
    public function it_should_pass_validation_with_valid_data()
    {
        $validStudent = new User([
            'school_id' => 'STU123',
            'full_name' => 'Valid Student',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ]);

        $validFaculty = new User([
            'school_id' => 'FAC123',
            'full_name' => 'Valid Faculty',
            'role' => 'faculty'
        ]);

        $validAdmin = new User([
            'school_id' => 'ADM123',
            'full_name' => 'Valid Admin',
            'role' => 'admin'
        ]);

        $this->assertEmpty($this->userService->validate($validStudent));
        $this->assertEmpty($this->userService->validate($validFaculty));
        $this->assertEmpty($this->userService->validate($validAdmin));
    }

    /** @test */
    public function it_should_check_if_user_is_valid()
    {
        $validUser = new User([
            'school_id' => 'VALID123',
            'full_name' => 'Valid User',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A'
        ]);

        $invalidUser = new User([
            'school_id' => '',
            'full_name' => 'Invalid User',
            'role' => 'student'
        ]);

        $this->assertTrue($this->userService->isValid($validUser));
        $this->assertFalse($this->userService->isValid($invalidUser));
    }

    /** @test */
    public function it_should_handle_all_roles_correctly()
    {
        $roles = ['admin', 'faculty', 'student'];

        foreach ($roles as $role) {
            $user = new User([
                'school_id' => 'TEST123',
                'full_name' => 'Test User',
                'role' => $role,
                'year_level' => $role === 'student' ? '1st' : null,
                'section' => $role === 'student' ? 'A' : null
            ]);

            $this->assertEmpty($this->userService->validate($user), "Validation failed for role: $role");
            $this->assertEquals($role === 'admin', $this->userService->isAdmin($user));
            $this->assertEquals($role === 'faculty', $this->userService->isFaculty($user));
            $this->assertEquals($role === 'student', $this->userService->isStudent($user));
        }
    }
}