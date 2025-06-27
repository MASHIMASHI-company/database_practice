<?php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/auth.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ajax重複チェック
if (isset($_GET['checkDuplicate'])) {
    $username = isset($_GET['username']) ? trim($_GET['username']) : "";
    $email = isset($_GET['email']) ? trim($_GET['email']) : "";

    $response = ajaxCheckDuplicate($pdo, $username, $email);

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// POST送信された場合
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
  if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION["error"] = "不正なアクセスが検出されました。";
    $_SESSION["action"] = $_POST['action'] ?? '';
    header("Location: index.php");
    exit();
  }

    if ($_POST['action'] === 'signup') {
        $username  = trim($_POST["username"]);
        $email     = trim($_POST["email"]);
        $password  = $_POST["password"];
        $password2 = $_POST["password2"];

        $errors = validateSignUpData($username, $email, $password, $password2);

        if (!empty($errors)) {
            $_SESSION["error"] = implode("<br>", $errors);
            $_SESSION["action"] = "signup";
            header("Location: index.php");
            exit();
        }

        $existing = checkDuplicateUser($pdo, $username, $email);
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
        }

        try {
            $userId = registerUser($pdo, $username, $email, $password);

            $_SESSION["user_id"] = $userId;
            $_SESSION["username"] = $username;
            $_SESSION["email"] = $email;
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION["error"] = "登録に失敗しました: " . $e->getMessage();
            $_SESSION["action"] = "signup";
            header("Location: index.php");
            exit();
        }
    } elseif ($_POST['action'] === 'signin') {
        $username = trim($_POST["username"]);
        $password = $_POST["password"];

        $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = authenticateUser($pdo, $username, $password);
        if ($user) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["email"] = $user["email"];
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz App</title>
  <link rel="icon" href="../image/icon.png">
  <link rel="stylesheet" href="../css/main.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&family=Potta+One&display=swap" rel="stylesheet">
</head>
<body class="no-scroll">
  <header>
    <a href="dashboard.php"><img src="../image/1c5a6078-b57d-47e9-b234-2022e121fab6.png"></a>
    <a href="dashboard.php"><div>MASHIMASHI COMPANY</div></a>
  </header>

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
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
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
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
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
