<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// ----- Ajax用の重複チェック処理 -----
// GETパラメータに "checkDuplicate" が含まれている場合、ユーザー名・メールアドレスの重複チェックを行い JSON を返す
if (isset($_GET['checkDuplicate'])) {
    $response = [
        'usernameExists' => false,
        'emailExists'    => false,
    ];

    if (isset($_GET['username']) && $_GET['username'] !== "") {
        $username = trim($_GET['username']);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $response['usernameExists'] = true;
        }
    }

    if (isset($_GET['email']) && $_GET['email'] !== "") {
        $email = trim($_GET['email']);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $response['emailExists'] = true;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
// ----- Ajax用の重複チェック処理 終了 -----

// POST送信された場合
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'signup') {
        $username  = trim($_POST["username"]);
        $email     = trim($_POST["email"]);
        $password  = $_POST["password"];
        $password2 = $_POST["password2"];

        if ($password !== $password2) {
            $_SESSION["error"] = "パスワードが一致しません";
            $_SESSION["action"] = "signup";
            header("Location: index.php");
            exit();
        } elseif (strlen($password) < 5) {
            $_SESSION["error"] = "パスワードは5文字以上でなければなりません";
            $_SESSION["action"] = "signup";
            header("Location: index.php");
            exit();
        } else {
            // サーバー側で重複チェック
            $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $errMsg = "";
                if (isset($existing['username']) && $existing['username'] === $username) {
                    $errMsg .= "そのユーザー名は使用されています";
                }
                if (isset($existing['email']) && $existing['email'] === $email) {
                    $errMsg .= (empty($errMsg) ? "" : "<br>") . "そのメールアドレスは使用されています";
                }
                $_SESSION["error"] = $errMsg;
                $_SESSION["action"] = "signup";
                header("Location: index.php");
                exit();
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $email, $password_hash]);

                    // 登録成功後は自動ログインしてダッシュボードへリダイレクト
                    $_SESSION["user_id"] = $pdo->lastInsertId();
                    $_SESSION["username"] = $username;
                    header("Location: dashboard.php");
                    exit();
                } catch (PDOException $e) {
                    $_SESSION["error"] = "登録に失敗しました: " . $e->getMessage();
                    $_SESSION["action"] = "signup";
                    header("Location: index.php");
                    exit();
                }
            }
        }
    } elseif ($_POST['action'] === 'signin') {
        $username = trim($_POST["username"]);
        $password = $_POST["password"];

        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION["error"] = "UsernameまたはPasswordが正しくありません";
            $_SESSION["action"] = "signin";
            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Quiz App</title>
  <link rel="icon" href="../image/icon.png">
  <link rel="stylesheet" href="../css/main.css">
</head>
<body class="no-scroll">
  <?php include 'header.php'; ?>

  <main class="hero">
    <div class="left">
      <!-- サインイン・サインアップ用ボタン -->
      <a href="#" id="openSignIn" class="btn">SIGN IN <span class="sign-arrow">➡︎</span></a>
      <a href="#" id="openSignUp" class="btn">SIGN UP <span class="sign-arrow">➡︎</span></a>
    </div>
    <div class="right">
      <img src="../image/zebla.png" alt="Quiz App Image" class="zebra">
      <div class="stripes"></div>
    </div>
  </main>
  
  <footer>
    <div></div>
  </footer>

  <!-- Sign In モーダル -->
  <div id="modalSignIn" class="modal">
    <div class="modal-content">
      <span class="close" id="closeSignIn">&times;</span>
      <h2>Sign In</h2>
      <!-- サインイン処理でエラーがあった場合 -->
      <?php if (!empty($_SESSION["error"]) && isset($_SESSION["action"]) && $_SESSION["action"] === 'signin'): ?>
        <div id="signInError" style="color: red;">
          <?php echo htmlspecialchars($_SESSION["error"], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <script>
          document.getElementById('modalSignIn').classList.add("show");
        </script>
        <?php unset($_SESSION["error"], $_SESSION["action"]); ?>
      <?php endif; ?>

      <form id="formSignIn" method="POST" action="">
        <input type="hidden" name="action" value="signin">
        <label for="signin-username">Username:</label>
        <input type="text" id="signin-username" name="username" required>
        <br>
        <label for="signin-password">Password:</label>
        <input type="password" id="signin-password" name="password" required>
        <br>
        <button type="submit">Sign In</button>
      </form>
    </div>
  </div>

  <!-- Sign Up モーダル -->
  <div id="modalSignUp" class="modal">
    <div class="modal-content">
      <span class="close" id="closeSignUp">&times;</span>
      <h2>Sign Up</h2>
      <!-- Ajaxによる重複チェック用エリア -->
      <div id="signUpError" style="display:none; color:red;"></div>
      
      <?php if (!empty($_SESSION["error"]) && isset($_SESSION["action"]) && $_SESSION["action"] === 'signup'): ?>
        <div id="serverSignUpError">
          <?php echo htmlspecialchars($_SESSION["error"], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <script>
          document.getElementById('modalSignUp').classList.add("show");
        </script>
        <?php unset($_SESSION["error"], $_SESSION["action"]); ?>
      <?php endif; ?>
      
      <form id="formSignUp" method="POST" action="">
        <input type="hidden" name="action" value="signup">
        <label for="signup-username">Username:</label>
        <input type="text" id="signup-username" name="username" required>
        <label for="signup-email">Email:</label>
        <input type="email" id="signup-email" name="email" required>
        <label for="signup-password">Password:</label>
        <!-- HTML5 の minlength 属性で簡易ブラウザ側チェック -->
        <input type="password" id="signup-password" name="password" minlength="5" required>
        <label for="signup-password2">Confirm Password:</label>
        <input type="password" id="signup-password2" name="password2" required>
        <button type="submit">Sign Up</button>
      </form>
    </div>
  </div>

  <!-- JavaScript: モーダルの開閉・エラーメッセージ非表示、フォームリセット、パスワードチェック、Ajax 重複チェック -->
  <script src="../js/signin.js"></script>
  
  <!-- ※ エラー情報はリダイレクト後に unset されるため、リロード時にモーダルが自動表示されることはありません -->
  
</body>
</html>
