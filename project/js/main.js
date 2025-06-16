document.addEventListener("DOMContentLoaded", () => {
  let currentEditItem = null;
  const api = location.pathname + "?action=";

  // A. アコーディオン開閉
  document.body.addEventListener("click", (e) => {
    const tog = e.target.closest(".toggle");
    if (!tog) return;
    const pan = document.querySelector(`.${tog.dataset.target}`);
    if (!pan) return;
    pan.classList.toggle("open");
    tog.classList.toggle("active");
  });

  // B. ADDフォーム内 Answer 行の増減
  const addForm = document.getElementById("add-form");
  const ansWrap = document.getElementById("answer-form");
  function updateLabels(wrap) {
    wrap.querySelectorAll(".answer-field").forEach((fld, i) => {
      fld.querySelector("label").textContent = `Answer.${i + 1}:`;
    });
  }
  ansWrap.addEventListener("click", (e) => {
    if (e.target.matches(".remove-btn")) {
      const flds = ansWrap.querySelectorAll(".answer-field");
      if (flds.length <= 2) {
        alert("Answerは最低2つ必要です。");
        return;
      }
      e.target.closest(".answer-field").remove();
      updateLabels(ansWrap);
    } else if (e.target.matches("#add-answer-btn")) {
      const cnt = ansWrap.querySelectorAll(".answer-field").length + 1;
      const div = document.createElement("div");
      div.className = "answer-field";
      div.innerHTML = `
        <label>Answer.${cnt}:</label>
        <input type="text" name="add-answer[]" required>
        <button type="button" class="remove-btn">削除</button>
      `;
      ansWrap.insertBefore(div, e.target);
    }
  });

  // C. モーダル内 Answer 行の増減
  const editOverlay = document.getElementById("editOverlay");
  const editForm = document.getElementById("custom-edit-form");
  const editWrap = document.getElementById("edit-answer-fields");
  // ＋追加ボタンをモーダルHTML直後に追加
  if (editWrap) {
    const editAddBtn = document.createElement("button");
    editAddBtn.type = "button";
    editAddBtn.textContent = "＋追加";
    editAddBtn.classList.add("edit-add-btn");
    editWrap.parentNode.insertBefore(editAddBtn, editWrap.nextSibling);
  }

  document.body.addEventListener("click", (e) => {
    if (e.target.matches("#edit-answer-fields .remove-btn")) {
      const flds = editWrap.querySelectorAll(".answer-field");
      if (flds.length <= 2) {
        alert("Answerは最低2つ必要です。");
        return;
      }
      e.target.closest(".answer-field").remove();
      updateLabels(editWrap);
    } else if (e.target.matches(".edit-add-btn")) {
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

  // D. ADD → DB登録＋ページ再読み込み
  addForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const fd = new FormData(addForm);
    const title = fd.get("title").trim();
    const desc = fd.get("description").trim();
    const category = fd.get("category");
    const answers = fd.getAll("add-answer[]").map((a) => a.trim());
    if (!title || !desc || !category) {
      alert("全てのフィールドを入力してください。");
      return;
    }
    try {
      const res = await fetch(api + "add", {
        method: "POST",
        body: JSON.stringify({ title, description: desc, category, answers }),
      });
      const j = await res.json();
      if (j.success) return location.reload();
      throw new Error(j.error || "追加に失敗しました");
    } catch (err) {
      alert(err.message);
    }
  });

  // E. Delete / Edit(モーダル open)
  document.body.addEventListener("click", (e) => {
    const delBtn = e.target.closest(".delete-btn");
    if (delBtn) {
      if (!confirm("削除しますか？")) return;
      const item = delBtn.closest(".aco-item");
      fetch(api + "delete", {
        method: "POST",
        body: JSON.stringify({ id: item.dataset.id }),
      })
        .then((r) => r.json())
        .then((j) => {
          if (j.success) item.remove();
          else alert(j.error || "削除に失敗しました");
        });
      return;
    }

    const editBtn = e.target.closest(".edit-btn");
    if (editBtn) {
      const item = editBtn.closest(".aco-item");
      currentEditItem = item;
      // モーダルに値を詰める
      document.getElementById("edit-title").value =
        item.querySelector(".aco-title").textContent;
      document.getElementById("edit-description").value =
        item.querySelector(".aco-description").textContent;
      document.getElementById("edit-category").value = [
        "HTML",
        "CSS",
        "JavaScript",
      ].find((v) =>
        item.closest(".aco-content").classList.contains(v + "-aco")
      );
      // 回答リスト
      editWrap.innerHTML = "";
      item.querySelectorAll(".aco-answers li").forEach((li, i) => {
        const div = document.createElement("div");
        div.className = "answer-field";
        div.innerHTML = `
          <label>Answer.${i + 1}:</label>
          <input type="text" name="edit-answer[]" value="${
            li.textContent
          }" required>
          <button type="button" class="remove-btn">削除</button>
        `;
        editWrap.appendChild(div);
      });
      editOverlay.style.display = "flex";
    }
  });

  // F. モーダル保存 → DB更新＋reload
  editForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!currentEditItem) return;
    const fd = new FormData(editForm);
    const payload = {
      id: currentEditItem.dataset.id,
      title: fd.get("title").trim(),
      description: fd.get("description").trim(),
      category: fd.get("category"),
      answers: fd.getAll("edit-answer[]").map((a) => a.trim()),
    };
    try {
      const res = await fetch(api + "update", {
        method: "POST",
        body: JSON.stringify(payload),
      });
      const j = await res.json();
      if (j.success) return location.reload();
      throw new Error(j.error || "更新に失敗しました");
    } catch (err) {
      alert(err.message);
    }
  });

  // G. Sortable：handle を .aco-title に
  document.querySelectorAll(".aco-content").forEach((container) => {
    new Sortable(container, {
      animation: 150,
      draggable: ".aco-item",
      handle: ".aco-title", // ★.drag-handle → .aco-title に
      onStart: (el) => el.item.classList.add("dragging"),
      onEnd: (el) => el.item.classList.remove("dragging"),
    });
  });
});
document.addEventListener("DOMContentLoaded", () => {
  const editOverlay = document.getElementById("editOverlay");
  const closeBtn = editOverlay.querySelector(".custom-close-btn");

  // ×ボタンで閉じる
  closeBtn.addEventListener("click", () => {
    editOverlay.classList.remove("open");
  });

  // モーダル外クリックでも閉じる（任意）
  editOverlay.addEventListener("click", (e) => {
    if (e.target === editOverlay) {
      editOverlay.classList.remove("open");
    }
  });
});
