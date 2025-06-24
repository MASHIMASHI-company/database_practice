<?php
// dashboard.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// ログインしていない場合の処理（今回はデモ用にユーザーIDを1に設定）
// ※ 本番環境では適切な認証処理を実装してください。
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

// .env.php の読み込み
require_once __DIR__ . '/.env.php';

// DB接続情報を.env.phpから取得
$host    = $db_host;
$dbname  = $db_name;
$user    = $db_user;
$pass    = $db_pass;
$charset = 'utf8mb4';
$dsn     = "mysql:host={$host};dbname={$dbname};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("データベース接続に失敗しました: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// 全クイズ数を取得
$stmtTotal = $pdo->query("SELECT COUNT(*) AS total FROM quizzes");
$total     = (int)$stmtTotal->fetch()['total'];

// ユーザーの正解数を取得
$stmtCorrect = $pdo->prepare(
    "SELECT COUNT(*) AS correct
     FROM progress p
     JOIN choices c ON p.choice_id = c.id
     JOIN quizzes q ON q.id = c.quiz_id
     WHERE c.index_number = q.answer_index AND p.user_id = ?"
);
$stmtCorrect->execute([$user_id]);
$correct   = (int)$stmtCorrect->fetch()['correct'];

// 表示用テキスト（例："5/30"）
$levelText = "{$correct}/{$total}";

$username = $_SESSION['username'];
$email = $_SESSION['email'];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <link rel="icon" href="../image/icon.png">
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="dashboard">
        <!-- DBから取得した進捗結果を表示 -->
        <div class="status-bar">
            <div class="level"><?php echo htmlspecialchars($levelText, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div class="buttons">
            <div class="button">
                <a href="question.php?tag=HTML">HTML</a>
            </div>
            <div class="button">
                <a href="question.php?tag=CSS">CSS</a>
            </div>
            <div class="button">
                <a href="question.php?tag=JavaScript">JavaScript</a>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="../js/sidebar.js"></script>
    <script src="../js/mordol.js"></script>
    <script src="../js/progressbar.js"></script>
    <script>
        // PHPから取得した正解数と全クイズ数をJavaScriptに受け渡し、updateGauge()で進捗バー更新
        const correct = <?php echo json_encode($correct); ?>;
        const total   = <?php echo json_encode($total); ?>;
        updateGauge(correct, total);
    </script>
</body>
</html>
