<?php
// セッションを開始（必要なら）
session_start();

// セッション変数を全て削除
$_SESSION = [];

// セッションを破棄
session_destroy();

// 任意でCookieも削除（セキュリティ対策）
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// ログイン画面などへリダイレクト
header("Location: index.php");
exit();
?>
