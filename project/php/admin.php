<?php
// ────────────────────────────────────────────────────
// ① DB接続（.env.php で $db_host,$db_name,$db_user,$db_pass 定義）
require_once __DIR__.'/.env.php';
try {
  $pdo = new PDO(
    "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
    $db_user, $db_pass,
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
  );
} catch (PDOException $e) {
  die("DB接続エラー: ".$e->getMessage());
}

// ────────────────────────────────────────────────────
// ② Ajax処理：?action=add|update|delete
if (isset($_GET['action'])) {
  header('Content-Type: application/json');
  $in = json_decode(file_get_contents('php://input'), true);
  try {
    // Create
    if ($_GET['action'] === 'add') {
      $stmt = $pdo->prepare(
        "INSERT INTO quizzes (title,content,answer_index,tag)
         VALUES (:t,:c,0,:g)"
      );
      $stmt->execute([
        ':t'=>$in['title'],
        ':c'=>$in['description'],
        ':g'=>ucfirst($in['category'])
      ]);
      $qid = $pdo->lastInsertId();
      $ins = $pdo->prepare(
        "INSERT INTO choices (quiz_id,choice_text,index_number)
         VALUES (:q,:txt,:idx)"
      );
      foreach ($in['answers'] as $i=>$txt) {
        $ins->execute([':q'=>$qid,':txt'=>$txt,':idx'=>$i]);
      }
      echo json_encode(['success'=>true,'id'=>$qid]);
      exit;
    }
    // Update
    if ($_GET['action'] === 'update') {
      $qid = (int)$in['id'];
      $pdo->prepare(
        "UPDATE quizzes SET title=?,content=?,tag=? WHERE id=?"
      )->execute([
        $in['title'],
        $in['description'],
        ucfirst($in['category']),
        $qid
      ]);
      $pdo->prepare("DELETE FROM choices WHERE quiz_id=?")
          ->execute([$qid]);
      $ins = $pdo->prepare(
        "INSERT INTO choices (quiz_id,choice_text,index_number)
         VALUES (:q,:txt,:idx)"
      );
      foreach ($in['answers'] as $i=>$txt) {
        $ins->execute([':q'=>$qid,':txt'=>$txt,':idx'=>$i]);
      }
      echo json_encode(['success'=>true]);
      exit;
    }
    // Delete
    if ($_GET['action'] === 'delete') {
      $pdo->prepare("DELETE FROM quizzes WHERE id=?")
          ->execute([(int)$in['id']]);
      echo json_encode(['success'=>true]);
      exit;
    }
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
    exit;
  }
}

// ────────────────────────────────────────────────────
// ③ 通常表示：Read → グルーピング
$sql = "
  SELECT q.id AS qid, q.title, q.content, q.tag,
         c.choice_text, c.index_number
    FROM quizzes q
    LEFT JOIN choices c ON c.quiz_id=q.id
   ORDER BY q.tag, q.id, c.index_number
