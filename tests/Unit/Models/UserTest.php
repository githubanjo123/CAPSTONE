<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    /** @test */
    public function it_should_create_user_with_data()
    {
        $userData = [
            'user_id' => 1,
            'school_id' => 'TEST123',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
            'password' => 'hashed_password'
        ];

        $user = new User($userData);

        $this->assertEquals(1, $user->getUserId());
        $this->assertEquals('TEST123', $user->getSchoolId());
        $this->assertEquals('John Doe', $user->getFullName());
        $this->assertEquals('student', $user->getRole());
        $this->assertEquals('1st', $user->getYearLevel());
        $this->assertEquals('A', $user->getSection());
        $this->assertEquals('hashed_password', $user->getPassword());
    }

    /** @test */
    public function it_should_create_user_with_empty_data()
    {
        $user = new User();

        $this->assertNull($user->getUserId());
        $this->assertNull($user->getSchoolId());
        $this->assertNull($user->getFullName());
        $this->assertNull($user->getRole());
        $this->assertNull($user->getYearLevel());
        $this->assertNull($user->getSection());
        $this->assertNull($user->getPassword());
    }

    /** @test */
    public function it_should_hydrate_with_new_data()
    {
        $user = new User(['school_id' => 'OLD123']);
        
        $newData = [
            'school_id' => 'NEW456',
            'full_name' => 'Jane Smith',
            'role' => 'faculty'
        ];

        $result = $user->hydrate($newData);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('NEW456', $user->getSchoolId());
        $this->assertEquals('Jane Smith', $user->getFullName());
        $this->assertEquals('faculty', $user->getRole());
    }

    /** @test */
    public function it_should_convert_to_array()
    {
        $userData = [
            'user_id' => 1,
            'school_id' => 'TEST123',
            'full_name' => 'John Doe',
            'role' => 'student',
            'year_level' => '1st',
            'section' => 'A',
            'password' => 'hashed_password',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00'
        ];

        $user = new User($userData);
        $array = $user->toArray();

        $this->assertEquals($userData, $array);
    }

    /** @test */
    public function it_should_set_properties_via_setters()
    {
        $user = new User();

        $user->setUserId(123)
             ->setSchoolId('SETTER123')
             ->setFullName('Setter User')
             ->setRole('admin')
             ->setYearLevel('2nd')
             ->setSection('B')
             ->setPassword('new_password');

        $this->assertEquals(123, $user->getUserId());
        $this->assertEquals('SETTER123', $user->getSchoolId());
        $this->assertEquals('Setter User', $user->getFullName());
        $this->assertEquals('admin', $user->getRole());
        $this->assertEquals('2nd', $user->getYearLevel());
        $this->assertEquals('B', $user->getSection());
        $this->assertEquals('new_password', $user->getPassword());
    }

    /** @test */
    public function it_should_generate_default_password()
    {
        $user = new User([
            'school_id' => 'TEST123',
            'full_name' => 'John Doe'
        ]);

        $defaultPassword = $user->generateDefaultPassword();

        $this->assertEquals('TEST123John Doe', $defaultPassword);
    }

    /** @test */
    public function it_should_throw_exception_when_generating_password_without_required_data()
    {
        $user = new User(['school_id' => 'TEST123']); // Missing full_name

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('School ID and full name are required to generate password');

        $user->generateDefaultPassword();
    }

    /** @test */
    public function it_should_hash_password()
    {
        $user = new User();
        $plainPassword = 'testpassword123';

        $hashedPassword = $user->hashPassword($plainPassword);

        $this->assertNotEquals($plainPassword, $hashedPassword);
        $this->assertTrue(password_verify($plainPassword, $hashedPassword));
    }

    /** @test */
    public function it_should_verify_hashed_password()
    {
        $plainPassword = 'testpassword123';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        
        $user = new User(['password' => $hashedPassword]);

        $this->assertTrue($user->verifyPassword($plainPassword));
        $this->assertFalse($user->verifyPassword('wrongpassword'));
    }

    /** @test */
    public function it_should_verify_plain_text_password_for_legacy_support()
    {
        $plainPassword = 'plaintext123';
        
        $user = new User(['password' => $plainPassword]);

        $this->assertTrue($user->verifyPassword($plainPassword));
        $this->assertFalse($user->verifyPassword('wrongpassword'));
    }

    /** @test */
    public function it_should_return_false_for_password_verification_when_no_password_set()
    {
        $user = new User();

        $this->assertFalse($user->verifyPassword('anypassword'));
    }

    /** @test */
    public function it_should_check_if_user_is_admin()
    {
        $adminUser = new User(['role' => 'admin']);
        $studentUser = new User(['role' => 'student']);

        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($studentUser->isAdmin());
    }

    /** @test */
    public function it_should_check_if_user_is_faculty()
    {
        $facultyUser = new User(['role' => 'faculty']);
        $studentUser = new User(['role' => 'student']);

        $this->assertTrue($facultyUser->isFaculty());
        $this->assertFalse($studentUser->isFaculty());
    }

    /** @test */
    public function it_should_check_if_user_is_student()
    {
        $studentUser = new User(['role' => 'student']);
        $facultyUser = new User(['role' => 'faculty']);

        $this->assertTrue($studentUser->isStudent());
        $this->assertFalse($facultyUser->isStudent());
    }

    /** @test */
    public function it_should_validate_required_fields()
    {
        $user = new User([
            'school_id' => '',
            'full_name' => '',
            'role' => ''
        ]);

        $errors = $user->validate();

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

        $errors = $user->validate();

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

        $errors = $user->validate();

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

        $this->assertEmpty($facultyUser->validate());
        $this->assertEmpty($adminUser->validate());
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

        $this->assertEmpty($validStudent->validate());
        $this->assertEmpty($validFaculty->validate());
        $this->assertEmpty($validAdmin->validate());
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

        $this->assertTrue($validUser->isValid());
        $this->assertFalse($invalidUser->isValid());
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

            $this->assertEmpty($user->validate(), "Validation failed for role: $role");
            $this->assertEquals($role === 'admin', $user->isAdmin());
            $this->assertEquals($role === 'faculty', $user->isFaculty());
            $this->assertEquals($role === 'student', $user->isStudent());
        }
    }

    /** @test */
    public function it_should_handle_null_values_in_arrays()
    {
        $user = new User([
            'user_id' => null,
            'school_id' => null,
            'full_name' => null,
            'role' => null,
            'year_level' => null,
            'section' => null,
            'password' => null,
            'created_at' => null,
            'updated_at' => null
        ]);

        $array = $user->toArray();

        foreach ($array as $value) {
            $this->assertNull($value);
        }
    }

    /** @test */
    public function it_should_maintain_fluent_interface_for_setters()
    {
        $user = new User();

        $result = $user->setSchoolId('TEST123')
                      ->setFullName('Test User')
                      ->setRole('student');

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user, $result); // Should return the same instance
    }
}