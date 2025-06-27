<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../project/php/auth.php';

class AuthRegisterTest extends TestCase
{
    public function testRegisterUserSuccess()
    {
        $username = 'testuser';
        $email = 'test@example.com';
        $password = 'password123';

        // PDOStatement モック
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
                 ->method('execute')
                 ->with($this->callback(function($params) use ($username, $email, $password) {
                     return $params[0] === $username
                         && $params[1] === $email
                         && password_verify($password, $params[2]); // ここで直接検証
                 }));

        // PDO モック
        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')
                ->with($this->stringContains('INSERT INTO users'))
                ->willReturn($stmtMock);

        $pdoMock->method('lastInsertId')
                ->willReturn('42');

        $result = registerUser($pdoMock, $username, $email, $password);
        $this->assertEquals('42', $result);
    }
}
