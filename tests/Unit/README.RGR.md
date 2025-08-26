# Unit Tests TDD: Red-Green-Refactor (RGR)

This document maps your existing unit tests in `tests/Unit/` to a TDD (Red-Green-Refactor) narrative. It explains what each test file validates (RED), the minimal implementation that makes them pass (GREEN), and the hardening/refactors applied or recommended (REFACTOR).

Run all unit tests:
```bash
./vendor/bin/phpunit -c phpunit.xml --testsuite Unit
```

Contents covered:
- Auth/AuthServiceTest.php
- Admin/AdminControllerTest.php
- User/UserServiceTest.php
- DAO/UserDAOTest.php
- Models/UserTest.php
- Core/RouterTest.php

---

## Auth/AuthServiceTest.php

### 🔴 RED – What the tests enforce
- Successful login with valid `school_id` + `password` returns success and sets session fields.
- Failure on invalid password or non-existent user with clear messages.
- Empty credentials are rejected.
- `requireAuth()` redirects unauthenticated users to `/login`.
- `requireRole()` redirects when the session role doesn’t match.

### 🟢 GREEN – Minimal code that satisfies
- `AuthService::login($school_id, $password)`:
  - Trim inputs, fetch `User` via `UserDAO::authenticate()` (returns `User` model or null).
  - Verify password with `User::verifyPassword()` (supports hashed and plain fallback).
  - Start session and set `$_SESSION['user_id','school_id','full_name','role','year_level','section']`.
  - Return a success payload with `user` array.
- `AuthService::requireAuth()`/`requireRole($role)`:
  - Use session to check authentication/authorization; header redirect to base-path `/login` and exit when failing.

Example GREEN snippets (already implemented):
```php
// AuthService::isAuthenticated
return isset($_SESSION['user_id']) && isset($_SESSION['role']);
```
```php
// AuthService::requireAuth (base-path aware)
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if (!$this->isAuthenticated()) { header('Location: ' . $basePath . '/login'); exit; }
```

### 🔵 REFACTOR – Hardening
- Extract session write to a helper for easier test isolation.
- Log failed authentication attempts.
- Consolidate role constants and policies.

---

## Admin/AdminControllerTest.php

### 🔴 RED – What the tests enforce
- Construction requires authenticated `admin` (calls `requireAuth()` + `requireRole('admin')`).
- `dashboard()` composes data using `UserService` and renders the admin view.
- Actions (add/edit/delete for student/faculty) set success/error messages and redirect back to dashboard.
- `logout` flow shows confirmation and performs redirect on `?confirm=true`.

### 🟢 GREEN – Minimal code that satisfies
- `__construct()`: instantiate `AuthService`, `UserService`, `View`; enforce auth and role.
- `dashboard()`: fetch users by role via `UserService`, convert to arrays, render `admin.dashboard`.
- POST handlers: call `UserService` methods; set `$_SESSION['success_message']|error_message`; redirect to dashboard.
- `logout()`: if `confirm=true`, call `AuthService::logout()` then redirect to `/login`; otherwise render confirmation page.

Example GREEN snippets:
```php
// AdminController::__construct
$this->authService->requireAuth();
$this->authService->requireRole('admin');
```
```php
// Redirect to dashboard helper (base-path aware)
$basePath = dirname($_SERVER['SCRIPT_NAME']);
header('Location: ' . $basePath . '/admin/dashboard');
```

### 🔵 REFACTOR – Hardening
- Extract view-model builders for dashboard.
- Centralize redirect helper.
- Add CSRF tokens for admin POST actions.

---

## User/UserServiceTest.php

### 🔴 RED – What the tests enforce
- `createUser` fails on duplicate `school_id`; succeeds otherwise and returns new `user_id`.
- `updateUser` requires existing user; checks `school_id` uniqueness on change; returns success flag.
- `deleteUser` requires existing user; returns success flag.
- Query helpers: `getAllUsers`, `getUsersByRole`, `getStudentsByYearSection`, `getUserBy(School)Id` return `User` models.

### 🟢 GREEN – Minimal code that satisfies
- Use `UserDAOInterface` that returns `User` models.
- `createUser()`:
  - Create `User` model from data; check `schoolIdExists`; call `create()`; return outcome with `user_id`.
