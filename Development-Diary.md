## Development Diary

This document narrates the creation and evolution of the project as observed from the outside, following two pair programmers, Driver 1 and Driver 2, as they alternated turns, made decisions, and shaped the system. The account focuses on what was created or modified, why those choices were made at each moment, and how the work evolved over time.

### Day 1 — Establishing the foundation, wiring tests, and introducing the domain

Driver 1 began by grounding the project with a vendor-backed testing ecosystem. A substantial collection of testing and utility libraries appeared under `vendor/`, including PHPUnit, code coverage tooling, PSR interfaces, and related Symfony and Sebastian components. This was a deliberate move to secure a stable feedback loop for future development, ensuring that unit and integration tests could be run consistently from the outset.

With the scaffolding in place, attention shifted to the emerging domain. The first notable domain-focused step centered on user management. New and refactored elements appeared across `src/App/DAO/Auth/UserDAO.php`, `src/App/Interfaces/UserDAOInterface.php`, `src/App/Models/User.php`, and `src/App/Services/Auth/AuthService.php`. Driver 1 formalized a dedicated `User` model and pushed responsibilities into clearer layers, signalling an intention to separate concerns between data access, domain representation, and authentication services. The motivation here was to enable stronger validations and cleaner dependencies before the system grew more complex.

Picking up the thread, Driver 2 carried the refinement further by tightening validations and aligning services and tests. Updates landed in `src/App/Controllers/Admin/AdminController.php`, `src/App/Services/User/UserService.php`, and several test files: `tests/Unit/Auth/AuthServiceTest.php`, `tests/Unit/DAO/UserDAOTest.php`, `tests/Unit/Models/UserTest.php`, and `tests/Unit/User/UserServiceTest.php`. The test modifications reflected a red-green-refactor cadence: as service boundaries clarified, the tests were adjusted to encode behavior, guard against regressions, and document expectations. The driver pair prioritized test coverage to support rapid iteration.

Driver 1 then made a small but consequential compatibility adjustment in `src/App/Controllers/Admin/AdminController.php`, converting user objects to arrays for the admin view. This bridged a presentational mismatch uncovered by early UI integration and kept the interface stable while the domain evolved behind it.

To prevent complexity from creeping into tight corners, the pair refactored authentication flows and dependency injection across `src/App/Controllers/Admin/AdminController.php`, `src/App/Controllers/Faculty/FacultyController.php`, and `src/App/Services/Auth/AuthService.php`, with related test updates in `tests/Unit/Admin/AdminControllerTest.php` and `tests/Unit/Auth/AuthServiceTest.php`. The aim was to achieve clearer instantiation and lifecycles for collaborators, simplifying reasoning about session and role transitions. Driver 2 followed up by strengthening test doubles around `usersToArray` in `tests/Unit/Admin/AdminControllerTest.php`, ensuring the controller’s contract with its view layer remained explicit and verifiable.

The routing surface received early hardening. Driver 1 introduced lazy instantiation for controllers in `public/index.php` to defer cost and reduce boot-time coupling, then tightened authentication checks with role validation and loop-safe redirects across `public/index.php` and `src/App/Controllers/Auth/AuthController.php`. To support diverse hosting setups, they also improved subdirectory path handling in `src/App/Core/Router.php`. These changes were pragmatic responses to operational concerns discovered during local runs and early integration tests.

### Day 1 — Documenting intentions and codifying a TDD rhythm

Once the core feedback loop stabilized, the pair turned to documentation as a companion to tests. They added TDD narratives and feature-level readmes to capture their approach and keep future contributors aligned. New files appeared: `README.md`, `README.unit-tests.md`, `FEATURES.md`, and `README.unit-features.md`. These artifacts clarified scope, development methodology, and the planned capabilities of the system. The pair repeatedly refined these documents, reflecting the living nature of both the code and its story.

To reinforce their testing cadence, they added `tests/Unit/README.RGR.md`, making the red-green-refactor loop explicit. This functioned as both a learning aid and a north star, aligning daily decisions with their quality strategy. Subsequent commits iterated on the readmes to mirror recent changes to authentication, user flows, and routing validations.

With guardrails in place, Driver 1 expanded the authentication feature set, consolidating user login, session handling, and role checks. The documentation (`README.md`, `README.unit-features.md`, `README.unit-tests.md`) was immediately updated to match the implemented behavior and to prepare for broader test coverage. A clean-up pass standardized naming conventions across tests, renaming classes from “*RgrTest” to the simpler “*Test.” This improved discoverability and brought naming in line with common testing conventions, a small but meaningful reduction in friction for future readers.

### Day 1 — Broadening test coverage and focusing on maintainability

The pair then invested in fuller documentation of their testing strategy, adding `README_UnitTests.md`. Its subsequent refinements charted the growing scope of test coverage, including authentication state and user management. This documentation-first loop acted as scaffolding for refactors that moved business logic out of the model into services: `src/App/Models/User.php`, `src/App/Services/Auth/AuthService.php`, and `src/App/Services/User/UserService.php` changed in tandem with updates across `tests/Unit/Models/UserTest.php` and `tests/Unit/User/UserServiceTest.php`. The drivers moved logic to the service layer to clarify model responsibilities and make orchestration easier to test.

As password handling and validation needs came into sharper focus, the pair tuned both implementation and docs again, touching `README.md`, `README.unit-features.md`, `README.unit-tests.md`, and `README_UnitTests.md`. The result was a codebase that read more like a well-edited text: responsibilities were clearer, boundaries were more evident, and the supporting writing made intentions unmistakable.

### Day 2 — Pruning and consolidating narratives

Returning with fresh eyes, the pair opted to trim documentation that had become redundant or overlapping. They removed `FEATURES.md` and `README.unit-features.md`, then renamed `README.unit-tests.md` to `notsure.md` as a staging step for further cleanup before removing it entirely. In the same spirit of consolidation, they also removed `README.md` and `README_UnitTests.md`, leaving a leaner set of references to avoid confusion and duplication. This pruning signaled a preference for fewer, better-maintained sources of truth.

### Day 3 — Capturing the test brief

To ensure that the project’s intent remained legible despite the clean-up, the pair added `Technical-Test-Documentation.md`. This document captured the essence of the exercise and preserved the context needed to navigate the codebase and its tests without the earlier proliferation of readmes. The addition represented a balance between minimalism and clarity: enough framing to understand the goals, without the maintenance burden of multiple overlapping documents.

### Architectural throughline

Across these days, `public/index.php` evolved from a simple entry point to a more thoughtful composition layer, taking on lazy controller instantiation and stricter access checks. The routing core (`src/App/Core/Router.php`) became more resilient to deployment topologies. The application structure under `src/App/` settled into a layered shape: `Config`, `Controllers`, `Core`, `DAO`, `Interfaces`, `Models`, `Services`, and `Views`, each refined incrementally as responsibilities moved to their most natural homes. Tests matured in parallel under `tests/`, with unit and integration suites reflecting the steady normalization of boundaries and behaviors.

### Closing note

By the end of this arc, the project stood on a tidy foundation: a tested authentication and user management core, a pragmatic routing surface, and a focused set of documentation that tells just enough of the story to onboard the next contributor. Each change was made to sharpen intent, reduce accidental complexity, and keep feedback loops fast — the quiet discipline behind a system that is designed to evolve.

