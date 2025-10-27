<?php

namespace Tests\Unit\DAO;

use PHPUnit\Framework\TestCase;
use App\DAO\SubjectDAO;
use App\Models\Subject;
use PDO;
use PDOStatement;

class SubjectDAOTest extends TestCase
{
    private SubjectDAO $subjectDAO;
    private PDO $mockPdo;
    private PDOStatement $mockStmt;

    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
        
        // Create a partial mock of SubjectDAO and inject the mock PDO
        $this->subjectDAO = $this->getMockBuilder(SubjectDAO::class)
            ->onlyMethods([])
            ->getMock();
        
        // Use reflection to set the private $db property
        $reflection = new \ReflectionClass($this->subjectDAO);
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($this->subjectDAO, $this->mockPdo);
    }

    /**
     * @test
     */
    public function it_should_get_all_subjects()
    {
        $expectedData = [
            [
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'description' => 'Basic concepts',
                'units' => 3,
                'year_level' => '1st Year',
                'semester' => '1st Semester',
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00'
            ],
            [
                'subject_id' => 2,
                'subject_code' => 'MATH101',
                'subject_name' => 'College Algebra',
                'description' => 'Algebraic concepts',
                'units' => 3,
                'year_level' => '1st Year',
                'semester' => '1st Semester',
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00'
            ]
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT * FROM subjects'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->exactly(2))
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturnOnConsecutiveCalls($expectedData[0], $expectedData[1], false);

        $subjects = $this->subjectDAO->getAll();

        $this->assertCount(2, $subjects);
        $this->assertInstanceOf(Subject::class, $subjects[0]);
        $this->assertEquals('CS101', $subjects[0]->getSubjectCode());
        $this->assertEquals('MATH101', $subjects[1]->getSubjectCode());
    }

    /**
     * @test
     */
    public function it_should_get_subject_by_id()
    {
        $expectedData = [
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('WHERE subject_id = ?'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1]);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $subject = $this->subjectDAO->getById(1);

        $this->assertInstanceOf(Subject::class, $subject);
        $this->assertEquals(1, $subject->getSubjectId());
        $this->assertEquals('CS101', $subject->getSubjectCode());
    }

    /**
     * @test
     */
    public function it_should_return_null_when_subject_not_found()
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

        $subject = $this->subjectDAO->getById(999);

        $this->assertNull($subject);
    }

    /**
     * @test
     */
    public function it_should_get_subject_by_code()
    {
        $expectedData = [
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('WHERE subject_code = ?'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['CS101']);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $subject = $this->subjectDAO->getByCode('CS101');

        $this->assertInstanceOf(Subject::class, $subject);
        $this->assertEquals('CS101', $subject->getSubjectCode());
    }

    /**
     * @test
     */
    public function it_should_create_subject()
    {
        $subject = new Subject([
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computer Science',
            'description' => 'Basic concepts',
            'units' => 3,
            'year_level' => '1st Year',
            'semester' => '1st Semester'
        ]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO subjects'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                'CS101',
                'Introduction to Computer Science',
                'Basic concepts',
                3,
                '1st Year',
                '1st Semester'
            ])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('5');

        $result = $this->subjectDAO->create($subject);

        $this->assertEquals(5, $result);
    }

    /**
     * @test
     */
    public function it_should_update_subject()
    {
        $subject = new Subject([
            'subject_id' => 1,
            'subject_code' => 'CS101',
            'subject_name' => 'Updated Computer Science',
            'description' => 'Updated concepts',
            'units' => 4,
            'year_level' => '2nd Year',
            'semester' => '2nd Semester'
        ]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE subjects'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                'CS101',
                'Updated Computer Science',
                'Updated concepts',
                4,
                '2nd Year',
                '2nd Semester',
                1
            ])
            ->willReturn(true);

        $result = $this->subjectDAO->update($subject);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_delete_subject()
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('DELETE FROM subjects'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        $result = $this->subjectDAO->delete(1);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_search_subjects()
    {
        $expectedData = [
            [
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'description' => 'Basic concepts',
                'units' => 3,
                'year_level' => '1st Year',
                'semester' => '1st Semester',
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00'
            ]
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('WHERE subject_code LIKE ? OR subject_name LIKE ? OR description LIKE ?'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['%computer%', '%computer%', '%computer%']);

        $this->mockStmt->expects($this->exactly(2))
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturnOnConsecutiveCalls($expectedData[0], false);

        $subjects = $this->subjectDAO->search('computer');

        $this->assertCount(1, $subjects);
        $this->assertInstanceOf(Subject::class, $subjects[0]);
        $this->assertEquals('CS101', $subjects[0]->getSubjectCode());
    }

    /**
     * @test
     */
    public function it_should_get_subjects_by_year_level()
    {
        $expectedData = [
            [
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'description' => 'Basic concepts',
                'units' => 3,
                'year_level' => '1st Year',
                'semester' => '1st Semester',
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00'
            ]
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('WHERE year_level = ?'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['1st Year']);

        $this->mockStmt->expects($this->exactly(2))
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturnOnConsecutiveCalls($expectedData[0], false);

        $subjects = $this->subjectDAO->getByYearLevel('1st Year');

        $this->assertCount(1, $subjects);
        $this->assertInstanceOf(Subject::class, $subjects[0]);
        $this->assertEquals('1st Year', $subjects[0]->getYearLevel());
    }

    /**
     * @test
     */
    public function it_should_get_subjects_by_semester()
    {
        $expectedData = [
            [
                'subject_id' => 1,
                'subject_code' => 'CS101',
                'subject_name' => 'Introduction to Computer Science',
                'description' => 'Basic concepts',
                'units' => 3,
                'year_level' => '1st Year',
                'semester' => '1st Semester',
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:00:00'
            ]
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('WHERE semester = ?'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['1st Semester']);

        $this->mockStmt->expects($this->exactly(2))
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturnOnConsecutiveCalls($expectedData[0], false);

        $subjects = $this->subjectDAO->getBySemester('1st Semester');

        $this->assertCount(1, $subjects);
        $this->assertInstanceOf(Subject::class, $subjects[0]);
        $this->assertEquals('1st Semester', $subjects[0]->getSemester());
    }

    /**
     * @test
     */
    public function it_should_check_if_subject_has_faculty_assignments()
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT COUNT(*) FROM subject_faculty'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1]);

        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(2);

        $result = $this->subjectDAO->hasFacultyAssignments(1);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_check_if_subject_has_no_faculty_assignments()
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1]);

        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(0);

        $result = $this->subjectDAO->hasFacultyAssignments(1);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_should_check_if_subject_has_exams()
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT COUNT(*) FROM exams'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1]);

        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(3);

        $result = $this->subjectDAO->hasExams(1);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_check_if_subject_has_no_exams()
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1]);

        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(0);

        $result = $this->subjectDAO->hasExams(1);

        $this->assertFalse($result);
    }
}