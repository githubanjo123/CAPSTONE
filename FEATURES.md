# Current Working Features

This document summarizes the features that are implemented and routable in the current project, with their routes, access controls, and brief usage notes.

## Authentication
- Login page (HTML)
  - GET `/login`
  - Renders the login form. If already authenticated with a valid role, redirects to the role dashboard.

- Login (API)
  - POST `/api/auth/login`
  - Body: `school_id`, `password`
  - On success: session is initialized and user is redirected (by controller) to the role dashboard.
  - On failure: login page is re-rendered with error.

- Logout (API)
  - POST `/api/auth/logout`
  - Returns JSON success message and clears user session.

- Root redirect
  - GET `/`
  - Redirects to `/login`.

## Role-Based Dashboards
- Admin dashboard (HTML)
  - GET `/admin/dashboard`
  - Access: authenticated users with role `admin` (enforced by controller constructor via `requireAuth()` + `requireRole('admin')`).
  - Displays admin panel with student and faculty listings (via `UserService`).

- Faculty dashboard (HTML)
  - GET `/faculty/dashboard`
  - Access: authenticated users with role `faculty` (enforced by controller constructor).

- Student success placeholder (HTML)
  - GET `/student-success`
  - Basic success page shown after student login (placeholder until a full student dashboard exists).

## Logout (Confirmation Flows)
- Admin logout confirmation + action
  - GET `/admin/logout` (renders confirmation page)
  - GET `/admin/logout?confirm=true` (performs logout then redirects to `/login`)

- Faculty logout confirmation + action
  - GET `/faculty/logout` (renders confirmation page)
  - GET `/faculty/logout?confirm=true` (performs logout then redirects to `/login`)

## Admin User Management (CRUD)
All routes require role `admin` (enforced by `AdminController` constructor).

- Add generic user
  - POST `/admin/users/add`
  - Creates a user via `UserService::createUser()`.

- Add student
  - POST `/admin/users/add-student`
  - Creates a student user (role `student`).

- Edit student
  - POST `/admin/users/edit-student`
  - Updates an existing student.

- Delete student
  - POST `/admin/users/delete-student`
  - Deletes a student.

- Add faculty
  - POST `/admin/users/add-faculty`
  - Creates a faculty user (role `faculty`).

- Edit faculty
  - POST `/admin/users/edit-faculty`
  - Updates an existing faculty member.

- Delete faculty
  - POST `/admin/users/delete-faculty`
  - Deletes a faculty member.

- Edit user by id (generic)
  - POST `/admin/users/edit/{id}`

- Delete user by id (generic)
  - POST `/admin/users/delete/{id}`

Notes:
- Admin actions set success/error messages (via session) and redirect back to the dashboard.
- `UserService` and `UserDAO` handle business rules, validation, and persistence.

## Services and Core Behaviors
- Session-based authentication
  - `AuthService::login()` sets `$_SESSION` keys: `user_id`, `school_id`, `full_name`, `role`, optionally `year_level`, `section`.
  - `AuthService::logout()` clears session and cookie.
  - `AuthService::requireAuth()` and `requireRole($role)` redirect unauthenticated/unauthorized users to `/login`.

- User business logic
  - `User` model encapsulates validation, password hashing/verification, and conversion to array.
  - `UserService` provides higher-level methods: create, update, delete, list by role, list students by year/section, etc.

## Routing and Base Path Handling
- `public/index.php` registers all routes and uses a custom `Router` that normalizes paths and supports subdirectory deployments.
- Protected controllers (`AdminController`, `FacultyController`) are instantiated lazily inside route handlers to avoid constructor-time redirects on `/login`.

## Database
- Backed by MySQL `users` table.
- `UserDAO` uses prepared statements and returns `User` model instances.

## Summary
The application currently supports:
- Authentication (login/logout)
- Role-based access control
- Admin dashboard and CRUD for users (students/faculty)
- Faculty dashboard
- Student success placeholder page
- Robust routing with subdirectory support and session-backed authorization.