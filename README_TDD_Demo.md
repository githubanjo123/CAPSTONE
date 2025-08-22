# 🔄 TDD Demonstration: Red-Green-Refactor Cycle

This repository demonstrates **Test-Driven Development (TDD)** using a real-world Educational Management System built with PHP and PHPUnit.

## 📋 Quick Start

### Step 1: See the RED Phase (Failing Tests)
```bash
php tdd_step_by_step.php
```

This will show you **all tests failing** - this is the **RED phase** of TDD where we write tests first before any implementation.

### Step 2: Follow the GREEN Phase (Make Tests Pass)
```bash
php fix_tests_step_by_step.php
```

This interactive script lets you implement each phase step-by-step to make the tests pass.

### Step 3: Study the Complete Documentation
```bash
cat TDD_Documentation.md
```

Read the comprehensive TDD guide that shows the complete Red-Green-Refactor cycle.

## 🎯 What You'll Learn

### ❌ RED Phase Experience
- See **actual failing tests** with real error messages
- Understand how tests **drive the design**
- Experience writing **behavior-first** code

### ✅ GREEN Phase Implementation
- Write **minimal code** to make tests pass
- See how **architecture emerges** from testing needs
- Learn to **resist over-engineering** early

### 🔄 REFACTOR Phase Benefits
- Improve code **while tests provide safety**
- Extract **interfaces and patterns** naturally
- Experience **confident refactoring**

## 📊 Current Test Status

When you start, **ALL TESTS FAIL** (this is intentional):

```
❌ AuthServiceTest: 2 errors (Class not found)
❌ UserServiceTest: 6 errors (Class not found)  
❌ UserDAOTest: 2 errors (Class not found)
❌ AuthControllerTest: 2 errors (Class not found)
```

This demonstrates the **RED phase** perfectly!

## 🚀 TDD Phases Demonstrated

### Phase 1: Authentication System
- **RED:** Write failing authentication tests
- **GREEN:** Create minimal `AuthService` with hardcoded logic
- **REFACTOR:** Add input validation and dependency injection

### Phase 2: User Management
- **RED:** Write failing user creation tests
- **GREEN:** Create `UserService` with basic validation
- **REFACTOR:** Extract validation logic and improve error handling

### Phase 3: Data Access Layer
- **RED:** Write failing database integration tests
- **GREEN:** Create `UserDAO` with basic database operations
- **REFACTOR:** Add query builder pattern and connection management

### Phase 4: Controller Layer
- **RED:** Write failing HTTP request handling tests
- **GREEN:** Create `AuthController` with basic request handling
- **REFACTOR:** Extract base controller and improve error handling

## 🏗️ Architecture Evolution

The TDD process naturally evolves the architecture:

```
Simple Start → TDD-Driven Architecture
    Class           Controller → Service → DAO → Database
                       ↑         ↑         ↑
                   HTTP Layer  Business  Data Access
                              Logic Layer
```

## 📁 Project Structure

```
src/App/
├── Controllers/     # HTTP Request handling (Phase 4)
├── Services/        # Business Logic (Phase 1 & 2)
├── DAO/            # Data Access (Phase 3)
├── Interfaces/     # Contracts (Refactor phase)
└── Core/           # Framework components

tests/
├── Unit/           # Isolated unit tests
└── Integration/    # End-to-end tests
```

## 🔧 Key TDD Principles Demonstrated

1. **Test First:** Always write the test before the code
2. **Minimal Implementation:** Write just enough code to pass
3. **Refactor Safely:** Improve design with test coverage
4. **Design Emergence:** Let architecture evolve from testing needs
5. **Fast Feedback:** Quick test cycles drive development

## 📈 Benefits You'll Observe

- **Better Design:** Dependency injection emerges naturally
- **Higher Quality:** Edge cases caught early through testing
- **Confidence:** Safe refactoring with comprehensive test coverage
- **Documentation:** Tests serve as living documentation
- **Maintainability:** Clean, modular code that's easy to change

## 🎮 Interactive Experience

1. **Run `php tdd_step_by_step.php`** to see the RED phase
2. **Run `php fix_tests_step_by_step.php`** to implement GREEN phase
3. **Choose phases individually** or implement all at once
4. **Watch tests go from RED → GREEN** in real-time
5. **See architecture evolve** through TDD process

## 🏆 Success Metrics

After completing the TDD cycle:
- ✅ **10+ tests passing** from 0
- ✅ **Clean architecture** with proper separation of concerns
- ✅ **Dependency injection** throughout the application
- ✅ **Exception handling** for robust error management
- ✅ **Interface-based design** for maximum testability

## 📚 Next Steps

1. Study `TDD_Documentation.md` for detailed explanations
2. Practice the cycle with your own features
3. Apply TDD principles to your projects
4. Share your TDD experience with your team

---

**Remember:** TDD is not just about testing—it's a **design methodology** that leads to better software architecture and higher code quality.

🎯 **Start your TDD journey:** `php tdd_step_by_step.php`