- `updateUser()`:
  - Load `findById`; merge into `User` model; if `school_id` changed, check uniqueness; call `update()`.
- `deleteUser()`:
  - Load `findById`; call `delete()`; return outcome.

Example GREEN snippet:
```php
if (isset($data['school_id']) && $data['school_id'] !== $existing->getSchoolId()) {
    if ($this->userDAO->schoolIdExists($data['school_id'], $userId)) {
        return ['success' => false, 'message' => 'School ID already exists.'];
    }
}
```

### 🔵 REFACTOR – Hardening
- Delegate validation to `User::validate()`; generate and hash default passwords for students.
- Keep service returns simple structs for testability; adapt to arrays at the view layer.

---

## DAO/UserDAOTest.php

### 🔴 RED – What the tests enforce
- DAO uses the `Database` singleton and `PDO` prepared statements.
- Retrieval methods return `User` model instances (not arrays): `findById`, `findBySchoolId`, `getAllUsers`, `getUsersByRole`, `getStudentsByYearSection`.
- `create(User)` returns new ID; `update($id, User)`/`delete($id)` return bool.
- `schoolIdExists($schoolId, $excludeId)` behaves correctly.

### 🟢 GREEN – Minimal code that satisfies
- Implement data access with prepared statements and map rows to `User` models:
```php
$stmt = $pdo->prepare('SELECT * FROM users WHERE school_id = ?');
$stmt->execute([$schoolId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
return $row ? new User($row) : null;
```
- `create(User $u)`: insert and return `lastInsertId()` cast to int.

### 🔵 REFACTOR – Hardening
- Handle `PDOException` with safe fallbacks; avoid leaking DB errors.
- Add pagination to list queries if needed later.

---

## Models/UserTest.php

### 🔴 RED – What the tests enforce
- `User` supports hydration, serialization (`hydrate()`, `toArray()`), getters/setters.
- Business methods such as `verifyPassword()`, `hashPassword()`, `generateDefaultPassword()` behave consistently.
- `validate()` returns structured errors for invalid inputs.

### 🟢 GREEN – Minimal code that satisfies
- Implement the `User` model with business logic centralization:
```php
public function toArray(): array { /* map all properties */ }
public function verifyPassword(string $plain): bool { /* password_verify or fallback */ }
public function hashPassword(string $plain): string { return password_hash($plain, PASSWORD_BCRYPT); }
```

### 🔵 REFACTOR – Hardening
- Enforce invariants in setters; normalize names; keep validation rules cohesive and test-backed.

---

## Core/RouterTest.php

### 🔴 RED – What the tests enforce
- Route registration for `GET/POST/PUT/DELETE` works.
- `dispatch()`/`handleRequest()` resolve routes correctly, including parameterized paths and query strings.
- Router normalizes paths (trailing slash removal, subdirectory stripping, including `public/` parent stripping) to match registered routes.
- 404 behavior when no route matches.

### 🟢 GREEN – Minimal code that satisfies
- Maintain a `$routes` map keyed by method and path.
- Normalize `$path` from `REQUEST_URI`, strip base path using `dirname($_SERVER['SCRIPT_NAME'])` and its parent if script dir ends with `/public`.
- Match exact and parameterized routes; invoke callbacks.

Example GREEN snippet:
```php
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$parentDir = rtrim(dirname($scriptDir), '/');
$candidates = array_unique(array_filter([$scriptDir, substr($scriptDir, -7) === '/public' ? $parentDir : null]));
foreach ($candidates as $base) {
    if ($base && $base !== '/' && strpos($path, $base) === 0) { $path = substr($path, strlen($base)) ?: '/'; break; }
}
```

### 🔵 REFACTOR – Hardening
- Extract a pure `PathHelper::normalize()` and reuse in `Router` to keep path logic unit-testable.
- Add logging around routing decisions (guarded by env flag) to assist debugging.

---

## Notes
- All GREEN snippets above align with your existing implementation; use them as reference for mapping tests to code.
- REFACTOR items are recommendations. Implement incrementally with new unit tests to keep changes safe.