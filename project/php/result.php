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
    <link rel="stylesheet" href="../css/main.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <main class="result-page">
        <?php foreach ($results as $index => $row): ?>
            <div class="question">
                <div class="result" onclick="toggleAccordion(this)">
                    QUESTION<?= $index + 1 ?> <span class="arrow">▼</span>
                    <?= ($row['index_number'] == $row['answer_index']) ? '○' : '×' ?>
                </div>
                <div class="problem-statement">
                    <?= htmlspecialchars($row['content']) ?><br>
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
    <script src="../js/acoordion.js"></script>
</body>

</html>