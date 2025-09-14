<?php

namespace Tests\Unit\DAO;

use PHPUnit\Framework\TestCase;
use App\DAO\AssignmentDAO;
use App\Models\SubjectAssignment;
use PDO;
use PDOStatement;

class AssignmentDAOTest extends TestCase
{
    private $mockPDO;
    private $mockStatement;
    private $assignmentDAO;

    protected function setUp(): void
    {
        $this->mockPDO = $this->createMock(PDO::class);
        $this->mockStatement = $this->createMock(PDOStatement::class);
        $this->assignmentDAO = new AssignmentDAO($this->mockPDO);
    }

    /** @test */
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
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00',
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'faculty_name' => 'John Doe'
            ]
        ];

        $this->mockPDO->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT sa.*, s.subject_code, s.subject_name, u.full_name as faculty_name'))
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStatement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $result = $this->assignmentDAO->getAll();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(SubjectAssignment::class, $result[0]);
        $this->assertEquals(1, $result[0]->getId());
        $this->assertEquals('CS101', $result[0]->getSubjectCode());
    }

    /** @test */
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
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00',
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'faculty_name' => 'John Doe'
        ];

        $this->mockPDO->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT sa.*, s.subject_code, s.subject_name, u.full_name as faculty_name'))
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        $this->mockStatement->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $result = $this->assignmentDAO->getById(1);

        $this->assertInstanceOf(SubjectAssignment::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('CS101', $result->getSubjectCode());
    }

    /** @test */
    public function it_should_return_null_when_assignment_not_found()
    {
        $this->mockPDO->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute')
            ->with([999])
            ->willReturn(true);

        $this->mockStatement->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $result = $this->assignmentDAO->getById(999);

        $this->assertNull($result);
    }

    /** @test */
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

        $this->mockPDO->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO subject_assignments'))
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute')
            ->with([1, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'active', 'Test assignment'])
            ->willReturn(true);

        $this->mockPDO->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('5');

        $result = $this->assignmentDAO->create($assignment);

        $this->assertInstanceOf(SubjectAssignment::class, $result);
        $this->assertEquals(5, $result->getId());
    }

    /** @test */
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
            'status' => 'active',
            'notes' => 'Updated assignment'
        ]);

        $this->mockPDO->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE subject_assignments'))
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute')
            ->with([1, 2, '1st Year', 'A', '2024-2025', '1st Semester', 'active', 'Updated assignment', 1])
            ->willReturn(true);

        $result = $this->assignmentDAO->update($assignment);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_should_delete_assignment()
    {
        $this->mockPDO->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('DELETE FROM subject_assignments'))
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        $result = $this->assignmentDAO->delete(1);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_should_check_assignment_exists()
    {
        $this->mockPDO->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT COUNT(*) FROM subject_assignments'))
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute')
            ->with([1, '1st Year', 'A', '2024-2025', '1st Semester'])
            ->willReturn(true);

        $this->mockStatement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        $result = $this->assignmentDAO->assignmentExists(1, '1st Year', 'A', '2024-2025', '1st Semester');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_should_get_assignments_by_filters()
    {
        $filters = [
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ];

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
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00',
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'faculty_name' => 'John Doe'
            ]
        ];

        $this->mockPDO->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT sa.*, s.subject_code, s.subject_name, u.full_name as faculty_name'))
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStatement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $result = $this->assignmentDAO->getByFilters($filters);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(SubjectAssignment::class, $result[0]);
    }

    /** @test */
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
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00',
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'units' => 3
            ]
        ];

        $this->mockPDO->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT sa.*, s.subject_code, s.subject_name, s.units'))
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute')
            ->with([2, '2024-2025'])
            ->willReturn(true);

        $this->mockStatement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $result = $this->assignmentDAO->getFacultyWorkload(2, '2024-2025');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(SubjectAssignment::class, $result[0]);
    }

    /** @test */
    public function it_should_get_assignment_statistics()
    {
        $expectedData = [
            'total_assignments' => 10,
            'active_assignments' => 8,
            'pending_assignments' => 2,
            'inactive_assignments' => 0
        ];

        $this->mockPDO->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT COUNT(*) as total_assignments'))
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute')
            ->with(['2024-2025'])
            ->willReturn(true);

        $this->mockStatement->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $result = $this->assignmentDAO->getAssignmentStats('2024-2025');

        $this->assertIsArray($result);
        $this->assertEquals(10, $result['total_assignments']);
        $this->assertEquals(8, $result['active_assignments']);
    }
}