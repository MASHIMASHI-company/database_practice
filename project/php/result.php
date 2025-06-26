<?php
require_once __DIR__ . '/db_connect.php';

require_once 'auth.php';
login_required(); // セッションチェック
$user_id = $_SESSION["user_id"];
$tag = $_GET['tag'] ?? null;
if (!$tag) {
    header("Location: dashboard.php");
    exit;
}

// そのタグの問題数を取得
$stmt = $pdo->prepare("SELECT COUNT(*) FROM quizzes WHERE tag = ?");
$stmt->execute([$tag]);
$totalQuestions = (int)$stmt->fetchColumn();

// 最新の progress からその件数分取得
$stmt = $pdo->prepare("
    SELECT 
        p.id AS progress_id,
        q.id AS quiz_id,
        q.title,
        q.content,
        q.answer_index,
        c.choice_text,
        c.index_number
    FROM progress p
    JOIN choices c ON p.choice_id = c.id
    JOIN quizzes q ON c.quiz_id = q.id
    WHERE p.user_id = ? AND q.tag = ?
    ORDER BY p.id DESC
    LIMIT ?
");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $tag, PDO::PARAM_STR);
$stmt->bindValue(3, (int)$totalQuestions, PDO::PARAM_INT);
$stmt->execute();
$results = array_reverse($stmt->fetchAll());
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>dashboard</title>
    <link rel="icon" href="../image/icon.png">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/prism-okaidia.css">
    <script src="https://cdn.jsdelivr.net/npm/prismjs/prism.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs/components/prism-markup.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs/components/prism-css.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs/components/prism-javascript.min.js"></script>
</head>

<body>
    <?php include 'header.php'; ?>
    <main class="result-page">
        <?php foreach ($results as $index => $row): ?>
            <div class="question toggle">
                <div class="result" onclick="toggleAccordion(this)">
                    <span class="arrow">▶︎</span>
                    <span class="text">
                        <span class="result-gap"><?= ($row['index_number'] == $row['answer_index']) ? '○' : '×' ?></span><?= htmlspecialchars($row['title']) ?>
                    </span>
                </div>
                <div class="result-statement">
                    <?= $row['content'] ?>
                    <?php if (stripos($row['content'], '<pre') === false): ?>
                        <br>
                    <?php endif; ?>
                    <?= ($row['index_number'] == $row['answer_index']) ? '○' : '×' ?>
                    <?= htmlspecialchars($row['choice_text']) ?><br>
                </div>
            </div>
        <?php endforeach; ?>
    </main>
    <?php include 'footer.php'; ?>
    <script src="../js/sidebar.js"></script>
    <script src="../js/mordol.js"></script>
    <script src="../js/button.js"></script>
    <script src="../js/accordion.js"></script>
</body>

</html>