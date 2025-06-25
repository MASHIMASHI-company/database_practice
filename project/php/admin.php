<?php
require_once __DIR__ . '/db_connect.php';

// ────────────────────────────────────────────────────
// ② Ajax処理：?action=add|update|delete
if (isset($_GET['action'])) {
  header('Content-Type: application/json; charset=utf-8');
  $in = json_decode(file_get_contents('php://input'), true);
  try {
    // --- Create ---
    if ($_GET['action'] === 'add') {
      $t            = trim($in['title'] ?? '');
      $c            = trim($in['description'] ?? '');
      $tag          = ucfirst($in['category'] ?? '');
      $answers      = $in['answers'] ?? [];
      $correctIndex = intval($in['correctIndex'] ?? -1);

      if (!$t || !$c || !$tag || count($answers) < 2 || $correctIndex < 0) {
        throw new Exception('入力を確認してください');
      }

      // quizzes に answer_index も含めて INSERT
      $stmt = $pdo->prepare(
        "INSERT INTO quizzes (title,content,answer_index,tag)
         VALUES (:t,:c,:ai,:g)"
      );
      $stmt->execute([
        ':t'  => $t,
        ':c'  => $c,
        ':ai' => $correctIndex,
        ':g'  => $tag
      ]);
      $qid = $pdo->lastInsertId();

      // choices
      $ins = $pdo->prepare(
        "INSERT INTO choices (quiz_id,choice_text,index_number)
         VALUES (:q,:txt,:idx)"
      );
      foreach ($answers as $i => $txt) {
        $ins->execute([
          ':q'   => $qid,
          ':txt' => trim($txt),
          ':idx' => $i
        ]);
      }

      echo json_encode(['success' => true]);
      exit;
    }

    // --- Update ---
    if ($_GET['action'] === 'update') {
      $qid          = intval($in['id'] ?? 0);
      $t            = trim($in['title'] ?? '');
      $c            = trim($in['description'] ?? '');
      $tag          = ucfirst($in['category'] ?? '');
      $answers      = $in['answers'] ?? [];
      $correctIndex = intval($in['correctIndex'] ?? -1);

      if (!$qid || !$t || !$c || !$tag || count($answers) < 2 || $correctIndex < 0) {
        throw new Exception('入力を確認してください');
      }

      // quizzes 更新
      $pdo->prepare(
        "UPDATE quizzes
           SET title        = ?,
               content      = ?,
               answer_index = ?,
               tag          = ?,
               updated_at   = NOW()
         WHERE id = ?"
      )->execute([
        $t, $c, $correctIndex, $tag, $qid
      ]);

      // choices を再構築
      $pdo->prepare("DELETE FROM choices WHERE quiz_id = ?")
          ->execute([$qid]);
      $ins = $pdo->prepare(
        "INSERT INTO choices (quiz_id,choice_text,index_number)
         VALUES (:q,:txt,:idx)"
      );
      foreach ($answers as $i => $txt) {
        $ins->execute([
          ':q'   => $qid,
          ':txt' => trim($txt),
          ':idx' => $i
        ]);
      }

      echo json_encode(['success' => true]);
      exit;
    }

    // --- Delete ---
    if ($_GET['action'] === 'delete') {
      $pdo->prepare("DELETE FROM quizzes WHERE id = ?")
          ->execute([intval($in['id'] ?? 0)]);
      echo json_encode(['success' => true]);
      exit;
    }

    throw new Exception('Unknown action');
  } catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
  }
}

// ────────────────────────────────────────────────────
// ③ 通常表示：Read → グルーピング
$sql = "
  SELECT q.id AS qid, q.title, q.content, q.tag, q.answer_index,
         c.choice_text
    FROM quizzes q
    LEFT JOIN choices c ON c.quiz_id = q.id
   ORDER BY q.tag, q.id, c.index_number
