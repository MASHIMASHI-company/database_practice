<?php
// dashboard.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
    require_once 'auth.php';
    login_required();

require_once __DIR__ . '/db_connect.php';

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
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="dashboard">
        <!-- DBから取得した進捗結果を表示 -->
        <div class="status-bar">
            <div class="level"><?php echo htmlspecialchars($levelText, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div class="buttons">
            <div class="button dashboard-card">
                <a href="question.php?tag=HTML">
                    <img src="../image/dog-html.jpg" alt="html" class="logo-image">
                    <div class="card-title">HTML</div>
                    <div class="card-description">Webページの「土台」を作る言語。見出しや文章、画像などを並べて、ページの中身を決めます。いわばWebサイトの設計図です。</div>
                </a>
            </div>
            <div class="button dashboard-card">
                <a href="question.php?tag=CSS">
                <img src="../image/rakko-css.jpg" alt="css" class="logo-image">
                    <div class="card-title">CSS</div>
                    <div class="card-description">HTMLで作ったページを「オシャレ」にする言語。色や文字サイズ、配置などを変えて、見やすく整えます。服を着せるようなイメージです。</div>

                </a>
            </div>
            <div class="button dashboard-card">
                <a href="question.php?tag=JavaScript">
                <img src="../image/howel-js.jpg" alt="js" class="logo-image">
                    <div class="card-title">JavaScript</div>
                    <div class="card-description">ボタンを押したら動く、表示が変わるなど、ページに「動き」や「反応」をつける言語。Webページをもっと便利にしてくれます。</div>

                </a>
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