";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$groups = [];
foreach ($rows as $r) {
  $t = strtolower($r['tag']);
  if (!isset($groups[$t][$r['qid']])) {
    $groups[$t][$r['qid']] = [
      'id'=>$r['qid'],
      'title'=>$r['title'],
      'content'=>$r['content'],
      'choices'=>[]
    ];
  }
  if ($r['choice_text'] !== null) {
    $groups[$t][$r['qid']]['choices'][] = $r['choice_text'];
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>dashboard</title>
  <link rel="stylesheet" href="../css/main.css">
  <style>
    /* ドラッグハンドル疑似要素 */
    .aco-item .aco-title {
      position: relative;
      padding-left: 1.2em;
      cursor: grab;
      user-select: none;
    }
    .aco-item .aco-title:before {
      content: "☰";
      position: absolute;
      left: 0; top:50%;
      transform: translateY(-50%);
    }
    .aco-item.dragging .aco-title {
      cursor: grabbing; opacity:0.6;
    }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <main class="admin">
    <div class="add">
      <div>ADD</div>
      <form id="add-form" action="#">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required><br>
        <label for="description">Description:</label>
        <input type="text" name="description" id="description" required><br>
        <label for="category">Category:</label>
        <select name="category" id="category" required>
          <option value="">--</option>
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

  <!-- 編集モーダル -->
<div id="editOverlay">
  <div class="edit-modal">
    <button class="custom-close-btn">✖</button>
    <form id="custom-edit-form">
      <h2>Edit Quiz</h2>
      <label>Title:</label><br>
      <input type="text" name="title" id="edit-title" required><br><br>

      <label>Description:</label><br>
      <input type="text" name="description" id="edit-description" required><br><br>

      <label>Category:</label><br>
      <select name="category" id="edit-category" required>
        <option value="html">HTML</option>
        <option value="css">CSS</option>
        <option value="javascript">JavaScript</option>
      </select><br><br>

      <div class="answer">
        <div>Answers:</div>
        <div id="edit-answer-fields"></div>
      </div><br>

      <div class="submit modal-update" >
        <button type="submit">UPDATE</button>
      </div>
    </form>
  </div>
</div>


  <div class="admin-aco">
    <!-- HTML -->
    <div class="toggle" data-target="html-aco">
      <span class="arrow">▶</span> HTML
    </div>
    <div class="aco-content html-aco">
      <?php foreach ($groups['html'] ?? [] as $q): ?>
      <div class="aco-item" data-id="<?= $q['id'] ?>">
        <div class="aco-title"><?= htmlspecialchars($q['title']) ?></div>
        <div class="aco-description"><?= htmlspecialchars($q['content']) ?></div>
        <ul class="aco-answers">
          <?php foreach ($q['choices'] as $c): ?>
            <li><?= htmlspecialchars($c) ?></li>
          <?php endforeach; ?>
        </ul>
        <div class="aco-actions">
          <button class="edit-btn">Edit</button>
          <button class="delete-btn">Delete</button>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="aco-actions"></div>
    </div>

    <!-- CSS -->
    <div class="toggle" data-target="css-aco">
      <span class="arrow">▶</span> CSS
    </div>
    <div class="aco-content css-aco">
      <?php foreach ($groups['css'] ?? [] as $q): ?>
      <div class="aco-item" data-id="<?= $q['id'] ?>">
        <div class="aco-title"><?= htmlspecialchars($q['title']) ?></div>
        <div class="aco-description"><?= htmlspecialchars($q['content']) ?></div>
        <ul class="aco-answers">
          <?php foreach ($q['choices'] as $c): ?>
            <li><?= htmlspecialchars($c) ?></li>
          <?php endforeach; ?>
        </ul>
        <div class="aco-actions">
          <button class="edit-btn">Edit</button>
          <button class="delete-btn">Delete</button>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="aco-actions"></div>
    </div>

    <!-- JavaScript -->
    <div class="toggle" data-target="js-aco">
      <span class="arrow">▶</span> JavaScript
    </div>
    <div class="aco-content js-aco">
      <?php foreach ($groups['javascript'] ?? [] as $q): ?>
      <div class="aco-item" data-id="<?= $q['id'] ?>">
        <div class="aco-title"><?= htmlspecialchars($q['title']) ?></div>
        <div class="aco-description"><?= htmlspecialchars($q['content']) ?></div>
        <ul class="aco-answers">
          <?php foreach ($q['choices'] as $c): ?>
            <li><?= htmlspecialchars($c) ?></li>
          <?php endforeach; ?>
        </ul>
        <div class="aco-actions">
          <button class="edit-btn">Edit</button>
          <button class="delete-btn">Delete</button>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="aco-actions"></div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <!-- SortableJS -->
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <!-- 完全版JS：貼り付けるだけで動作 -->
  <script>
document.addEventListener("DOMContentLoaded", () => {
  let currentEditItem = null;
  const api = location.pathname + "?action=";

  // ■ A: アコーディオン
  document.body.addEventListener("click", e => {
    const tog = e.target.closest(".toggle");
    if (!tog) return;
    const pan = document.querySelector(`.${tog.dataset.target}`);
    pan?.classList.toggle("open");
    tog.classList.toggle("active");
  });

  // ■ B: ADD フォーム／Answer 行操作
  const addForm = document.getElementById("add-form");
  const ansWrap = document.getElementById("answer-form");
  if (addForm && ansWrap) {
    ansWrap.addEventListener("click", e => {
      if (e.target.matches(".remove-btn")) {
        const flds = ansWrap.querySelectorAll(".answer-field");
        if (flds.length <= 2) return alert("最低2つ必要です");
        e.target.closest(".answer-field").remove();
        ansWrap.querySelectorAll(".answer-field").forEach((f,i)=>{
          f.querySelector("label").textContent = `Answer.${i+1}:`;
        });
      }
      if (e.target.matches("#add-answer-btn")) {
        const cnt = ansWrap.querySelectorAll(".answer-field").length + 1;
        const div = document.createElement("div");
        div.className = "answer-field";
        div.innerHTML = `
          <label>Answer.${cnt}:</label>
          <input type="text" name="add-answer[]" required>
          <button type="button" class="remove-btn">削除</button>`;
        ansWrap.insertBefore(div, e.target);
      }
    });

    addForm.addEventListener("submit", async e => {
      e.preventDefault();
      const fd       = new FormData(addForm);
      const title    = fd.get("title")?.trim();
      const description = fd.get("description")?.trim();
      const category = fd.get("category");
      const answers  = fd.getAll("add-answer[]").map(v=>v.trim());
      if (!title||!description||!category)
        return alert("全て入力してください");
      const res = await fetch(api+"add", {
        method:"POST",
        body: JSON.stringify({ title, description, category, answers })
      });
      const j = await res.json();
      if (j.success) return location.reload();
      alert(j.error||"追加失敗");
    });
  }

  // ■ C: EDIT モーダルの Answer 行操作
  const editOverlay = document.getElementById("editOverlay");
  const editForm    = document.getElementById("custom-edit-form");
  const editWrap    = document.getElementById("edit-answer-fields");
  if (editWrap) {
    // ＋追加ボタン
    const btn = document.createElement("button");
    btn.type        = "button";
    btn.textContent = "＋追加";
    btn.classList.add("edit-add-btn");
    editWrap.parentNode.insertBefore(btn, editWrap.nextSibling);

    document.body.addEventListener("click", e => {
      if (e.target.matches(".edit-add-btn")) {
        const cnt = editWrap.querySelectorAll(".answer-field").length + 1;
        const div = document.createElement("div");
        div.className = "answer-field";
        div.innerHTML = `
          <label>Answer.${cnt}:</label>
          <input type="text" name="edit-answer[]" required>
          <button type="button" class="remove-btn">削除</button>`;
        editWrap.appendChild(div);
      }
      if (e.target.matches("#edit-answer-fields .remove-btn")) {
        const flds = editWrap.querySelectorAll(".answer-field");
        if (flds.length <= 2) return alert("最低2つ必要です");
        e.target.closest(".answer-field").remove();
        editWrap.querySelectorAll(".answer-field").forEach((f,i)=>{
          f.querySelector("label").textContent = `Answer.${i+1}:`;
        });
      }
    });
  }
  // モーダル閉じる
  document.querySelector(".custom-close-btn")
    ?.addEventListener("click", () => {
      editOverlay.style.display = "none";
      currentEditItem = null;
    });

  // ■ D: Delete / Edit 開く
  document.body.addEventListener("click", e => {
    // Delete
    const del = e.target.closest(".delete-btn");
    if (del) {
      if (!confirm("削除しますか？")) return;
      const item = del.closest(".aco-item");
      fetch(api+"delete", {
        method:"POST",
        body: JSON.stringify({ id: item.dataset.id })
      })
      .then(r=>r.json()).then(j=>{
        if (j.success) item.remove();
        else alert(j.error||"削除失敗");
      });
      return;
    }

    // Edit 開く
    const ed = e.target.closest(".edit-btn");
    if (ed && editForm && editWrap) {
      const item = ed.closest(".aco-item");
      currentEditItem = item;
      // Title / Description
      document.getElementById("edit-title").value =
        item.querySelector(".aco-title").textContent;
      document.getElementById("edit-description").value =
        item.querySelector(".aco-description").textContent;
      // Category
      document.getElementById("edit-category").value =
        ["html","css","javascript"].find(v=>
          item.closest(".aco-content").classList.contains(v+"-aco")
        );
      // Choices
      editWrap.innerHTML = "";
      item.querySelectorAll(".aco-answers li").forEach((li,i)=>{
        const div = document.createElement("div");
        div.className = "answer-field";
        div.innerHTML = `
          <label>Answer.${i+1}:</label>
          <input type="text" name="edit-answer[]" value="${li.textContent}" required>
          <button type="button" class="remove-btn">削除</button>`;
        editWrap.appendChild(div);
      });
      editOverlay.style.display = "flex";
    }
  });

  // ■ E: モーダル保存 → DB更新 + DOM更新
  if (editForm) {
    editForm.addEventListener("submit", async e => {
      e.preventDefault();
      if (!currentEditItem) return;
      const fd = new FormData(editForm);
      const payload = {
        id: currentEditItem.dataset.id,
        title:       fd.get("title").trim(),
        description: fd.get("description").trim(),
        category:    fd.get("category"),
        answers:     fd.getAll("edit-answer[]").map(v=>v.trim())
      };
      const res = await fetch(api+"update", {
        method:"POST",
        body: JSON.stringify(payload)
      });
      const j = await res.json();
      if (!j.success) return alert(j.error||"更新失敗");
      // DOMを更新
      currentEditItem.querySelector(".aco-title").textContent = payload.title;
      currentEditItem.querySelector(".aco-description").textContent = payload.description;
      currentEditItem.querySelector(".aco-answers").innerHTML =
        payload.answers.map((a,idx) =>
          `<li${idx===payload.answer_index?' class="correct"':''}>${a}</li>`
        ).join("");
      // セクション移動（もしカテゴリ変わってたら）
      const oldP = currentEditItem.closest(".aco-content");
      const newP = document.querySelector(`.${payload.category}-aco`);
      if (oldP && newP && oldP!==newP) newP.appendChild(currentEditItem);
      editOverlay.style.display = "none";
      currentEditItem = null;
    });
  }

  // ■ F: Sortable (ハンドル .aco-title)
  document.querySelectorAll(".aco-content").forEach(container => {
    new Sortable(container, {
      animation:150,
      draggable:".aco-item",
      handle:".aco-title",
      onStart: e=>e.item.classList.add("dragging"),
      onEnd:   e=>e.item.classList.remove("dragging")
    });
  });
});
</script>

</body>
</html>
