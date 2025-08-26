# Unit Tests - TDD Evolution Documentation

This document chronicles the Test-Driven Development (TDD) evolution of the project, showing the RED-GREEN-REFACTOR cycle for each feature implemented.

## Table of Contents

1. [User Model](#user-model)
2. [User DAO](#user-dao)
3. [Auth Service](#auth-service)
4. [User Service](#user-service)
5. [Router](#router)
6. [Admin Controller](#admin-controller)

---

## User Model

**Test Class:** `tests/Unit/Models/UserTest.php`  
**Production Code:** `src/App/Models/User.php`  
**Architectural Layer:** Model

### Iteration 1 – User Entity Creation

#### 🔴 RED Phase

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

#### 🟢 GREEN Phase

```php
// Minimal implementation to make test pass
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

#### 🔵 REFACTOR Phase

```php
// src/App/Models/User.php:1-95
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

### Iteration 2 – Password Verification

#### 🔴 RED Phase

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

#### 🟢 GREEN Phase

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

#### 🔵 REFACTOR Phase

```php
// src/App/Models/User.php:75-95
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

---

## User DAO

**Test Class:** `tests/Unit/DAO/UserDAOTest.php`  
**Production Code:** `src/App/DAO/Auth/UserDAO.php`  
**Architectural Layer:** DAO (Data Access Object)

### Iteration 1 – Find User by School ID

#### 🔴 RED Phase

```php
// tests/Unit/DAO/UserDAOTest.php:25-55
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
```

#### 🟢 GREEN Phase

```php
// Minimal implementation
public function findBySchoolId($school_id): ?User
{
    $stmt = $this->db->prepare("SELECT * FROM users WHERE school_id = ?");
    $stmt->execute([$school_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $data ? new User($data) : null;
}
```

#### 🔵 REFACTOR Phase

```php
// src/App/DAO/Auth/UserDAO.php:20-30
/**
 * Find user by school ID and return User model
 */
public function findBySchoolId($school_id): ?User
{
    try {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE school_id = ?");
        $stmt->execute([$school_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? new User($data) : null;
    } catch (PDOException $e) {
        return null;
    }
}
```

### Iteration 2 – Create User

#### 🔴 RED Phase

```php
// tests/Unit/DAO/UserDAOTest.php:200-230
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
```

#### 🟢 GREEN Phase

```php
// Quick implementation
public function create(User $user): ?int
{
    $sql = "INSERT INTO users (school_id, full_name, password, role, year_level, section, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
        $user->getSchoolId(),
        $user->getFullName(),
        $user->getPassword(),
        $user->getRole(),
        $user->getYearLevel(),
        $user->getSection()
    ]);

    return $result ? (int)$this->db->lastInsertId() : null;
}
```

#### 🔵 REFACTOR Phase

```php
// src/App/DAO/Auth/UserDAO.php:85-105
/**
 * Create new user from User model
 */
public function create(User $user): ?int
{
    try {
        $sql = "INSERT INTO {$this->table} (school_id, full_name, password, role, year_level, section, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $user->getSchoolId(),
            $user->getFullName(),
            $user->getPassword(),
            $user->getRole(),
            $user->getRole() === 'student' ? $user->getYearLevel() : null,
            $user->getRole() === 'student' ? $user->getSection() : null
        ]);

        return $result ? (int)$this->db->lastInsertId() : null;
    } catch (PDOException $e) {
        return null;
    }
}
```

---

## Auth Service

**Test Class:** `tests/Unit/Auth/AuthServiceTest.php`  
**Production Code:** `src/App/Services/Auth/AuthService.php`  
**Architectural Layer:** Service

### Iteration 1 – User Login

#### 🔴 RED Phase

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

    // Start session for the test
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $result = $this->authService->login($schoolId, $password);

    $this->assertTrue($result['success']);
    $this->assertEquals('Login successful!', $result['message']);
    $this->assertArrayHasKey('user', $result);
    $this->assertEquals($schoolId, $result['user']['school_id']);
    $this->assertEquals('John Doe', $result['user']['full_name']);
    $this->assertEquals('student', $result['user']['role']);
}
```

#### 🟢 GREEN Phase

```php
// Quick implementation
public function login($school_id, $password)
{
    if (empty(trim($school_id)) || empty(trim($password))) {
        return ['success' => false, 'message' => 'School ID and password are required.'];
    }

    $user = $this->userDAO->authenticate($school_id, $password);
    
    if (!$user) {
        return ['success' => false, 'message' => 'User not found.'];
    }

    if (!$user->verifyPassword($password)) {
        return ['success' => false, 'message' => 'Invalid School ID or password.'];
    }

    // Start session and store user data
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

#### 🔵 REFACTOR Phase

```php
// src/App/Services/Auth/AuthService.php:15-65
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

### Iteration 2 – Create User with Business Logic

#### 🔴 RED Phase

```php
// tests/Unit/Auth/AuthServiceTest.php:180-210
/** @test */
public function it_should_create_user_successfully()
{
    $userData = [
        'school_id' => 'NEW123',
        'full_name' => 'New User',
        'role' => 'student',
        'year_level' => '1st',
        'section' => 'A'
    ];

    // Mock DAO methods
    $this->userDAOMock
        ->expects($this->once())
        ->method('schoolIdExists')
        ->with('NEW123')
        ->willReturn(false);

    $this->userDAOMock
        ->expects($this->once())
        ->method('create')
        ->willReturn(123);

    $result = $this->authService->createUser($userData);

    $this->assertTrue($result['success']);
    $this->assertEquals('User created successfully', $result['message']);
    $this->assertEquals(123, $result['user_id']);
    $this->assertArrayHasKey('default_password', $result);
}
```

#### 🟢 GREEN Phase

```php
// Quick implementation
public function createUser(array $userData): array
{
    $user = new User($userData);
    
    // Check if school ID exists
    if ($this->userDAO->schoolIdExists($user->getSchoolId())) {
        return ['success' => false, 'message' => 'School ID already exists'];
    }

    // Generate default password
    $defaultPassword = $user->getSchoolId() . $user->getFullName();
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    $user->setPassword($hashedPassword);

    $userId = $this->userDAO->create($user);

    if ($userId) {
        return [
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $userId,
            'default_password' => $defaultPassword
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to create user'];
    }
}
```

#### 🔵 REFACTOR Phase

```php
// src/App/Services/Auth/AuthService.php:70-110
/**
 * Create a new user with business logic
 */
public function createUser(array $userData): array
{
    // Create User model from data
    $user = new User($userData);

    // Validate user data
    $validationErrors = $this->userService->validate($user);
    if (!empty($validationErrors)) {
        return [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validationErrors
        ];
    }

    // Check if school ID already exists
    if ($this->userDAO->schoolIdExists($user->getSchoolId())) {
        return [
            'success' => false,
            'message' => 'School ID already exists'
        ];
    }

    // Generate and hash default password
    $defaultPassword = $this->userService->generateDefaultPassword($user);
    $hashedPassword = $this->userService->hashPassword($defaultPassword);
    $user->setPassword($hashedPassword);

    // Save to database
    $userId = $this->userDAO->create($user);

    if ($userId) {
        return [
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $userId,
            'default_password' => $defaultPassword // Return for admin to share with user
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to create user'
        ];
    }
}
```

---

## User Service

**Test Class:** `tests/Unit/User/UserServiceTest.php`  
**Production Code:** `src/App/Services/User/UserService.php`  
**Architectural Layer:** Service

### Iteration 1 – User Validation

#### 🔴 RED Phase

```php
// tests/Unit/User/UserServiceTest.php:400-420
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
```

#### 🟢 GREEN Phase

```php
// Quick implementation
public function validate(User $user): array
{
    $errors = [];

    if (empty($user->getSchoolId())) {
        $errors[] = 'School ID is required';
    }

    if (empty($user->getFullName())) {
        $errors[] = 'Full name is required';
    }

    if (empty($user->getRole())) {
        $errors[] = 'Role is required';
    }

    return $errors;
}
```

#### 🔵 REFACTOR Phase

```php
// src/App/Services/User/UserService.php:220-250
/**
 * Validate user data
 */
public function validate(User $user): array
{
    $errors = [];

    if (empty($user->getSchoolId())) {
        $errors[] = 'School ID is required';
    }

    if (empty($user->getFullName())) {
        $errors[] = 'Full name is required';
    }

    if (empty($user->getRole())) {
        $errors[] = 'Role is required';
    } elseif (!in_array($user->getRole(), ['admin', 'faculty', 'student'])) {
        $errors[] = 'Invalid role';
    }

    if ($user->getRole() === 'student') {
        if (empty($user->getYearLevel())) {
            $errors[] = 'Year level is required for students';
        }
        if (empty($user->getSection())) {
            $errors[] = 'Section is required for students';
        }
    }

    return $errors;
}
```

### Iteration 2 – Password Generation

#### 🔴 RED Phase

```php
// tests/Unit/User/UserServiceTest.php:320-340
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
```

#### 🟢 GREEN Phase

```php
// Quick implementation
public function generateDefaultPassword(User $user): string
{
    return $user->getSchoolId() . $user->getFullName();
}
```

#### 🔵 REFACTOR Phase

```php
// src/App/Services/User/UserService.php:180-190
/**
 * Generate default password for user
 */
public function generateDefaultPassword(User $user): string
{
    if (empty($user->getSchoolId()) || empty($user->getFullName())) {
        throw new \InvalidArgumentException('School ID and full name are required to generate password');
    }
    
    return $user->getSchoolId() . $user->getFullName();
}
```

---

## Router

**Test Class:** `tests/Unit/Core/RouterTest.php`  
**Production Code:** `src/App/Core/Router.php`  
**Architectural Layer:** Core

### Iteration 1 – Route Registration

#### 🔴 RED Phase

```php
// tests/Unit/Core/RouterTest.php:25-45
/**
 * @test
 * @group router
 * @group get
 */
public function it_should_register_get_route()
{
    // Arrange (Red Phase - Test First)
    $path = '/test';
    $callback = function() { return 'test response'; };

    // Act (Green Phase - Make it pass)
    $this->router->get($path, $callback);

    // Assert (Refactor Phase - Clean up)
    $reflection = new \ReflectionClass($this->router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($this->router);

    $this->assertArrayHasKey('GET', $routes);
    $this->assertArrayHasKey($path, $routes['GET']);
    $this->assertEquals($callback, $routes['GET'][$path]);
}
```

#### 🟢 GREEN Phase

```php
// Quick implementation
class Router
{
    private $routes = [];

    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }
}
```

#### 🔵 REFACTOR Phase

```php
// src/App/Core/Router.php:1-50
<?php

namespace App\Core;

class Router
{
    private $routes = [];

    /**
     * Add a GET route
     */
    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Add a POST route
     */
    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Add a PUT route
     */
    public function put($path, $callback)
    {
        $this->routes['PUT'][$path] = $callback;
    }

    /**
     * Add a DELETE route
     */
    public function delete($path, $callback)
    {
        $this->routes['DELETE'][$path] = $callback;
    }
}
```

### Iteration 2 – Route Dispatch

#### 🔴 RED Phase

```php
// tests/Unit/Core/RouterTest.php:75-105
/**
 * @test
 * @group router
 * @group dispatch
 */
public function it_should_dispatch_get_request_to_correct_route()
{
    // Arrange (Red Phase)
    $path = '/test';
    $expectedResponse = 'test response';
    $callback = function() use ($expectedResponse) { return $expectedResponse; };

    $this->router->get($path, $callback);

    // Mock $_SERVER variables
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $path;

    // Act (Green Phase)
    ob_start();
    $this->router->dispatch();
    $output = ob_get_clean();

    // Assert (Refactor Phase)
    $this->assertEquals($expectedResponse, $output);
}
```

#### 🟢 GREEN Phase

```php
// Quick implementation
public function dispatch()
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    if (isset($this->routes[$method][$path])) {
        $callback = $this->routes[$method][$path];
        $result = $callback();
        echo $result;
    } else {
        http_response_code(404);
        echo '404 Not Found';
    }
}
```

#### 🔵 REFACTOR Phase

```php
// src/App/Core/Router.php:100-150
/**
 * Dispatch based on current globals and echo handler return
 */
public function dispatch()
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    $path = rtrim($path, '/');
    if ($path === '') {
        $path = '/';
    }

    // Handle subdirectory paths
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = dirname($scriptName);
    $parentDir = rtrim(dirname($scriptDir), '/');
    $candidates = array_unique(array_filter([
        $scriptDir,
        $parentDir && substr($scriptDir, -7) === '/public' ? $parentDir : null,
    ]));
    foreach ($candidates as $base) {
        if ($base !== '/' && $base !== '.' && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
            $path = $path === '' ? '/' : $path;
            break;
        }
    }

    if (isset($this->routes[$method][$path])) {
        $callback = $this->routes[$method][$path];
        $result = $this->invoke($callback, []);
        if ($result !== null) {
            echo $result;
        }
        return;
    }

    // Check for parameterized routes
    $matchedRoute = $this->findParameterizedRoute($method, $path);
    if ($matchedRoute) {
        $result = $this->invoke($matchedRoute['callback'], $matchedRoute['params']);
        if ($result !== null) {
            echo $result;
        }
        return;
    }

    http_response_code(404);
    echo '404 Not Found';
}
```

---

## Admin Controller

**Test Class:** `tests/Unit/Admin/AdminControllerTest.php`  
**Production Code:** `src/App/Controllers/Admin/AdminController.php`  
**Architectural Layer:** Controller

### Iteration 1 – Dashboard Display

#### 🔴 RED Phase

```php
// tests/Unit/Admin/AdminControllerTest.php:75-105
/**
 * @test
 */
public function it_should_display_admin_dashboard_with_user_data()
{
    // Mock current user data
    $currentUser = [
        'user_id' => 1,
        'school_id' => 'ADMIN-001',
        'full_name' => 'Admin User',
        'role' => 'admin'
    ];
    
    $students = [
        ['year_level' => '1st', 'section' => 'A'],
        ['year_level' => '1st', 'section' => 'B'],
        ['year_level' => '2nd', 'section' => 'A']
    ];
    
    $faculty = [
        ['full_name' => 'Faculty 1'],
        ['full_name' => 'Faculty 2']
    ];
    
    // Set up mock expectations
    $this->authServiceMock
        ->expects($this->once())
        ->method('getCurrentUser')
        ->willReturn($currentUser);
        
    $this->userServiceMock
        ->expects($this->exactly(2))
        ->method('getUsersByRole')
        ->willReturnMap([
            ['student', $students],
            ['faculty', $faculty]
        ]);
        
    $this->viewMock
        ->expects($this->once())
        ->method('display')
        ->with('admin.dashboard', $this->callback(function($data) use ($currentUser, $students, $faculty) {
            return $data['admin'] === $currentUser &&
                   $data['students'] === $students &&
                   $data['faculty'] === $faculty;
        }));
    
    // Call the method
    $this->adminController->dashboard();
}
```

#### 🟢 GREEN Phase

```php
// Quick implementation
public function dashboard()
{
    $currentUser = $this->authService->getCurrentUser();
    $students = $this->userService->getUsersByRole('student');
    $faculty = $this->userService->getUsersByRole('faculty');
    
    $data = [
        'admin' => $currentUser,
        'students' => $students,
        'faculty' => $faculty
    ];
    
    $this->view->display('admin.dashboard', $data);
}
```

#### 🔵 REFACTOR Phase

```php
// src/App/Controllers/Admin/AdminController.php:25-45
/**
 * Show admin dashboard
 */
public function dashboard()
{
    $currentUser = $this->authService->getCurrentUser();
    
    // Get real data from database
    $students = $this->userService->getUsersByRole('student');
    $faculty = $this->userService->getUsersByRole('faculty');
    
    // Convert User objects to arrays for view compatibility
    $studentsArray = $this->userService->usersToArray($students);
    $facultyArray = $this->userService->usersToArray($faculty);
    
    $data = [
        'admin' => $currentUser, // Already an array from AuthService
        'students' => $studentsArray,
        'faculty' => $facultyArray,
        'yearSections' => $this->getYearSections($studentsArray)
    ];
    
    $this->view->display('admin.dashboard', $data);
}
```

### Iteration 2 – Add User

#### 🔴 RED Phase

```php
// tests/Unit/Admin/AdminControllerTest.php:180-210
/**
 * @test
 */
public function it_should_add_user_successfully()
{
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'full_name' => 'New User',
        'school_id' => '2024-001',
        'role' => 'student'
    ];
    
    $expectedResult = [
        'success' => true,
        'message' => 'User created successfully'
    ];
    
    $this->userServiceMock
        ->expects($this->once())
        ->method('createUser')
        ->with($_POST)
        ->willReturn($expectedResult);
    
    $this->adminController->addUser();
    
    $output = ob_get_contents();
    $response = json_decode($output, true);
    
    $this->assertEquals('success', $response['status']);
    $this->assertEquals('User created successfully', $response['message']);
}
```

#### 🟢 GREEN Phase

```php
// Quick implementation
public function addUser()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->showError('Invalid request method.');
        return;
    }

    $result = $this->userService->createUser($_POST);
    
    if ($result['success']) {
        $this->showSuccess($result['message']);
    } else {
        $this->showError($result['message']);
    }
}

private function showSuccess($message)
{
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => $message]);
}

private function showError($message)
{
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $message]);
}
```

#### 🔵 REFACTOR Phase

```php
// src/App/Controllers/Admin/AdminController.php:150-170
/**
 * Handle add user request
 */
public function addUser()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->showError('Invalid request method.');
        return;
    }

    $result = $this->userService->createUser($_POST);
    
    if ($result['success']) {
        $this->showSuccess($result['message']);
    } else {
        $this->showError($result['message']);
    }
}

/**
 * Show success message
 */
private function showSuccess($message)
{
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => $message
    ]);
}

/**
 * Show error message
 */
private function showError($message)
{
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
}
```

---

## Summary

This TDD evolution demonstrates the systematic approach to building a robust PHP application:

1. **Models** - Started with basic entity creation and evolved to include business logic like password verification
2. **DAOs** - Focused on data access patterns with proper error handling and User model integration
3. **Services** - Implemented business logic with validation, authentication, and user management
4. **Core Components** - Built routing system with parameterized routes and subdirectory support
5. **Controllers** - Created admin interface with proper request handling and response formatting

Each iteration followed the RED-GREEN-REFACTOR cycle:
- **RED**: Write failing tests that define the desired behavior
- **GREEN**: Write minimal code to make tests pass
- **REFACTOR**: Clean up and improve the implementation while keeping tests green

The final production code demonstrates clean architecture principles with proper separation of concerns, dependency injection, and comprehensive error handling.