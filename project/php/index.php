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

$error = "";  // エラーメッセージ用変数

// POST送信された場合の処理
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'signup') {
        $username  = trim($_POST["username"]);
        $email     = trim($_POST["email"]);
        $password  = $_POST["password"];
        $password2 = $_POST["password2"];

        if ($password !== $password2) {
            $error = "パスワードが一致しません";
        }
        // サーバー側のパスワード文字数チェック
        elseif (strlen($password) < 5) {
            $error = "パスワードは5文字以上でなければなりません";
        }
        else {
            // サーバー側で重複チェック（Ajax側チェックはユーザー体験向上の補助）
            $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if (isset($existing['username']) && $existing['username'] === $username) {
                    $error = "そのユーザー名は使用されています";
                }
                if (isset($existing['email']) && $existing['email'] === $email) {
                    $error .= (empty($error) ? "" : "<br>") . "そのメールアドレスは使用されています";
                }
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
    }
    elseif ($_POST['action'] === 'signin') {
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
      <img src="../image/Blue and White Modern Illustrative Thesis Defense Presentation.png" alt="Quiz App Image" class="zebra">
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
      
      <!-- サインイン処理でエラーがあった場合、エラーメッセージを赤色で表示 -->
      <?php if (!empty($error) && isset($_POST['action']) && $_POST['action'] === 'signin'): ?>
        <div id="signInError" style="color: red;">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <script>
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
      <!-- Ajaxによる重複チェック用エリア -->
      <div id="signUpError" style="display:none; color:red;"></div>
      
      <?php if (!empty($error) && isset($_POST['action']) && $_POST['action'] === 'signup'): ?>
        <div id="serverSignUpError">
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
        <label for="signup-email">Email:</label>
        <input type="email" id="signup-email" name="email" required>
        <label for="signup-password">Password:</label>
        <!-- HTML5 の minlength 属性でブラウザ側の簡易チェックも -->
        <input type="password" id="signup-password" name="password" minlength="5" required>
        <label for="signup-password2">Confirm Password:</label>
        <input type="password" id="signup-password2" name="password2" required>
        <button type="submit">Sign Up</button>
      </form>
    </div>
  </div>

  <!-- JavaScript: モーダルの開閉・エラー非表示、パスワードチェック、Ajax重複チェック、及びフォームリセット -->
  <script>
    // モーダル要素の取得
    const modalSignIn = document.getElementById("modalSignIn");
    const modalSignUp = document.getElementById("modalSignUp");
    
    // 各フォーム要素の取得
    const formSignIn = document.getElementById("formSignIn");
    const formSignUp = document.getElementById("formSignUp");

    // ボタン・閉じるアイコンの取得
    const openSignIn = document.getElementById("openSignIn");
    const openSignUp = document.getElementById("openSignUp");
    const closeSignIn = document.getElementById("closeSignIn");
    const closeSignUp = document.getElementById("closeSignUp");

    // モーダルを開く
    openSignIn.addEventListener("click", (e) => {
      e.preventDefault();
      modalSignIn.style.display = "block";
    });
    openSignUp.addEventListener("click", (e) => {
      e.preventDefault();
      modalSignUp.style.display = "block";
    });

    // モーダルを閉じるとき（×ボタン）にフォームリセットを実行
    closeSignIn.addEventListener("click", () => {
      modalSignIn.style.display = "none";
      const errorMsg = document.getElementById("signInError");
      if (errorMsg) { errorMsg.style.display = "none"; }
      formSignIn.reset();
    });
    closeSignUp.addEventListener("click", () => {
      modalSignUp.style.display = "none";
      const serverError = document.getElementById("serverSignUpError");
      if (serverError) { serverError.style.display = "none"; }
      document.getElementById("signUpError").style.display = "none";
      formSignUp.reset();
    });

    // 画面外クリック時にモーダルを閉じ、フォームリセットを実行
    window.addEventListener("click", (e) => {
      if (e.target === modalSignIn) {
        modalSignIn.style.display = "none";
        const errorMsg = document.getElementById("signInError");
        if (errorMsg) { errorMsg.style.display = "none"; }
        formSignIn.reset();
      }
      if (e.target === modalSignUp) {
        modalSignUp.style.display = "none";
        const serverError = document.getElementById("serverSignUpError");
        if (serverError) { serverError.style.display = "none"; }
        document.getElementById("signUpError").style.display = "none";
        formSignUp.reset();
      }
    });

    // Sign Up フォーム: パスワード一致・文字数チェック、Ajax 重複チェック
    document.getElementById("formSignUp").addEventListener("submit", async (e) => {
      e.preventDefault();
      const pw1 = document.getElementById("signup-password").value;
      const pw2 = document.getElementById("signup-password2").value;
      if (pw1 !== pw2) {
        alert("パスワードが一致しません");
        return;
      }
      if (pw1.length < 5) {
        alert("パスワードは5文字以上でなければなりません");
        return;
      }
      const username = document.getElementById("signup-username").value.trim();
      const email = document.getElementById("signup-email").value.trim();
      const errorDiv = document.getElementById("signUpError");

      try {
        const response = await fetch(`index.php?checkDuplicate=1&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`);
        const result = await response.json();
        let errorMessages = [];
        if (result.usernameExists) {
          errorMessages.push("そのユーザー名は使用されています");
        }
        if (result.emailExists) {
          errorMessages.push("そのメールアドレスは使用されています");
        }
        if (errorMessages.length > 0) {
          errorDiv.innerHTML = errorMessages.join("<br>");
          errorDiv.style.display = "block";
          return; // 重複があれば送信を中断
        } else {
          errorDiv.style.display = "none";
          e.target.submit();  // 問題なければフォーム送信
        }
      } catch (err) {
        console.error(err);
      }
    });

    // Optional: 入力後 (blur イベント) に Ajax 重複チェックを実施
    const signupUsernameInput = document.getElementById("signup-username");
    const signupEmailInput = document.getElementById("signup-email");
    signupUsernameInput.addEventListener("blur", () => checkDuplicate());
    signupEmailInput.addEventListener("blur", () => checkDuplicate());

    function checkDuplicate() {
      const username = signupUsernameInput.value.trim();
      const email = signupEmailInput.value.trim();
      const errorDiv = document.getElementById("signUpError");
      if (username === "" && email === "") {
        errorDiv.style.display = "none";
        return;
      }
      fetch(`index.php?checkDuplicate=1&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`)
        .then(response => response.json())
        .then(result => {
          let errorMessages = [];
          if (result.usernameExists) { errorMessages.push("そのユーザー名は使用されています"); }
          if (result.emailExists) { errorMessages.push("そのメールアドレスは使用されています"); }
          if (errorMessages.length > 0) {
            errorDiv.innerHTML = errorMessages.join("<br>");
            errorDiv.style.display = "block";
          } else {
            errorDiv.style.display = "none";
          }
        })
        .catch(err => console.error(err));
    }
    
    // エラー発生後、POST 状態を GET 状態に置換して再送信防止
    <?php if (!empty($error)) : ?>
      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
      }
    <?php endif; ?>
  </script>
</body>
</html>