";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$groups = ['HTML'=>[], 'CSS'=>[], 'JavaScript'=>[]];
foreach ($rows as $r) {
  $t = $r['tag'];
  if (!isset($groups[$t][$r['qid']])) {
    $groups[$t][$r['qid']] = [
      'id'           => $r['qid'],
      'title'        => $r['title'],
      'content'      => $r['content'],
      'answer_index' => $r['answer_index'],
      'choices'      => []
    ];
  }
  $groups[$t][$r['qid']]['choices'][] = $r['choice_text'];
}
?>

<head>
  <!-- <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script> -->
</head>
<body>
<?php include 'header.php'; ?>

<main class="admin">
  <!-- ADD フォーム -->
  <div class="add">
    <div>ADD</div>
    <form id="add-form" action="#">
      <label>Title:</label>
      <input type="text" name="title" required><br>
      <label>Description:</label>
      <input type="text" name="description" required><br>
      <label>Category:</label>
      <select name="category" required>
        <option value="">--</option>
        <option value="HTML">HTML</option>
        <option value="CSS">CSS</option>
        <option value="JavaScript">JavaScript</option>
      </select>
      <div class="answer">
        <div>ANSWER</div>
        <div id="answer-form">
          <div class="answer-field">
            <label>Answer.1:</label>
            <input type="text" name="add-answer[]" required>
            <button type="button" class="remove-btn">削除</button>
          </div>
          <div class="answer-field">
            <label>Answer.2:</label>
            <input type="text" name="add-answer[]" required>
            <button type="button" class="remove-btn">削除</button>
          </div>
          <button type="button" id="add-answer-btn">＋追加</button>
        </div>
      </div>
      <div class="submit"><button type="submit">ADD</button></div>
    </form>
  </div>
</main>

<!-- 編集モーダル -->
<div id="editOverlay">
  <div class="edit-modal">
    <button class="custom-close-btn">✖</button>
    <form id="custom-edit-form">
      <h2>Edit Quiz</h2>
      <label>Title:</label><br>
      <input type="text" name="title" id="edit-title" required><br><br>
      <label>Description:</label><br>
      <textarea name="description" id="edit-description" rows="4" required></textarea><br><br>
      <label>Category:</label><br>
      <select name="category" id="edit-category" required>
        <option value="HTML">HTML</option>
        <option value="CSS">CSS</option>
        <option value="JavaScript">JavaScript</option>
      </select><br>
      <div class="answer">
        <div>ANSWER</div>
        <div id="edit-answer-fields"></div>
      </div><br>
      <div class="submit modal-update">
        <button type="submit">UPDATE</button>
      </div>
    </form>
  </div>
</div>

<!-- 編集一覧 -->
<div class="admin-aco">
  <?php foreach (['HTML'=>'HTML','CSS'=>'CSS','JavaScript'=>'JavaScript'] as $key=>$label): ?>
    <div class="toggle" data-target="<?= $key ?>-aco" onclick="toggleAccordion(this)">
      <span class="arrow">▶</span> <?= $label ?>
    </div>
    <div class="aco-content <?= $key ?>-aco">
      <?php foreach ($groups[$key] ?? [] as $q): ?>
        <div class="aco-item" data-id="<?= $q['id'] ?>">
          <div class="aco-title"><?= htmlspecialchars($q['title']) ?></div>
          <div class="aco-description"><?= htmlspecialchars($q['content']) ?></div>
          <ul class="aco-answers">
            <?php foreach ($q['choices'] as $i=>$c): ?>
              <li<?= $i===$q['answer_index']?' class="correct"':''?>>
                <?= htmlspecialchars($c) ?>
              </li>
            <?php endforeach ?>
          </ul>
          <div class="aco-actions">
            <button class="edit-btn">Edit</button>
            <button class="delete-btn">Delete</button>
          </div>
        </div>
      <?php endforeach ?>
      <div class="aco-actions"></div>
    </div>
  <?php endforeach ?>
</div>

<footer>
  <div></div>
</footer>

<script src="../js/admin.js"></script>
<script src="../js/accordion.js"></script>
</body>
</html>
