document.addEventListener("DOMContentLoaded", () => {
  let currentEditItem = null;

  //——— A. アコーディオン開閉 ———
  document.body.addEventListener("click", (e) => {
    if (!e.target.matches(".toggle")) return;
    const toggle = e.target;
    const target = document.querySelector(`.${toggle.dataset.target}`);
    if (!target) return;
    target.classList.toggle("open");
    toggle.classList.toggle("active");
  });

  //——— B. ADD フォーム内 Answer 行の追加・削除 ———
  const addForm = document.getElementById("add-form");
  const ansWrap = document.getElementById("answer-form");
  const addAnsBtn = document.getElementById("add-answer-btn");
  function updateLabels(container) {
    container.querySelectorAll(".answer-field").forEach((f, i) => {
      f.querySelector("label").textContent = `Answer.${i + 1}:`;
    });
  }
  ansWrap.addEventListener("click", (e) => {
    if (e.target.matches(".remove-btn")) {
      const fields = ansWrap.querySelectorAll(".answer-field");
      if (fields.length <= 2) return alert("Answerは最低2つ必要です。");
      e.target.closest(".answer-field").remove();
      updateLabels(ansWrap);
    }
    if (e.target.matches("#add-answer-btn")) {
      const cnt = ansWrap.querySelectorAll(".answer-field").length + 1;
      const div = document.createElement("div");
      div.className = "answer-field";
      div.innerHTML = `
        <label>Answer.${cnt}:</label>
        <input type="text" name="add-answer[]" required>
        <button type="button" class="remove-btn">削除</button>
      `;
      ansWrap.insertBefore(div, addAnsBtn);
    }
  });

  //——— C. モーダル内 Answer 行の追加・削除 ———
  const editOverlay = document.getElementById("editOverlay");
  const editForm = document.getElementById("custom-edit-form");
  const editWrap = document.getElementById("edit-answer-fields");
  // ＋追加ボタン
  const editAddBtn = document.createElement("button");
  editAddBtn.type = "button";
  editAddBtn.textContent = "＋追加";
  editAddBtn.classList.add("edit-add-btn");
  editWrap.after(editAddBtn);

  document.body.addEventListener("click", (e) => {
    if (e.target.matches("#edit-answer-fields .remove-btn")) {
      const fields = editWrap.querySelectorAll(".answer-field");
      if (fields.length <= 2) return alert("Answerは最低2つ必要です。");
      e.target.closest(".answer-field").remove();
      updateLabels(editWrap);
    }
    if (e.target.matches(".edit-add-btn")) {
      const cnt = editWrap.querySelectorAll(".answer-field").length + 1;
      const div = document.createElement("div");
      div.className = "answer-field";
      div.innerHTML = `
        <label>Answer.${cnt}:</label>
        <input type="text" name="edit-answer[]" required>
        <button type="button" class="remove-btn">削除</button>
      `;
      editWrap.appendChild(div);
    }
  });

  // モーダル閉じる
  editOverlay
    .querySelector(".custom-close-btn")
    .addEventListener("click", () => {
      editOverlay.style.display = "none";
      currentEditItem = null;
    });

  //——— D. ADD フォーム送信 → アコーディオンにアイテム追加 ———
  const categoryMap = { html: "html", css: "css", javascript: "js" };
  addForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const title = addForm.title.value.trim();
    const desc = addForm.description.value.trim();
    const cat = addForm.category.value;
    const answers = Array.from(
      addForm.querySelectorAll('input[name="add-answer[]"]')
    ).map((i) => i.value.trim());
    if (!title || !desc || !cat)
      return alert("全てのフィールドを入力してください。");
    const mapped = categoryMap[cat];
    const container = document.querySelector(`.aco-content.${mapped}-aco`);
    if (!container) return alert("無効なカテゴリです。");

    // —— 新アイテム（ドラッグハンドル＋タイトル）生成 ——
    const item = document.createElement("div");
    item.className = "aco-item";
    item.innerHTML = `
      <span class="drag-handle">☰</span>
      <div class="aco-title">${title}</div>
      <div class="aco-description">${desc}</div>
      <ul class="aco-answers">
        ${answers.map((a) => `<li>${a}</li>`).join("")}
      </ul>
      <div class="aco-actions">
        <button class="edit-btn">Edit</button>
        <button class="delete-btn">Delete</button>
      </div>
    `;
    container.appendChild(item);

    // 自動で開く
    const tog = document.querySelector(`.toggle[data-target="${mapped}-aco"]`);
    if (tog && !tog.classList.contains("active")) tog.click();

    // フォームクリア
    addForm.reset();
    const fields = ansWrap.querySelectorAll(".answer-field");
    fields.forEach((f, i) => {
      if (i >= 2) f.remove();
      else f.querySelector("input").value = "";
    });
    updateLabels(ansWrap);
  });

  //——— E. Edit/Delete（委譲） + モーダル読み込み ———
  document.body.addEventListener("click", (e) => {
    // Delete
    if (e.target.matches(".aco-actions .delete-btn")) {
      if (confirm("Delete this item?")) e.target.closest(".aco-item").remove();
      return;
    }
    // Edit
    if (e.target.matches(".aco-actions .edit-btn")) {
      const item = e.target.closest(".aco-item");
      currentEditItem = item;
      // タイトル
      document.getElementById("edit-title").value =
        item.querySelector(".aco-title").textContent;
      document.getElementById("edit-description").value =
        item.querySelector(".aco-description").textContent;
      // カテゴリ
      const selCat = document.getElementById("edit-category");
      const p = item.closest(".aco-content");
      selCat.value = p.classList.contains("html-aco")
        ? "html"
        : p.classList.contains("css-aco")
        ? "css"
        : "javascript";
      // 回答
      const answers = Array.from(item.querySelectorAll(".aco-answers li")).map(
        (li) => li.textContent
      );
      editWrap.innerHTML = "";
      answers.forEach((a, i) => {
        const div = document.createElement("div");
        div.className = "answer-field";
        div.innerHTML = `
          <label>Answer.${i + 1}:</label>
          <input type="text" name="edit-answer[]" value="${a}" required>
          <button type="button" class="remove-btn">削除</button>
        `;
        editWrap.appendChild(div);
      });
      editOverlay.style.display = "flex";
    }
  });

  //——— F. モーダル保存 ———
  editForm.addEventListener("submit", (e) => {
    e.preventDefault();
    if (!currentEditItem) return;
    const newTitle = document.getElementById("edit-title").value.trim();
    const newDesc = document.getElementById("edit-description").value.trim();
    const newCat = document.getElementById("edit-category").value;
    const newAnswers = Array.from(
      editForm.querySelectorAll('input[name="edit-answer[]"]')
    ).map((i) => i.value.trim());
    if (!newTitle || !newDesc || !newCat)
      return alert("全てのフィールドを入力してください。");

    // 上書き
    currentEditItem.querySelector(".aco-title").textContent = newTitle;
    currentEditItem.querySelector(".aco-description").textContent = newDesc;
    currentEditItem.querySelector(".aco-answers").innerHTML = newAnswers
      .map((a) => `<li>${a}</li>`)
      .join("");
    // 移動
    const oldC = currentEditItem.closest(".aco-content");
    const mapC = { html: "html-aco", css: "css-aco", javascript: "js-aco" };
    const newC = document.querySelector(`.${mapC[newCat]}`);
    if (oldC !== newC) newC.appendChild(currentEditItem);

    editOverlay.style.display = "none";
    currentEditItem = null;
  });

  //——— G. 並べ替え機能（SortableJS） ———
  document.querySelectorAll(".aco-content").forEach((container) => {
    new Sortable(container, {
      animation: 150,
      draggable: ".aco-item",
      handle: ".drag-handle", // ★ここをドラッグハンドルに
    });
  });
});
