<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../project/php/auth.php';

class AuthLoginTest extends TestCase
{
    public function testAuthenticateUserSuccess()
    {
        $username = 'testuser';
        $password = 'password123';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userData = [
            'id' => 1,
            'username' => $username,
            'email' => 'test@example.com',
            'password_hash' => $passwordHash,
        ];

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
                 ->method('execute')
                 ->with([$username]);
        $stmtMock->method('fetch')
                 ->willReturn($userData);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')
                ->willReturn($stmtMock);

        $result = authenticateUser($pdoMock, $username, $password);
        $this->assertIsArray($result);
        $this->assertEquals($username, $result['username']);
    }

    public function testAuthenticateUserUserNotFound()
    {
        $username = 'notfounduser';
        $password = 'password123';

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
                 ->method('execute')
                 ->with([$username]);
        $stmtMock->method('fetch')
                 ->willReturn(false);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')
                ->willReturn($stmtMock);

        $result = authenticateUser($pdoMock, $username, $password);
        $this->assertFalse($result);
    }

    public function testAuthenticateUserWrongPassword()
    {
        $username = 'testuser';
        $correctPasswordHash = password_hash('correct_password', PASSWORD_DEFAULT);
        $userData = [
            'id' => 1,
            'username' => $username,
            'email' => 'test@example.com',
            'password_hash' => $correctPasswordHash,
        ];

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
                 ->method('execute')
                 ->with([$username]);
        $stmtMock->method('fetch')
                 ->willReturn($userData);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')
                ->willReturn($stmtMock);

        $result = authenticateUser($pdoMock, $username, 'wrong_password');
        $this->assertFalse($result);
    }
}
