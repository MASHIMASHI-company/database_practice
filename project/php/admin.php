<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>dashboard</title>
    <link rel="stylesheet" href="../css/main.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <main class="admin">
    <div class="add">
  <div>ADD</div>
  <form id="add-form" action="#">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required><br>

    <label for="description">Description:</label>
    <input type="text" id="description" name="description" required><br>

    <label for="category">Category:</label>
    <select id="category" name="category" required>
      <option value="">-- Select a category --</option>
      <option value="html">HTML</option>
      <option value="css">CSS</option>
      <option value="javascript">JavaScript</option>
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

    <div class="submit">
      <button type="submit">ADD</button>
    </div>
  </form>
</div>

    </main>
    <div class="admin-aco">
    <div class="toggle" data-target="html-aco">
        <span class="arrow">▶</span> HTML
    </div>
    <div class="aco-content html-aco">

    <div class="aco-actions">

    </div>
    </div>

    <div class="toggle" data-target="css-aco">
        <span class="arrow">▶</span> CSS
    </div>
    <div class="aco-content css-aco">

        <div class="aco-actions">

        </div>
    </div>

    <div class="toggle" data-target="js-aco">
        <span class="arrow">▶</span> JavaScript
    </div>
    <div class="aco-content html-aco">
  <div class="aco-item">
    <!-- ここがドラッグハンドル -->
    <span class="drag-handle">☰</span>

    <!-- 既存のタイトル／説明／回答 -->
    <div class="aco-title">既存のHTML問題タイトル</div>
    <div class="aco-description">初期説明文</div>
    <ul class="aco-answers">
      <li>回答1</li>
      <li>回答2</li>
    </ul>

    <!-- 編集＋削除ボタン -->
    <div class="aco-actions">
      <button class="edit-btn">Edit</button>
      <button class="delete-btn">Delete</button>
    </div>
  </div>
  <!-- （複数あれば同じ構造で続ける） -->
</div>

    </div>

    <!-- 編集モーダル -->
<div class="custom-overlay" id="editOverlay" style="display: none;">
  <div class="custom-modal">
    <button class="custom-close-btn">&times;</button>
    <h2>編集</h2>
    <form id="custom-edit-form">
      <label for="edit-title">Title:</label>
      <input type="text" id="edit-title" name="title" required><br>

      <label for="edit-description">Description:</label>
      <input type="text" id="edit-description" name="description" required><br>

      <label for="edit-category">Category:</label>
      <select id="edit-category" name="category" required>
        <option value="">-- Select a category --</option>
        <option value="html">HTML</option>
        <option value="css">CSS</option>
        <option value="javascript">JavaScript</option>
      </select><br>

      <div id="edit-answer-fields">
  <div class="answer-field">
    <label>Answer.1:</label>
    <input type="text" name="edit-answer[]" value="" required>
    <button type="button" class="remove-btn">削除</button>
  </div>
  <!-- 以下追加されていく -->
</div>

      <button class="submit-modal" type="submit">保存</button>
    </form>
  </div>
</div>



    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="../js/aco.js"></script>
    <script src="../js/edit-delete.js"></script>
</body>

</html>