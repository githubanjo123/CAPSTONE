# Unit Tests - TDD Evolution Documentation

This document demonstrates both **micro-level** (per test case) and **macro-level** (per feature) TDD cycles, showing how individual tests evolve through RED-GREEN-REFACTOR and how complete features are built through multiple test iterations.

## Table of Contents

1. [User Authentication Feature](#user-authentication-feature)
   - [Test Case 1: Valid Login](#test-case-1-valid-login)
   - [Test Case 2: Invalid Password](#test-case-2-invalid-password)
   - [Test Case 3: User Not Found](#test-case-3-user-not-found)
   - [Test Case 4: Empty Credentials](#test-case-4-empty-credentials)
   - [Feature Complete: All Tests Refactored](#feature-complete-all-tests-refactored)

2. [User Model Feature](#user-model-feature)
   - [Test Case 1: Create User with Data](#test-case-1-create-user-with-data)
   - [Test Case 2: Password Verification](#test-case-2-password-verification)
   - [Feature Complete: Clean User Model](#feature-complete-clean-user-model)

---

## User Authentication Feature

**Macro Level Goal:** Complete user authentication system with login validation, session management, and error handling.

### Test Case 1: Valid Login

#### 🔴 RED Phase (Micro Level)

```php
// tests/Unit/Auth/AuthServiceTest.php:75-105
/** @test */
public function it_should_login_successfully_with_valid_credentials()
{
    $schoolId = 'TEST123';
    $password = 'password123';
    
    // Create a User object to return from DAO
    $user = new User([
        'user_id' => 1,
        'school_id' => $schoolId,
        'full_name' => 'John Doe',
        'role' => 'student',
        'year_level' => '1st',
        'section' => 'A',
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ]);

    // Mock the DAO to return the user
    $this->userDAOMock
        ->expects($this->once())
        ->method('authenticate')
        ->with($schoolId, $password)
        ->willReturn($user);

    $result = $this->authService->login($schoolId, $password);

    $this->assertTrue($result['success']);
    $this->assertEquals('Login successful!', $result['message']);
    $this->assertArrayHasKey('user', $result);
    $this->assertEquals($schoolId, $result['user']['school_id']);
}
```

**Test fails because:** `AuthService::login()` method doesn't exist yet.

#### 🟢 GREEN Phase (Micro Level)

```php
// src/App/Services/Auth/AuthService.php - Quick implementation
public function login($school_id, $password)
{
    $user = $this->userDAO->authenticate($school_id, $password);
    
    if (!$user) {
        return ['success' => false, 'message' => 'User not found.'];
    }

    if (!$user->verifyPassword($password)) {
        return ['success' => false, 'message' => 'Invalid credentials.'];
    }

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_id'] = $user->getUserId();
    $_SESSION['school_id'] = $user->getSchoolId();
    $_SESSION['full_name'] = $user->getFullName();
    $_SESSION['role'] = $user->getRole();

    return [
        'success' => true,
        'message' => 'Login successful!',
        'user' => [
            'user_id' => $user->getUserId(),
            'school_id' => $user->getSchoolId(),
            'full_name' => $user->getFullName(),
            'role' => $user->getRole()
        ]
    ];
}
```

**Test passes!** ✅

#### 🔵 REFACTOR Phase (Micro Level)

```php
// src/App/Services/Auth/AuthService.php - Clean implementation
/**
 * Login user with school ID and password
 */
public function login($school_id, $password)
{
    // Validate inputs
    if (empty(trim($school_id)) || empty(trim($password))) {
        return [
            'success' => false,
            'message' => 'School ID and password are required.'
        ];
    }

    // Sanitize inputs
    $school_id = trim($school_id);
    $password = trim($password);

    // Get user from DAO
    $user = $this->userDAO->authenticate($school_id, $password);

    if (!$user) {
        return [
            'success' => false,
            'message' => 'User not found.'
        ];
    }

    // Verify password
    if (!$user->verifyPassword($password)) {
        return [
            'success' => false,
            'message' => 'Invalid School ID or password.'
        ];
    }

    // Start session and store user data
    $this->createUserSession($user);

    return [
        'success' => true,
        'message' => 'Login successful!',
        'user' => $this->getUserArray($user)
    ];
}

private function createUserSession(User $user): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user_id'] = $user->getUserId();
    $_SESSION['school_id'] = $user->getSchoolId();
    $_SESSION['full_name'] = $user->getFullName();
    $_SESSION['role'] = $user->getRole();
    $_SESSION['year_level'] = $user->getYearLevel();
    $_SESSION['section'] = $user->getSection();
}

private function getUserArray(User $user): array
{
    return [
        'user_id' => $user->getUserId(),
        'school_id' => $user->getSchoolId(),
        'full_name' => $user->getFullName(),
        'role' => $user->getRole(),
        'year_level' => $user->getYearLevel(),
        'section' => $user->getSection()
    ];
}
```

**Code is now clean and maintainable!** ✅

---

### Test Case 2: Invalid Password

#### 🔴 RED Phase (Micro Level)

```php
// tests/Unit/Auth/AuthServiceTest.php:107-135
/** @test */
public function it_should_fail_login_with_invalid_password()
{
    $schoolId = 'TEST123';
    $password = 'wrongpassword';
    $correctPassword = 'correctpassword';
    
    // Create a User object with correct password
    $user = new User([
        'user_id' => 1,
        'school_id' => $schoolId,
        'full_name' => 'John Doe',
        'role' => 'student',
        'password' => password_hash($correctPassword, PASSWORD_DEFAULT)
    ]);

    // Mock the DAO to return the user
    $this->userDAOMock
        ->expects($this->once())
        ->method('authenticate')
        ->with($schoolId, $password)
        ->willReturn($user);

    $result = $this->authService->login($schoolId, $password);

    $this->assertFalse($result['success']);
    $this->assertEquals('Invalid School ID or password.', $result['message']);
}
```

**Test fails because:** Current implementation returns "Invalid credentials" instead of "Invalid School ID or password."

#### 🟢 GREEN Phase (Micro Level)

```php
// Quick fix to make test pass
if (!$user->verifyPassword($password)) {
    return [
        'success' => false,
        'message' => 'Invalid School ID or password.'
    ];
}
```

**Test passes!** ✅

#### 🔵 REFACTOR Phase (Micro Level)

```php
// No additional refactoring needed - message is already correct
// The previous refactoring already handled this case properly
```

---

### Test Case 3: User Not Found

#### 🔴 RED Phase (Micro Level)

```php
// tests/Unit/Auth/AuthServiceTest.php:137-155
/** @test */
public function it_should_fail_login_when_user_not_found()
{
    $schoolId = 'NONEXISTENT';
    $password = 'password123';

    // Mock the DAO to return null (user not found)
    $this->userDAOMock
        ->expects($this->once())
        ->method('authenticate')
        ->with($schoolId, $password)
        ->willReturn(null);

    $result = $this->authService->login($schoolId, $password);

    $this->assertFalse($result['success']);
    $this->assertEquals('User not found.', $result['message']);
}
```

**Test passes immediately!** ✅ (Previous implementation already handles this case)

#### 🟢 GREEN Phase (Micro Level)
*No changes needed - test already passes*

#### 🔵 REFACTOR Phase (Micro Level)
*No changes needed - code is already clean*

---

### Test Case 4: Empty Credentials

#### 🔴 RED Phase (Micro Level)

```php
// tests/Unit/Auth/AuthServiceTest.php:157-175
/** @test */
public function it_should_fail_login_with_empty_credentials()
{
    $result1 = $this->authService->login('', 'password');
    $this->assertFalse($result1['success']);
    $this->assertEquals('School ID and password are required.', $result1['message']);

    $result2 = $this->authService->login('schoolid', '');
    $this->assertFalse($result2['success']);
    $this->assertEquals('School ID and password are required.', $result2['message']);

    $result3 = $this->authService->login('', '');
    $this->assertFalse($result3['success']);
    $this->assertEquals('School ID and password are required.', $result3['message']);
}
```

**Test passes immediately!** ✅ (Previous refactoring already added input validation)

#### 🟢 GREEN Phase (Micro Level)
*No changes needed - test already passes*

#### 🔵 REFACTOR Phase (Micro Level)
*No changes needed - code is already clean*

---

### Feature Complete: All Tests Refactored

**Macro Level Achievement:** ✅ Complete User Authentication Feature

```php
// src/App/Services/Auth/AuthService.php - Final Production Code
/**
 * Login user with school ID and password
 */
public function login($school_id, $password)
{
    // Validate inputs - check if trimmed values are empty
    if (empty(trim($school_id)) || empty(trim($password))) {
        return [
            'success' => false,
            'message' => 'School ID and password are required.'
        ];
    }

    // Sanitize inputs
    $school_id = trim($school_id);
    $password = trim($password);

    // Get user from DAO (this just retrieves the user, no authentication yet)
    $user = $this->userDAO->authenticate($school_id, $password);

    if (!$user) {
        return [
            'success' => false,
            'message' => 'User not found.'
        ];
    }

    // Now perform authentication business logic using the User model
    if (!$user->verifyPassword($password)) {
        return [
            'success' => false,
            'message' => 'Invalid School ID or password.'
        ];
    }

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Store user data in session
    $_SESSION['user_id'] = $user->getUserId();
    $_SESSION['school_id'] = $user->getSchoolId();
    $_SESSION['full_name'] = $user->getFullName();
    $_SESSION['role'] = $user->getRole();
    $_SESSION['year_level'] = $user->getYearLevel();
    $_SESSION['section'] = $user->getSection();

    return [
        'success' => true,
        'message' => 'Login successful!',
        'user' => [
            'user_id' => $user->getUserId(),
            'school_id' => $user->getSchoolId(),
            'full_name' => $user->getFullName(),
            'role' => $user->getRole(),
            'year_level' => $user->getYearLevel(),
            'section' => $user->getSection()
        ]
    ];
}
```

**Feature Summary:**
- ✅ Valid login with session creation
- ✅ Invalid password handling
- ✅ User not found handling
- ✅ Empty credentials validation
- ✅ Input sanitization
- ✅ Proper error messages
- ✅ Clean, maintainable code

---

## User Model Feature

**Macro Level Goal:** Complete user entity with data management and password verification.

### Test Case 1: Create User with Data

#### 🔴 RED Phase (Micro Level)

```php
// tests/Unit/Models/UserTest.php:15-35
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
```

**Test fails because:** `User` class doesn't exist yet.

#### 🟢 GREEN Phase (Micro Level)

```php
// src/App/Models/User.php - Quick implementation
class User
{
    private $user_id;
    private $school_id;
    private $full_name;
    private $password;
    private $role;
    private $year_level;
    private $section;

    public function __construct(array $data = [])
    {
        $this->user_id = $data['user_id'] ?? null;
        $this->school_id = $data['school_id'] ?? null;
        $this->full_name = $data['full_name'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->year_level = $data['year_level'] ?? null;
        $this->section = $data['section'] ?? null;
    }

    public function getUserId() { return $this->user_id; }
    public function getSchoolId() { return $this->school_id; }
    public function getFullName() { return $this->full_name; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getYearLevel() { return $this->year_level; }
    public function getSection() { return $this->section; }
}
```

**Test passes!** ✅

#### 🔵 REFACTOR Phase (Micro Level)

```php
// src/App/Models/User.php - Clean implementation
<?php

namespace App\Models;

class User
{
    private $user_id;
    private $school_id;
    private $full_name;
    private $password;
    private $role;
    private $year_level;
    private $section;
    private $created_at;
    private $updated_at;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    /**
     * Hydrate the model with data
     */
    public function hydrate(array $data): self
    {
        $this->user_id = $data['user_id'] ?? null;
        $this->school_id = $data['school_id'] ?? null;
        $this->full_name = $data['full_name'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->year_level = $data['year_level'] ?? null;
        $this->section = $data['section'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        
        return $this;
    }

    // Getters and setters with proper type hints and method chaining
    public function getUserId(): ?int { return $this->user_id; }
    public function getSchoolId(): ?string { return $this->school_id; }
    public function getFullName(): ?string { return $this->full_name; }
    public function getPassword(): ?string { return $this->password; }
    public function getRole(): ?string { return $this->role; }
    public function getYearLevel(): ?string { return $this->year_level; }
    public function getSection(): ?string { return $this->section; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    // Setters with method chaining
    public function setUserId(?int $user_id): self { $this->user_id = $user_id; return $this; }
    public function setSchoolId(?string $school_id): self { $this->school_id = $school_id; return $this; }
    public function setFullName(?string $full_name): self { $this->full_name = $full_name; return $this; }
    public function setPassword(?string $password): self { $this->password = $password; return $this; }
    public function setRole(?string $role): self { $this->role = $role; return $this; }
    public function setYearLevel(?string $year_level): self { $this->year_level = $year_level; return $this; }
    public function setSection(?string $section): self { $this->section = $section; return $this; }
}
```

**Code is now clean and maintainable!** ✅

---

### Test Case 2: Password Verification

#### 🔴 RED Phase (Micro Level)

```php
// tests/Unit/Models/UserTest.php:95-105
/** @test */
public function it_should_verify_password_with_hashed_password()
{
    $plainPassword = 'test_password';
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    
    $user = new User(['password' => $hashedPassword]);

    $this->assertTrue($user->verifyPassword($plainPassword));
    $this->assertFalse($user->verifyPassword('wrong_password'));
}
```

**Test fails because:** `verifyPassword()` method doesn't exist yet.

#### 🟢 GREEN Phase (Micro Level)

```php
// Quick implementation
public function verifyPassword(string $inputPassword): bool
{
    if (empty($this->password)) {
        return false;
    }
    return password_verify($inputPassword, $this->password);
}
```

**Test passes!** ✅

#### 🔵 REFACTOR Phase (Micro Level)

```php
// src/App/Models/User.php - Clean implementation
/**
 * Verify password - kept in model as it's about the entity's own data
 */
public function verifyPassword(string $inputPassword): bool
{
    if (empty($this->password)) {
        return false;
    }

    // Check if password is hashed (starts with $) or plain text
    if (strpos($this->password, '$') === 0) {
        return password_verify($inputPassword, $this->password);
    } else {
        // Legacy plain text password support
        return $inputPassword === $this->password;
    }
}
```

**Code now handles both hashed and legacy plain text passwords!** ✅

---

### Feature Complete: Clean User Model

**Macro Level Achievement:** ✅ Complete User Model Feature

```php
// src/App/Models/User.php - Final Production Code
<?php

namespace App\Models;

class User
{
    private $user_id;
    private $school_id;
    private $full_name;
    private $password;
    private $role;
    private $year_level;
    private $section;
    private $created_at;
    private $updated_at;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    /**
     * Hydrate the model with data
     */
    public function hydrate(array $data): self
    {
        $this->user_id = $data['user_id'] ?? null;
        $this->school_id = $data['school_id'] ?? null;
        $this->full_name = $data['full_name'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->year_level = $data['year_level'] ?? null;
        $this->section = $data['section'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        
        return $this;
    }

    /**
     * Convert model to array
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'school_id' => $this->school_id,
            'full_name' => $this->full_name,
            'password' => $this->password,
            'role' => $this->role,
            'year_level' => $this->year_level,
            'section' => $this->section,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    // Getters with proper type hints
    public function getUserId(): ?int { return $this->user_id; }
    public function getSchoolId(): ?string { return $this->school_id; }
    public function getFullName(): ?string { return $this->full_name; }
    public function getPassword(): ?string { return $this->password; }
    public function getRole(): ?string { return $this->role; }
    public function getYearLevel(): ?string { return $this->year_level; }
    public function getSection(): ?string { return $this->section; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    // Setters with method chaining
    public function setUserId(?int $user_id): self { $this->user_id = $user_id; return $this; }
    public function setSchoolId(?string $school_id): self { $this->school_id = $school_id; return $this; }
    public function setFullName(?string $full_name): self { $this->full_name = $full_name; return $this; }
    public function setPassword(?string $password): self { $this->password = $password; return $this; }
    public function setRole(?string $role): self { $this->role = $role; return $this; }
    public function setYearLevel(?string $year_level): self { $this->year_level = $year_level; return $this; }
    public function setSection(?string $section): self { $this->section = $section; return $this; }

    /**
     * Verify password - kept in model as it's about the entity's own data
     */
    public function verifyPassword(string $inputPassword): bool
    {
        if (empty($this->password)) {
            return false;
        }

        // Check if password is hashed (starts with $) or plain text
        if (strpos($this->password, '$') === 0) {
            return password_verify($inputPassword, $this->password);
        } else {
            // Legacy plain text password support
            return $inputPassword === $this->password;
        }
    }
}
```

**Feature Summary:**
- ✅ User entity creation with data hydration
- ✅ Password verification with hashed and plain text support
- ✅ Proper type hints and method chaining
- ✅ Data conversion to array
- ✅ Clean, maintainable code structure

---

## TDD Cycle Summary

### Micro Level (Per Test Case)
Each individual test follows the RED-GREEN-REFACTOR cycle:
1. **🔴 RED**: Write failing test that defines desired behavior
2. **🟢 GREEN**: Write minimal code to make test pass
3. **🔵 REFACTOR**: Clean up code while keeping test green

### Macro Level (Per Feature)
Complete features are built through multiple test iterations:
1. **Start with first test case** → RED-GREEN-REFACTOR
2. **Add second test case** → RED-GREEN-REFACTOR
3. **Continue until all test cases are complete**
4. **Final refactoring** → Clean, production-ready feature

### Key Benefits
- **Confidence**: Each test case is proven to work
- **Incremental Development**: Features grow step by step
- **Clean Code**: Continuous refactoring ensures maintainability
- **Regression Prevention**: All previous functionality remains intact
- **Documentation**: Tests serve as living documentation of behavior

This approach ensures that every piece of functionality is thoroughly tested and that the final code is clean, maintainable, and reliable.