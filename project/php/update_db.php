<?php
session_start();
header('Content-Type: application/json');

// .env.php などを読み込む（例: require_once('.env.php');）
// DB接続情報を.env.phpから取得
require_once __DIR__ . '/db_connect.php';

$editType = $_POST['editType'] ?? null;
if (!$editType) {
    echo json_encode(["success" => false, "error" => "編集内容が指定されていません"]);
    exit;
}

try {
    if ($editType === "Pass Word") {
        $currentPassword = $_POST["current-password"] ?? "";
        $newPassword = $_POST["new-password"] ?? "";
        $confirmPassword = $_POST["confirm-password"] ?? "";

        // 新しいパスワードと確認用パスワードが一致するかチェック
        if ($newPassword !== $confirmPassword) {
            echo json_encode(["success" => false, "error" => "新しいパスワードと確認用パスワードが一致しません"]);
            exit;
        }

        // 新しいパスワードが5文字以上かチェック
        if (strlen($newPassword) < 5) {
            echo json_encode(["success" => false, "error" => "新しいパスワードは5文字以上にしてください"]);
            exit;
        }

        // 現在のパスワードをデータベースから取得し、入力されたものと照合
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->execute(["id" => $_SESSION['user_id']]);
        $userData = $stmt->fetch();

        if (!$userData || !password_verify($currentPassword, $userData['password_hash'])) {
            echo json_encode(["success" => false, "error" => "現在のパスワードが正しくありません"]);
            exit;
        }

        // 入力が全て正しい場合は、新しいパスワードをハッシュ化して更新
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmtUpdate = $pdo->prepare("UPDATE users SET password_hash = :password WHERE id = :id");
        $stmtUpdate->execute([
            "password" => $hashedPassword,
            "id"       => $_SESSION['user_id']
        ]);
        // パスワード更新の場合は新たな値の返送は不要
        $response = ["success" => true];
    } elseif ($editType === "User Name") {
        $name = $_POST["single-input"] ?? "";
        $stmt = $pdo->prepare("UPDATE users SET username = :username WHERE id = :id");
        $stmt->execute([
            "username" => $name,
            "id"       => $_SESSION['user_id']
        ]);
        // セッションのユーザーネームも更新
        $_SESSION['username'] = $name;
        // 更新後のユーザーネームをレスポンスにも含める
        $response = ["success" => true, "new_username" => $name];
    } elseif ($editType === "Email Address") {
        $email = $_POST["single-input"] ?? "";
        // メール形式のバリデーション（サーバー側でも再確認）
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "error" => "正しいメールアドレスを入力してください"]);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE users SET email = :email WHERE id = :id");
        $stmt->execute([
            "email" => $email,
            "id"    => $_SESSION['user_id']
        ]);

        // セッションのメールアドレスも更新
        $_SESSION['email'] = $email;
        $response = ["success" => true, "new_email" => $email];
    } else {
        echo json_encode(["success" => false, "error" => "不明な編集タイプ"]);
        exit;
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
