document.addEventListener("DOMContentLoaded", () => {
  //——— A. アコーディオン開閉 ———
  // ※ CSS 側で .aco-content.open{display:block;} を用意しておく
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
  const ansWrapper = document.getElementById("answer-form");
  const addAnsBtn = document.getElementById("add-answer-btn");

  function updateLabels(container) {
    container.querySelectorAll(".answer-field").forEach((f, i) => {
      f.querySelector("label").textContent = `Answer.${i + 1}:`;
    });
  }

  ansWrapper.addEventListener("click", (e) => {
    if (e.target.matches(".remove-btn")) {
      const fields = ansWrapper.querySelectorAll(".answer-field");
      if (fields.length <= 2) return alert("Answerは最低2つ必要です。");
      e.target.closest(".answer-field").remove();
      updateLabels(ansWrapper);
    }
    if (e.target.matches("#add-answer-btn")) {
      const cnt = ansWrapper.querySelectorAll(".answer-field").length + 1;
      const div = document.createElement("div");
      div.className = "answer-field";
      div.innerHTML = `
        <label>Answer.${cnt}:</label>
        <input type="text" name="add-answer[]" required>
        <button type="button" class="remove-btn">削除</button>
      `;
      ansWrapper.insertBefore(div, addAnsBtn);
    }
  });

  //——— C. モーダル内 Answer 行の追加・削除 ———
  const editOverlay = document.getElementById("editOverlay");
  const editWrap = document.getElementById("edit-answer-fields");
  const editAddBtn = document.createElement("button");
  editAddBtn.type = "button";
  editAddBtn.textContent = "＋追加";
  editAddBtn.classList.add("edit-add-btn");
  editWrap.after(editAddBtn);

  document.body.addEventListener("click", (e) => {
    if (e.target.matches("#edit-answer-fields .remove-btn")) {
      const flds = editWrap.querySelectorAll(".answer-field");
      if (flds.length <= 2) return alert("Answerは最低2つ必要です。");
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
    .addEventListener("click", () => (editOverlay.style.display = "none"));

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

    if (!title || !desc || !cat) {
      return alert("全てのフィールドを入力してください。");
    }
    const mapped = categoryMap[cat];
    const container = document.querySelector(`.aco-content.${mapped}-aco`);
    if (!container) return alert("無効なカテゴリです。");

    const item = document.createElement("div");
    item.className = "aco-item";
    item.innerHTML = `
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

    // アコーディオンを自動で開く
    const tog = document.querySelector(`.toggle[data-target="${mapped}-aco"]`);
    if (tog && !tog.classList.contains("active")) tog.click();

    // フォーム初期化
    addForm.reset();
    const fields = ansWrapper.querySelectorAll(".answer-field");
    fields.forEach((f, i) => {
      if (i >= 2) f.remove();
      else f.querySelector("input").value = "";
    });
    updateLabels(ansWrapper);
  });

  //——— E. Edit/Delete ボタン（イベント委譲） ———
  document.body.addEventListener("click", (e) => {
    if (e.target.matches(".aco-actions .delete-btn")) {
      if (confirm("Delete this item?")) {
        e.target.closest(".aco-item").remove();
      }
    }
    if (e.target.matches(".aco-actions .edit-btn")) {
      // ここでモーダルにデータを流し込む処理を追加できます
      editOverlay.style.display = "flex";
    }
  });
});
