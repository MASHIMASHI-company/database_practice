<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$error = "";  // エラーメッセージ用変数

// POST送信された場合、処理を実行する
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    // サインアップ処理
    if ($_POST['action'] === 'signup') {
        $username  = trim($_POST["username"]);
        $email     = trim($_POST["email"]);
        $password  = $_POST["password"];
        $password2 = $_POST["password2"];

        if ($password !== $password2) {
            $error = "パスワードが一致しません";
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
                $error = "登録に失敗しました: " . $e->getMessage();
            }
        }
    }
    // サインイン処理
    elseif ($_POST['action'] === 'signin') {
        $username = trim($_POST["username"]);
        $password = $_POST["password"];

        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "UsernameまたはPasswordが正しくありません";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Quiz App</title>
  <link rel="stylesheet" href="../css/main.css">
</head>
<body class="no-scroll">
  <?php include 'header.php'; ?>

  <main class="hero">
    <div class="left">
      <!-- サインイン・サインアップ用のボタン -->
      <a href="#" id="openSignIn" class="btn">SIGN IN <span class="sign-arrow">➡︎</span></a>
      <a href="#" id="openSignUp" class="btn">SIGN UP <span class="sign-arrow">➡︎</span></a>
    </div>
    <div class="right">
      <img src="../image/Blue and White Modern Illustrative Thesis Defense Presentation.png" alt="Quiz App Image" class="zebra">
      <div class="stripes"></div>
    </div>
  </main>

  <?php include 'footer.php'; ?>

  <!-- Sign In モーダル -->
  <div id="modalSignIn" class="modal">
    <div class="modal-content">
      <span class="close" id="closeSignIn">&times;</span>
      <h2>Sign In</h2>
      
      <!-- サインイン処理でエラーがあった場合、モーダル内にエラーメッセージを表示 -->
      <?php if (!empty($error) && isset($_POST['action']) && $_POST['action'] === 'signin'): ?>
        <div id="signInError">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <script>
          // エラー発生時は自動的にサインインモーダルを表示
          document.getElementById('modalSignIn').style.display = "block";
        </script>
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
      <?php if (!empty($error) && isset($_POST['action']) && $_POST['action'] === 'signup'): ?>
        <div id="signUpError">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <script>
          document.getElementById('modalSignUp').style.display = "block";
        </script>
      <?php endif; ?>
      <form id="formSignUp" method="POST" action="">
        <input type="hidden" name="action" value="signup">
        <label for="signup-username">Username:</label>
        <input type="text" id="signup-username" name="username" required>
        <br>
        <label for="signup-email">Email:</label>
        <input type="email" id="signup-email" name="email" required>
        <br>
        <label for="signup-password">Password:</label>
        <input type="password" id="signup-password" name="password" required>
        <br>
        <label for="signup-password2">Confirm Password:</label>
        <input type="password" id="signup-password2" name="password2" required>
        <br>
        <button type="submit">Sign Up</button>
      </form>
    </div>
  </div>

  <!-- モーダルの開閉・エラー非表示処理用の JavaScript -->
  <script>
    // モーダル要素の取得
    const modalSignIn = document.getElementById("modalSignIn");
    const modalSignUp = document.getElementById("modalSignUp");

    // 開くボタン・閉じるボタンの取得
    const openSignIn = document.getElementById("openSignIn");
    const openSignUp = document.getElementById("openSignUp");
    const closeSignIn = document.getElementById("closeSignIn");
    const closeSignUp = document.getElementById("closeSignUp");

    // サインインモーダルを開く
    openSignIn.addEventListener("click", function(e) {
      e.preventDefault();
      modalSignIn.style.display = "block";
    });
    // サインアップモーダルを開く
    openSignUp.addEventListener("click", function(e) {
      e.preventDefault();
      modalSignUp.style.display = "block";
    });

    // サインインモーダルを閉じる処理
    closeSignIn.addEventListener("click", function() {
      modalSignIn.style.display = "none";
      const errorMsg = document.getElementById("signInError");
      if (errorMsg) {
        errorMsg.style.display = "none";
      }
    });
    // サインアップモーダルを閉じる処理
    closeSignUp.addEventListener("click", function() {
      modalSignUp.style.display = "none";
      const errorMsg = document.getElementById("signUpError");
      if (errorMsg) {
        errorMsg.style.display = "none";
      }
    });

    // モーダル外クリックの場合の処理（エラー表示も非表示）
    window.addEventListener("click", function(event) {
      if (event.target === modalSignIn) {
        modalSignIn.style.display = "none";
        const errorMsg = document.getElementById("signInError");
        if (errorMsg) {
          errorMsg.style.display = "none";
        }
      }
      if (event.target === modalSignUp) {
        modalSignUp.style.display = "none";
        const errorMsg = document.getElementById("signUpError");
        if (errorMsg) {
          errorMsg.style.display = "none";
        }
      }
    });
    
    // エラー発生後の POST 状態を GET 状態に置き換え
    <?php if (!empty($error)) : ?>
      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
      }
    <?php endif; ?>
  </script>
</body>
</html>
