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
    public function it_should_verify_password_with_hashed_password()
    {
        $plainPassword = 'test_password';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        
        $user = new User(['password' => $hashedPassword]);

        $this->assertTrue($user->verifyPassword($plainPassword));
        $this->assertFalse($user->verifyPassword('wrong_password'));
    }

    /** @test */
    public function it_should_verify_password_with_plain_text_password()
    {
        $plainPassword = 'test_password';
        
        $user = new User(['password' => $plainPassword]);

        $this->assertTrue($user->verifyPassword($plainPassword));
        $this->assertFalse($user->verifyPassword('wrong_password'));
    }

    /** @test */
    public function it_should_return_false_for_empty_password()
    {
        $user = new User(['password' => '']);

        $this->assertFalse($user->verifyPassword('any_password'));
    }

    /** @test */
    public function it_should_return_false_for_null_password()
    {
        $user = new User(['password' => null]);

        $this->assertFalse($user->verifyPassword('any_password'));
    }
}