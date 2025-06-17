<?php
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // JSONを受け取る
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['quizId']) || !isset($input['choiceIndex'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $quizId = (int)$input['quizId'];
    $choiceIndex = (int)$input['choiceIndex'];
    $user_id = 1; // 仮ユーザーID（本来はセッション等から）

    // 選択肢IDを取得（quiz_id と index_number で特定）
    $stmt = $pdo->prepare("SELECT id FROM choices WHERE quiz_id = :quiz_id AND index_number = :index_number");
    $stmt->execute([':quiz_id' => $quizId, ':index_number' => $choiceIndex]);
    $choice = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$choice) {
        http_response_code(400);
        echo json_encode(['error' => 'Choice not found']);
        exit;
    }
    $choice_id = $choice['id'];

    // 既にそのクイズに回答済みかどうかをチェック（choicesテーブル経由）
    $stmt = $pdo->prepare("SELECT p.id FROM progress p 
                            JOIN choices c ON c.id = p.choice_id
                            WHERE p.user_id = :user_id AND c.quiz_id = :quiz_id");
    $stmt->execute([':user_id' => $user_id, ':quiz_id' => $quizId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // 回答済みなら更新
        $stmt = $pdo->prepare("UPDATE progress SET choice_id = :choice_id, answered_at = NOW() WHERE id = :progress_id");
        $stmt->execute([':choice_id' => $choice_id, ':progress_id' => $existing['id']]);
    } else {
        // 未回答なら新規に挿入
        $stmt = $pdo->prepare("INSERT INTO progress (user_id, choice_id) VALUES (:user_id, :choice_id)");
        $stmt->execute([':user_id' => $user_id, ':choice_id' => $choice_id]);
    }

    echo json_encode(['success' => true]);
    exit;
}

// --- GETの場合：クイズデータ取得処理 ---

// 仮ユーザーIDを固定（user_id = 1）※ 本番ではセッション管理等を実装してください
$user_id = 1;

// タグ取得
if (isset($_GET['tag'])) {
    $quiz_tag = $_GET['tag'];
} else {
    header("Location: dashboard.php");
    exit;
}

// クイズと選択肢の取得（タグに沿った並び順で）
$stmt_quiz = $pdo->prepare("SELECT * FROM quizzes WHERE tag = :tag ORDER BY id");
$stmt_quiz->execute([':tag' => $quiz_tag]);
$quizzes = $stmt_quiz->fetchAll(PDO::FETCH_ASSOC);

$quiz_ids = array_column($quizzes, 'id');
if (count($quiz_ids) === 0) {
    header("Location: dashboard.php");
    exit;
}

$in_clause = implode(',', array_fill(0, count($quiz_ids), '?'));
$stmt_choice = $pdo->prepare("SELECT * FROM choices WHERE quiz_id IN ($in_clause) ORDER BY quiz_id, index_number");
$stmt_choice->execute($quiz_ids);
$choices = $stmt_choice->fetchAll(PDO::FETCH_ASSOC);

// 組み立て（各問題に対して選択肢を連想配列で追加）
$quiz_data = [];
foreach ($quizzes as $quiz) {
    $quiz_data[$quiz['id']] = [
        'id' => $quiz['id'],  // 問題IDとして渡す
        'content' => $quiz['content'],
        'choices' => []
    ];
}
foreach ($choices as $choice) {
    $quiz_data[$choice['quiz_id']]['choices'][] = [
        'index_number' => $choice['index_number'],
        'choice_text' => $choice['choice_text']
    ];
}

$quiz_data_json = json_encode(array_values($quiz_data), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard - <?php echo htmlspecialchars($quiz_tag); ?></title>
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="status-bar">
            <div class="level">0/20</div>
        </div>
        <div class="question">
            <div class="button">
                <div class="q"></div>
            </div>
            <div class="problem-statement open" id="problem-statement"></div>
        </div>

        <div class="options" id="options"></div>

        <div class="next-button-container">
            <a class="fixed-button next-button" onclick="checkAndGo()">NEXT</a>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="../js/sidebar.js"></script>
    <script src="../js/mordol.js"></script>
    <script src="../js/button.js"></script>
    <script>
      // クイズデータ、保存先URL、タグをJSに渡す
      window.quizData = <?= $quiz_data_json ?>;
      window.quizSaveUrl = '<?= basename(__FILE__) ?>';
      window.quizTag = '<?= htmlspecialchars($quiz_tag); ?>';
    </script>
    <script src="../js/main.js"></script>
</body>
</html>
