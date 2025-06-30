<?php
function login_required() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}

function validateSignUpData($username, $email, $password, $password2) {
    $errors = [];

    // ユーザー名
    if (empty($username)) {
        $errors[] = "ユーザー名を入力してください";
    }

    // メールアドレス
    if (empty($email)) {
        $errors[] = "メールアドレスを入力してください";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "有効なメールアドレスを入力してください";
    }

    // パスワード
    if (empty($password)) {
        $errors[] = "パスワードを入力してください";
    } elseif (strlen($password) < 5) {
        $errors[] = "パスワードは5文字以上でなければなりません";
    }

    // パスワード（確認）
    if (empty($password2)) {
        $errors[] = "確認用パスワードを入力してください";
    } elseif ($password !== $password2) {
        $errors[] = "パスワードが一致しません";
    }

    return $errors;
}


function checkDuplicateUser($pdo, $username, $email) {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function registerUser($pdo, $username, $email, $password) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $password_hash]);
    return $pdo->lastInsertId();
}

function authenticateUser($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user["password_hash"])) {
        return $user;
    }
    return false;
}

function ajaxCheckDuplicate($pdo, $username, $email) {
    $response = [
        'usernameExists' => false,
        'emailExists' => false,
    ];

    if ($username !== "") {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $response['usernameExists'] = true;
        }
    }

    if ($email !== "") {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $response['emailExists'] = true;
        }
    }

    return $response;
}