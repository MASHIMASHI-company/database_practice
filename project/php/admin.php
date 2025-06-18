<?php
// index.php

// ────────────────────────────────────────────────────
// ① DB接続（.env.php で $db_host,$db_name,$db_user,$db_pass 定義）
require_once __DIR__ . '/.env.php';
try {
  $pdo = new PDO(
    "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
    $db_user, $db_pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
} catch (PDOException $e) {
  die("DB接続エラー: " . $e->getMessage());
}

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

      // quizzes 更新に answer_index を追加
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
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>Quiz 管理画面</title>
  <link rel="icon" href="../image/icon.png">
  <link rel="stylesheet" href="project/css/main.css">
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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
      </select><br><br>
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

<!-- 質問一覧 -->
<div class="admin-aco">
  <?php foreach (['HTML'=>'HTML','CSS'=>'CSS','JavaScript'=>'JavaScript'] as $key=>$label): ?>
    <div class="toggle" data-target="<?= $key ?>-aco">
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

<script>
document.addEventListener("DOMContentLoaded", () => {
  let currentEditItem = null;
  const api = location.pathname + "?action=";

  // ■ A: アコーディオン
  document.body.addEventListener("click", e => {
    const tog = e.target.closest(".toggle");
    if (!tog) return;
    document.querySelector(`.${tog.dataset.target}`)?.classList.toggle("open");
    tog.classList.toggle("active");
  });

  // ■ B: ADDフォーム／Answer＋正解ラジオ操作
  const addForm = document.getElementById("add-form");
  const ansWrap = document.getElementById("answer-form");
  function attachAddRadio(field, idx) {
    const r = document.createElement("input");
    r.type="radio"; r.name="add-correct"; r.value=idx; r.required=true;
    const lbl = document.createElement("label");
    lbl.className="correct-label"; lbl.textContent=""; lbl.prepend(r);
    field.prepend(lbl);
  }
  if (addForm && ansWrap) {
    ansWrap.querySelectorAll(".answer-field").forEach((f,i)=>attachAddRadio(f,i));
    ansWrap.addEventListener("click", e => {
      if (e.target.matches(".remove-btn")) {
        const flds=ansWrap.querySelectorAll(".answer-field");
        if (flds.length<=2) return alert("最低2つ必要です");
        e.target.closest(".answer-field").remove();
        ansWrap.querySelectorAll(".answer-field").forEach((f,i)=>{
          f.querySelector("label:not(.correct-label)").textContent=`Answer.${i+1}:`;
          const rr=f.querySelector("input[type=radio]"); if(rr) rr.value=i;
        });
      }
      if (e.target.matches("#add-answer-btn")) {
        const cnt=ansWrap.querySelectorAll(".answer-field").length+1;
        const div=document.createElement("div"); div.className="answer-field";
        div.innerHTML=`
          <label>Answer.${cnt}:</label>
          <input type="text" name="add-answer[]" required>
          <button type="button" class="remove-btn">削除</button>`;
        ansWrap.insertBefore(div,e.target); attachAddRadio(div,cnt-1);
      }
    });
    addForm.addEventListener("submit", async e=> {
      e.preventDefault();
      const fd=new FormData(addForm),
            title=fd.get("title")?.trim(),
            description=fd.get("description")?.trim(),
            category=fd.get("category"),
            answers=fd.getAll("add-answer[]").map(v=>v.trim()),
            correctIndex=parseInt(fd.get("add-correct"),10);
      if(!title||!description||!category) return alert("全て入力してください");
      const res=await fetch(api+"add",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body:JSON.stringify({title,description,category,answers,correctIndex})
      }), j=await res.json();
      if(j.success) return location.reload(); alert(j.error||"追加失敗");
    });
  }

  // ■ C: EDITモーダル／Answer＋正解ラジオ操作
  const editOverlay=document.getElementById("editOverlay"),
        editForm=document.getElementById("custom-edit-form"),
        editWrap=document.getElementById("edit-answer-fields");
  function attachEditRadio(field,idx,chk=false){
    const r=document.createElement("input");
    r.type="radio"; r.name="edit-correct"; r.value=idx; r.required=true;
    if(chk) r.checked=true;
    const lbl=document.createElement("label");
    lbl.className="correct-label"; lbl.textContent=""; lbl.prepend(r);
    field.prepend(lbl);
  }
  if(editWrap){
    const btn=document.createElement("button");
    btn.type="button"; btn.textContent="＋追加"; btn.classList.add("edit-add-btn");
    editWrap.parentNode.insertBefore(btn,editWrap.nextSibling);
    document.body.addEventListener("click",e=>{
      if(e.target.matches("#edit-answer-fields .remove-btn")){
        const flds=editWrap.querySelectorAll(".answer-field");
        if(flds.length<=2) return alert("最低2つ必要です");
        e.target.closest(".answer-field").remove();
        editWrap.querySelectorAll(".answer-field").forEach((f,i)=>{
          f.querySelector("label:not(.correct-label)").textContent=`Answer.${i+1}:`;
          const rr=f.querySelector("input[type=radio]"); if(rr) rr.value=i;
        });
      }
      if(e.target.matches(".edit-add-btn")){
        const cnt=editWrap.querySelectorAll(".answer-field").length+1;
        const div=document.createElement("div"); div.className="answer-field";
        div.innerHTML=`
          <label>Answer.${cnt}:</label>
          <input type="text" name="edit-answer[]" required>
          <button type="button" class="remove-btn">削除</button>`;
        editWrap.appendChild(div); attachEditRadio(div,cnt-1);
      }
    });
    editOverlay.querySelector(".custom-close-btn")
      .addEventListener("click",()=>{
        editOverlay.style.display="none"; currentEditItem=null;
      });
  }

  // ■ D: Delete / Edit 開く
  document.body.addEventListener("click",e=>{
    // Delete
    const del=e.target.closest(".delete-btn");
    if(del){
      if(!confirm("削除しますか？"))return;
      const item=del.closest(".aco-item");
      fetch(api+"delete",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body:JSON.stringify({id:item.dataset.id})
      }).then(r=>r.json()).then(j=>{
        if(j.success) item.remove(); else alert(j.error||"削除失敗");
      });
      return;
    }
    // Edit 開く
    const ed=e.target.closest(".edit-btn");
    if(ed&&editForm&&editWrap){
      const item=ed.closest(".aco-item"); currentEditItem=item;
      // Title/Description/Category
      editForm.querySelector("#edit-title").value=
        item.querySelector(".aco-title").textContent;
      editForm.querySelector("#edit-description").value=
        item.querySelector(".aco-description").textContent;
      editForm.querySelector("#edit-category").value=
        ["HTML","CSS","JavaScript"].find(v=>
          item.closest(`.${v}-aco`)
        );
      // 既存Answersと correctIndex 取得
      const lis=Array.from(item.querySelectorAll(".aco-answers li")),
            correctIdx=lis.findIndex(li=>li.classList.contains("correct"));
      editWrap.innerHTML="";
      lis.forEach((li, i) => {
        const div = document.createElement("div");
        div.className = "answer-field";

        // 1) テキストは trim() して前後の空白除去
        const text = li.textContent.trim();

        // 2) 先頭の改行・空白を消した一行文字列で innerHTML に渡す
        div.innerHTML = 
          `<label>Answer.${i+1}:</label>` +
          `<input type="text" name="edit-answer[]" value="${text}" required>` +
          `<button type="button" class="remove-btn">削除</button>`;

        editWrap.appendChild(div);
        attachEditRadio(div, i, i === correctIdx);
      });
      editOverlay.style.display="flex";
    }
  });

  // ■ E: EDIT保存 → DB+DOM更新
  if(editForm){
    editForm.addEventListener("submit",async e=>{
      e.preventDefault(); if(!currentEditItem)return;
      const fd=new FormData(editForm),
            payload={
              id:currentEditItem.dataset.id,
              title:fd.get("title").trim(),
              description:fd.get("description").trim(),
              category:fd.get("category"),
              answers:fd.getAll("edit-answer[]").map(v=>v.trim()),
              correctIndex:parseInt(fd.get("edit-correct"),10)
            },
            res=await fetch(api+"update",{
              method:"POST",
              headers:{"Content-Type":"application/json"},
              body:JSON.stringify(payload)
            }), j=await res.json();
      if(!j.success)return alert(j.error||"更新失敗");
      // DOM更新
      currentEditItem.querySelector(".aco-title").textContent=payload.title;
      currentEditItem.querySelector(".aco-description").textContent=payload.description;
      const ul=currentEditItem.querySelector(".aco-answers");
      ul.innerHTML=payload.answers.map((a,i)=>
        `<li${i===payload.correctIndex?' class="correct"':''}>${a}</li>`
      ).join("");
      const oldP=currentEditItem.closest(".aco-content"),
            newP=document.querySelector(`.${payload.category}-aco`);
      if(oldP&&newP&&oldP!==newP)newP.appendChild(currentEditItem);
      editOverlay.style.display="none"; currentEditItem=null;
    });
  }

  // ■ F: Sortable（既存そのまま）
  document.querySelectorAll(".aco-content").forEach(container=>{
    new Sortable(container,{
      animation:150, draggable:".aco-item",
      handle:".aco-title",
      onStart:e=>e.item.classList.add("dragging"),
      onEnd:e=>e.item.classList.remove("dragging")
    });
  });
});
</script>
</body>
</html>
