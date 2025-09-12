TDD Development Diary - PHP Web Application
Development Sessions
Session 1: Project Foundation
Driver: Developer A
Navigator: Developer B
Setup Phase:

Created basic repository structure with src/, tests/, public/, Config/ directories
Added composer.json with PSR-4 autoload mapping "App\\": "src/App/"
Configured phpunit.xml with bootstrap pointing to tests/bootstrap.php
Built bootstrap.php to normalize test environment:

Required Composer autoloader
Set $_ENV['APP_ENV'] = 'testing'
Started output buffering
Cleared superglobals and set $_SERVER defaults


Created public/index.php front controller
Added Config/App.php and Config/Database.php for application configuration

First Test (Red):

Wrote RouterTest.php testing App\Core\Router class
Tested basic contract: Router::get($path, $callback) and Router::dispatch()
Expected route registration to create internal routes structure with HTTP method keys
Expected dispatch to execute callback and return/echo result

Navigator B suggested focusing on minimal viable routing first.
Roles switch
Session 2: Basic Router Implementation
Driver: Developer B
Navigator: Developer A
Implementation (Green):

Created Router.php with private $routes = []
Implemented HTTP method registration:

get($path, $callback) → $this->routes['GET'][$path] = $callback
post($path, $callback) → $this->routes['POST'][$path] = $callback
put($path, $callback) → $this->routes['PUT'][$path] = $callback
delete($path, $callback) → $this->routes['DELETE'][$path] = $callback
any($path, $callback) convenience method for all methods


Made $routes property accessible for test introspection

Navigator A suggested adding dispatch functionality next.
Roles switch
Session 3: Request Dispatch Logic
Driver: Developer A
Navigator: Developer B
Extended Tests (Red):

Added dispatch behavior tests in RouterTest.php
Set $_SERVER['REQUEST_METHOD'] and $_SERVER['REQUEST_URI'] in tests
Expected dispatch() to echo callback results for GET/POST requests
Expected 404 output for non-existent routes
Documented project structure in test comments for team reference

Navigator B suggested implementing path parsing and response handling.
Implementation (Green):

Added dispatch() method to read request method and URI
Implemented path normalization:

parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
rtrim($path, '/') with empty string conversion to '/'


Created invoke($callback, $params) to execute callbacks and return results
Added notFound() method:

Set http_response_code(404)
Set Content-Type: application/json header
Echo json_encode(['status'=>'error','message'=>'Route not found.'])



Roles switch
Session 4: Advanced Routing Features
Driver: Developer B
Navigator: Developer A
Advanced Tests (Red):

Added parameterized route tests: /users/{id}, /users/{id}/posts/{postId}
Expected captured segments passed to callbacks as parameters
Added subdirectory handling test:

Set $_SERVER['REQUEST_URI'] = '/subdirectory/login'
Set $_SERVER['SCRIPT_NAME'] = '/subdirectory/index.php'
Expected router to strip subdirectory and match /login


Included notes about Views and Controllers folder structure

Navigator A suggested regex-based pattern matching for flexibility.
Implementation (Green):

Created convertRouteToPattern($route):

Used preg_replace('/\{([^}]+)\}/', '([^/]+)', $route)
Returned '#^' . $pattern . '$#' for regex matching


Implemented findParameterizedRoute($method, $path):

Iterated through $this->routes[$method]
Applied preg_match($pattern, $path, $matches)
Returned callback and parameters (after array_shift($matches))


Added subdirectory detection logic:

Used $_SERVER['SCRIPT_NAME'] and dirname($scriptName)
Checked for 'public' directory in path
Stripped detected base path using strpos and substr



Roles switch
Session 5: HTTP Methods and Path Normalization
Driver: Developer A
Navigator: Developer B
Method Tests (Red):

Added comprehensive HTTP method tests (GET/POST/PUT/DELETE)
Tested root path handling for '/' routes
Verified proper $_SERVER['REQUEST_METHOD'] handling

