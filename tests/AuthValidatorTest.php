<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../project/php/auth.php'; 

class AuthValidatorTest extends TestCase
{
    public function testAllValidInputs()
    {
        $errors = validateSignUpData("user123", "user@example.com", "secret123", "secret123");
        $this->assertEmpty($errors);
    }

    public function testEmptyUsername()
    {
        $errors = validateSignUpData("", "user@example.com", "secret123", "secret123");
        $this->assertContains("ユーザー名を入力してください", $errors);
    }

    public function testInvalidEmail()
    {
        $errors = validateSignUpData("user123", "invalid-email", "secret123", "secret123");
        $this->assertContains("有効なメールアドレスを入力してください", $errors);
    }

    public function testPasswordTooShort()
    {
        $errors = validateSignUpData("user123", "user@example.com", "abc", "abc");
        $this->assertContains("パスワードは5文字以上でなければなりません", $errors);
    }

    public function testPasswordMismatch()
    {
        $errors = validateSignUpData("user123", "user@example.com", "secret123", "different123");
        $this->assertContains("パスワードが一致しません", $errors);
    }

    public function testMultipleErrors()
    {
        $errors = validateSignUpData("", "bad-email", "123", "456");
        $this->assertContains("ユーザー名を入力してください", $errors);
        $this->assertContains("有効なメールアドレスを入力してください", $errors);
        $this->assertContains("パスワードは5文字以上でなければなりません", $errors);
        $this->assertContains("パスワードが一致しません", $errors);
        $this->assertCount(4, $errors);
    }
}