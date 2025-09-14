<?php

namespace Tests\Unit\DAO;

use PHPUnit\Framework\TestCase;
use App\DAO\AssignmentDAO;
use App\Models\SubjectAssignment;
use PDO;
use PDOStatement;

class AssignmentDAOTest extends TestCase
{
    private PDO $mockPdo;
    private PDOStatement $mockStmt;

    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
    }

    /**
     * Helper method to create an AssignmentDAO with a mock PDO
     */
    private function createAssignmentDAOWithMockPDO()
    {
        $assignmentDAO = new AssignmentDAO();
        
        // Use reflection to inject the mock PDO
        $reflection = new \ReflectionClass($assignmentDAO);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($assignmentDAO, $this->mockPdo);
        
        return $assignmentDAO;
    }

    /**
     * @test
     */
    public function it_should_get_all_assignments()
    {
        $expectedData = [
            [
                'id' => 1,
                'subject_id' => 1,
                'faculty_id' => 2,
                'year_level' => '1st Year',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active',
                'notes' => 'Test assignment',
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'faculty_name' => 'John Doe',
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00'
            ],
            [
                'id' => 2,
                'subject_id' => 2,
                'faculty_id' => 3,
                'year_level' => '2nd Year',
                'section' => 'B',
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active',
                'notes' => 'Another assignment',
                'subject_code' => 'MATH101',
                'subject_name' => 'College Algebra',
                'faculty_name' => 'Jane Smith',
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00'
            ]
        ];

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT sa.*, s.subject_code, s.subject_name, u.full_name as faculty_name'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->exactly(3))
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturnOnConsecutiveCalls($expectedData[0], $expectedData[1], false);

        $assignments = $assignmentDAO->getAll();

        $this->assertCount(2, $assignments);
        $this->assertInstanceOf(SubjectAssignment::class, $assignments[0]);
        $this->assertEquals(1, $assignments[0]->getId());
        $this->assertEquals(2, $assignments[1]->getId());
    }

    /**
     * @test
     */
    public function it_should_get_assignment_by_id()
    {
        $expectedData = [
            'id' => 1,
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment',
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'faculty_name' => 'John Doe',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ];

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('WHERE sa.id = ?'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1]);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $assignment = $assignmentDAO->getById(1);

        $this->assertInstanceOf(SubjectAssignment::class, $assignment);
        $this->assertEquals(1, $assignment->getId());
        $this->assertEquals(1, $assignment->getSubjectId());
    }

    /**
     * @test
     */
    public function it_should_return_null_when_assignment_not_found()
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([999]);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $assignment = $assignmentDAO->getById(999);

        $this->assertNull($assignment);
    }

    /**
     * @test
     */
    public function it_should_create_assignment()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment'
        ]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO subject_assignments'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                1, // subject_id
                2, // faculty_id
                '1st Year', // year_level
                'A', // section
                '2024-2025', // academic_year
                '1st Semester', // semester
                'active', // status
                'Test assignment' // notes
            ])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('5');

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $result = $assignmentDAO->create($assignment);

        $this->assertInstanceOf(SubjectAssignment::class, $result);
        $this->assertEquals(5, $result->getId());
    }

    /**
     * @test
     */
    public function it_should_update_assignment()
    {
        $assignment = new SubjectAssignment([
            'id' => 1,
            'subject_id' => 1,
            'faculty_id' => 2,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'inactive',
            'notes' => 'Updated assignment'
        ]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE subject_assignments'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                1, // subject_id
                2, // faculty_id
                '1st Year', // year_level
                'A', // section
                '2024-2025', // academic_year
                '1st Semester', // semester
                'inactive', // status
                'Updated assignment', // notes
                1 // id
            ])
            ->willReturn(true);

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $result = $assignmentDAO->update($assignment);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_delete_assignment()
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('DELETE FROM subject_assignments'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $result = $assignmentDAO->delete(1);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_check_if_assignment_exists()
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT COUNT(*) FROM subject_assignments'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1, '1st Year', 'A', '2024-2025', '1st Semester']);

        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $result = $assignmentDAO->assignmentExists(1, '1st Year', 'A', '2024-2025', '1st Semester');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_check_if_assignment_exists_with_exclude_id()
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND id != ?'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1, '1st Year', 'A', '2024-2025', '1st Semester', 5]);

        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(0);

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $result = $assignmentDAO->assignmentExists(1, '1st Year', 'A', '2024-2025', '1st Semester', 5);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_should_get_assignments_by_filters()
    {
        $expectedData = [
            [
                'id' => 1,
                'subject_id' => 1,
                'faculty_id' => 2,
                'year_level' => '1st Year',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active',
                'notes' => 'Test assignment',
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'faculty_name' => 'John Doe',
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00'
            ]
        ];

        $filters = [
            'year_level' => '1st Year',
            'status' => 'active'
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('WHERE 1=1'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['1st Year', 'active']);

        $this->mockStmt->expects($this->exactly(2))
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturnOnConsecutiveCalls($expectedData[0], false);

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $assignments = $assignmentDAO->getByFilters($filters);

        $this->assertCount(1, $assignments);
        $this->assertInstanceOf(SubjectAssignment::class, $assignments[0]);
        $this->assertEquals('1st Year', $assignments[0]->getYearLevel());
    }

    /**
     * @test
     */
    public function it_should_get_faculty_workload()
    {
        $expectedData = [
            [
                'id' => 1,
                'subject_id' => 1,
                'faculty_id' => 2,
                'year_level' => '1st Year',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active',
                'notes' => 'Test assignment',
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'units' => 3,
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00'
            ]
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('WHERE sa.faculty_id = ? AND sa.status = \'active\''))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([2]);

        $this->mockStmt->expects($this->exactly(2))
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturnOnConsecutiveCalls($expectedData[0], false);

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $assignments = $assignmentDAO->getFacultyWorkload(2);

        $this->assertCount(1, $assignments);
        $this->assertInstanceOf(SubjectAssignment::class, $assignments[0]);
        $this->assertEquals(2, $assignments[0]->getFacultyId());
    }

    /**
     * @test
     */
    public function it_should_get_unassigned_subjects()
    {
        $expectedData = [
            [
                'subject_id' => 3,
                'subject_code' => 'PHYS101',
                'subject_name' => 'Physics',
                'description' => 'Basic physics',
                'units' => 3,
                'year_level' => '1st Year',
                'semester' => '1st Semester'
            ]
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('NOT EXISTS'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['2024-2025', '1st Semester']);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $subjects = $assignmentDAO->getUnassignedSubjects('2024-2025', '1st Semester');

        $this->assertCount(1, $subjects);
        $this->assertEquals('PHYS101', $subjects[0]['subject_code']);
    }

    /**
     * @test
     */
    public function it_should_get_assignment_stats()
    {
        $expectedData = [
            'total_assignments' => 10,
            'active_assignments' => 8,
            'inactive_assignments' => 1,
            'pending_assignments' => 1,
            'total_faculty' => 5,
            'total_subjects' => 7
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('COUNT(*) as total_assignments'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([]);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $stats = $assignmentDAO->getAssignmentStats();

        $this->assertEquals(10, $stats['total_assignments']);
        $this->assertEquals(8, $stats['active_assignments']);
        $this->assertEquals(5, $stats['total_faculty']);
    }

    /**
     * @test
     */
    public function it_should_get_assignment_stats_with_academic_year()
    {
        $expectedData = [
            'total_assignments' => 5,
            'active_assignments' => 4,
            'inactive_assignments' => 1,
            'pending_assignments' => 0,
            'total_faculty' => 3,
            'total_subjects' => 4
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('WHERE academic_year = ?'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['2024-2025']);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        // Create AssignmentDAO with mock PDO
        $assignmentDAO = $this->createAssignmentDAOWithMockPDO();
        
        $stats = $assignmentDAO->getAssignmentStats('2024-2025');

        $this->assertEquals(5, $stats['total_assignments']);
        $this->assertEquals(4, $stats['active_assignments']);
    }
}