Navigator B suggested consolidating path normalization logic.
Implementation (Green):

Completed put() and delete() method implementations
Enhanced any() helper method
Standardized root path handling: set $path = '/' when rtrim produces empty string
Aligned dispatch() and handleRequest() to use consistent path normalization

Roles switch
Session 6: Router Refactoring
Driver: Developer B
Navigator: Developer A
Refactoring (Blue):

Extracted executeCallback() method for better separation of concerns
Added support for Controller@method string format:

list($controller, $method) = explode('@', $callback)
new $controller() instantiation
call_user_func_array([$instance, $method], $params) invocation


Kept invoke() for simple callback returns
Added getRoutes() public accessor for test inspection
Added debug logging for processed paths and available routes
Established clear project structure: Core (Router.php, View.php), Controllers, Views

Navigator A suggested moving to model layer development.
Roles switch
Session 7: User Model Development
Driver: Developer A
Navigator: Developer B
Model Tests (Red):

Created UserTest.php testing App\Models\User contract
Expected constructor to accept array data and hydrate(array) method
Required toArray() method for serialization
Tested getters: getUserId, getSchoolId, getFullName, getRole, getYearLevel, getSection, getPassword
Expected fluent setters: setUserId, setSchoolId, setFullName, setRole, setYearLevel, setSection, setPassword
Critical requirement: verifyPassword(string) supporting both hashed and plain-text passwords
Expected integration with App.php configuration constants
Required compatibility with future UserService validation

Navigator B suggested focusing on authentication logic as core feature.
Implementation (Green):

Created User.php with all private properties
Implemented hydrate() method for data population
Added all required getters and fluent setters
Implemented critical verifyPassword(string $inputPassword):

Return false for empty stored password
Used strpos($this->password, '$') === 0 to detect hashed passwords
Applied password_verify() for hashed passwords
Used plain string comparison for legacy passwords


Added toArray() method for data export
Referenced App.php constants like BCRYPT_COST

Roles switch
Session 8: Data Access Layer
Driver: Developer B
Navigator: Developer A
DAO Tests (Red):

Created UserDAOTest.php for App\DAO\Auth\UserDAO
Mocked PDO and PDOStatement for isolated testing
Expected exact SQL string matching in PDO::prepare() calls
Required PDO::FETCH_ASSOC for all fetch operations
Tested specific SQL patterns:

"SELECT * FROM users WHERE school_id = ?" for findBySchoolId
"INSERT INTO users (school_id, full_name, password, role, year_level, section, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())" for create
Expected lastInsertId() usage for created records



Navigator A suggested matching exact SQL strings and parameter ordering.
Implementation (Green):

Created UserDAO.php in App\DAO\Auth namespace
Initialized with private $db from App\Config\Database::getInstance()->getConnection()
Implemented methods using $this->db->prepare($sql) and $stmt->execute($params)
Used fetch(PDO::FETCH_ASSOC) and fetchAll(PDO::FETCH_ASSOC) consistently
Returned new User($data) or arrays mapped to User objects
Made create() return (int)$this->db->lastInsertId() on success
Wrapped all database operations with try/catch (PDOException $e)
Returned safe defaults on database errors

Roles switch
Session 9: DAO Interface Introduction
Driver: Developer A
Navigator: Developer B
Interface Refactoring (Blue):

Created UserDAOInterface.php for better testability
Made UserDAO implement UserDAOInterface
Planned ahead for UserServiceInterface.php

Navigator B suggested this would simplify service layer testing.
Benefits:

Enabled FakeUserDAO usage in unit tests
Prepared for dependency injection in services
Improved test isolation

Roles switch
Session 10: Business Logic Layer
Driver: Developer B
Navigator: Developer A
Service Tests (Red):

Created UserServiceTest.php with FakeUserDAO test double
Tested comprehensive UserService behavior:

