<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../project/php/auth.php'; 

class AuthDuplicateCheckTest extends TestCase
{
    public function testDuplicateUsername()
    {
        $pdoMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $stmtMock->method('fetch')->willReturn([
            'username' => 'duplicateUser',
            'email' => null
        ]);

        $pdoMock->method('prepare')->willReturn($stmtMock);

        $result = checkDuplicateUser($pdoMock, 'duplicateUser', 'new@example.com');
        $this->assertEquals('duplicateUser', $result['username']);
    }

    public function testDuplicateEmail()
    {
        $pdoMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $stmtMock->method('fetch')->willReturn([
            'username' => null,
            'email' => 'duplicate@example.com'
        ]);

        $pdoMock->method('prepare')->willReturn($stmtMock);

        $result = checkDuplicateUser($pdoMock, 'newUser', 'duplicate@example.com');
        $this->assertEquals('duplicate@example.com', $result['email']);
    }

    public function testNoDuplicates()
    {
        $pdoMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $stmtMock->method('fetch')->willReturn(false); // 重複なし

        $pdoMock->method('prepare')->willReturn($stmtMock);

        $result = checkDuplicateUser($pdoMock, 'newUser', 'new@example.com');
        $this->assertFalse($result);
    }

    public function testAjaxCheckDuplicate_UsernameExists()
    {
        $pdoMock = $this->createMock(PDO::class);
        $stmtMockUsername = $this->createMock(PDOStatement::class);
        $stmtMockUsername->method('fetch')->willReturn(['id' => 1]);

        $pdoMock->method('prepare')->willReturn($stmtMockUsername);

        $result = ajaxCheckDuplicate($pdoMock, 'existingUser', '');
        $this->assertTrue($result['usernameExists']);
        $this->assertFalse($result['emailExists']);
    }

    public function testAjaxCheckDuplicate_EmailExists()
    {
        $pdoMock = $this->createMock(PDO::class);
        $stmtMock1 = $this->createMock(PDOStatement::class);
        $stmtMock2 = $this->createMock(PDOStatement::class);

        // 1回目の fetch() → username → false（重複なし）
        $stmtMock1->method('fetch')->willReturn(false);

        // 2回目の fetch() → email → ['id' => 2]（重複あり）
        $stmtMock2->method('fetch')->willReturn(['id' => 2]);

        // prepare() の呼び出し順に応じて異なるモックを返す
        $pdoMock->method('prepare')
                ->willReturnOnConsecutiveCalls($stmtMock1, $stmtMock2);

        $result = ajaxCheckDuplicate($pdoMock, 'newUser', 'used@example.com');

        $this->assertFalse($result['usernameExists']);
        $this->assertTrue($result['emailExists']);
    }

    public function testAjaxCheckDuplicate_NoDuplicates()
    {
        $pdoMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        // 両方とも重複なし
        $stmtMock->method('fetch')->willReturn(false);
        $pdoMock->method('prepare')->willReturn($stmtMock);

        $result = ajaxCheckDuplicate($pdoMock, 'newUser', 'new@example.com');
        $this->assertFalse($result['usernameExists']);
        $this->assertFalse($result['emailExists']);
    }
}