createUser, updateUser, deleteUser
getAllUsers, getUsersByRole, getStudentsByYearSection
usersToArray for data export
generateDefaultPassword(User) - expected concatenation of school ID and full name
hashPassword(string) - expected password_hash usage
validate(User) - expected role and student-specific field validation
isAdmin, isFaculty, isStudent role checking methods



Navigator A suggested centralizing password and validation logic here.
Implementation (Green):

Created UserService.php accepting UserDAOInterface $userDAO = null
Defaulted to new UserDAO() when no DAO provided
Implemented createUser($data):

Instantiated new User($data)
Called validate($user) for business rules
Checked uniqueness via $this->userDAO->schoolIdExists($user->getSchoolId())
Generated password via generateDefaultPassword(User $user)
Hashed password via hashPassword(string)
Persisted with $this->userDAO->create($user)


Implemented generateDefaultPassword(User $user) returning $user->getSchoolId() . $user->getFullName()
Implemented hashPassword(string) using password_hash($plainPassword, PASSWORD_DEFAULT)
Created validate(User $user) returning human-readable validation error arrays
Added role checking methods for authorization logic

Roles switch
Session 11: Authentication Service
Driver: Developer A
Navigator: Developer B
Auth Tests (Red):

Created AuthServiceTest.php for App\Services\Auth\AuthService
Tested complete authentication contract:

login($schoolId, $password) with session management
isAuthenticated(), getCurrentUser(), getCurrentUserModel()
requireAuth(), requireRole($role) for access control
logout() with session cleanup
changePassword($userId, $current, $new)
createUser method delegation


Expected login() to start PHP session and populate $_SESSION variables
Required mocked UserDAO injection for isolated testing
Expected authorization guards to perform redirects

Navigator B suggested separating authentication from user management.
Implementation (Green):

Created AuthService.php with constructor dependency injection:

__construct(UserDAO $userDAO = null, UserService $userService = null)
Defaulted to new UserDAO() and new UserService()


Implemented login():

Trimmed input parameters
Returned ['success'=>false,'message'=>'School ID and password are required.'] for empty inputs
Called $this->userDAO->authenticate($school_id, $password)
Verified password with $user->verifyPassword($password)
On success: session_start() and populated $_SESSION with user data


Implemented authorization guards:

requireAuth() and requireRole() computed dirname($_SERVER['SCRIPT_NAME'])
Performed header('Location: ' . $basePath . '/login'); exit; for unauthorized access


Implemented logout():

Cleared session variables
Used session_get_cookie_params() and setcookie() for cookie cleanup


Implemented changePassword():

Used $this->userDAO->findById() and $user->verifyPassword()
Called $user->hashPassword($newPassword) (noted inconsistency)
Updated via $this->userDAO->update()


Delegated createUser() to UserService::validate(), generateDefaultPassword(), and hashPassword()

Roles switch
Session 12: Password Logic Centralization
Driver: Developer B
Navigator: Developer A
Service Refactoring (Blue):

Moved password generation and hashing logic to UserService
Updated AuthService::createUser() to use $this->userService->hashPassword()
Identified inconsistency in AuthService::changePassword() calling $user->hashPassword($newPassword)

Navigator A suggested documenting the inconsistency for future resolution.
Documentation:

Added TODO comment in AuthService::changePassword()
Options: use $this->userService->hashPassword() or add hashPassword() to User model
Added note to repository documentation

Roles switch
Session 13: View Layer Implementation
Driver: Developer A
Navigator: Developer B
Controller Tests (Red):

Created AdminControllerTest.php with View mocks
Expected controllers to inject and use View objects with with(), render(), and display() methods
Mocked AuthService and UserService for isolated controller testing
Referenced Views templates using dot notation (e.g., auth.login, admin.dashboard)
Expected controllers to set session messages ($_SESSION['success_message']) for template rendering
Required htmlspecialchars() usage in templates for XSS prevention

Navigator B suggested file-based template rendering approach.
Implementation (Green):

Created View.php with constructor accepting $viewsPath (default: __DIR__ . '/../Views/')
Implemented data management with private $data = []
Created with($key, $value=null) method for fluent data assignment
Implemented render($view, $data=[]):

Merged data arrays
Used extract($data) to create template variables
Used ob_start() and include for template rendering
Converted dot notation to file paths: str_replace('.', '/', $view) . '.php'
Added file_exists() check with \Exception("View file not found: {$viewFile}")


Added renderWithLayout() for layout support including layouts/{$layout}.php
Created static utilities: make(), json(), redirect()
Made display() echo rendered content
Added template files under Views directory following naming conventions
Ensured all templates used htmlspecialchars() for XSS protection

Roles switch
Session 14: Service Interface Formalization
Driver: Developer B
Navigator: Developer A
Interface Refactoring (Blue):

Created src/App/Interfaces/UserServiceInterface.php
Updated AuthService and UserService constructors to accept interface types
Enabled easier mocking in tests

Navigator A suggested this would improve dependency injection and testing.
Benefits:

Better test isolation through interface mocking
Clearer service contracts
Improved maintainability

Roles switch
Session 15: Controller Implementation
Driver: Developer A
Navigator: Developer B
Integration Tests (Red):

Created AuthControllerTest.php and AdminControllerTest.php
Tested request-level behavior with $_SERVER['REQUEST_URI'] and $_SERVER['SCRIPT_NAME']
Expected controller endpoints to render views or perform header redirects
Enhanced phpunit.xml bootstrap to ensure clean test environment
Updated bootstrap.php:

Set APP_ENV=testing
Disabled cookie-based sessions: ini_set('session.use_cookies', '0')
Reset $_SESSION between tests



Navigator B suggested proper authorization guards and response handling.
Implementation (Green):

Implemented controllers in Controllers directory:

AuthController for authentication endpoints
AdminController for administrative functions
FacultyController for faculty-specific features


Used constructor injection for AuthService, UserService, and View
Implemented authorization guards:

AdminController used requireAuth() and requireRole('admin')
Applied guards at controller level for entire controller protection


Implemented request handling:

Used $_POST data and path parameters (e.g., editUser($userId))
Produced JSON responses via showSuccess/showError methods
Rendered HTML via View::display()


Added client-side HTML form validation with required attributes
Maintained server-side validation through UserService::validate()

Roles switch
Session 16: Project Stabilization
Driver: Developer B
Navigator: Developer A
Bug Fixes and Improvements:

Enhanced phpunit.xml with <env name="APP_ENV" value="testing"/>
Improved bootstrap.php session handling for tests
Fixed type casting: (int)$this->db->lastInsertId() in UserDAO::create()
Ensured UserService::hashPassword() used PASSWORD_DEFAULT
Documented project structure conventions:

Config/ - Environment and database configuration
Core/ - Router and View utilities
Controllers/ - HTTP request handlers
DAO/ - Data access objects
Services/ - Business logic layer
Models/ - Entity classes
Views/ - Template files
public/ - Web root directory



Navigator A suggested documenting known issues and TODOs.
Roles switch
Session 17: Error Handling Improvements
Driver: Developer A
Navigator: Developer B
404 Response Tests (Red):

Integration tests expected API 404s to include "404" or "Not Found" in response
Previous Router::notFound() returned only JSON
Tests demanded both HTTP status code and message content

Navigator B suggested maintaining both JSON and text compatibility.
Implementation (Green):

Updated Router::notFound():

Set http_response_code(404)
Emit JSON body: ['status'=>'error','message'=>'Route not found.']


Modified dispatch() fallback:

Print '404 Not Found' when invoke() returns null and response code is 404
Preserved JSON error body for API clients
Satisfied tests checking for 404 string in output
