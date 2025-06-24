document.addEventListener("DOMContentLoaded", () => {
  let currentEditItem = null;
  const api = location.pathname + "?action=";

  // ■ B: ADDフォーム／Answer＋正解ラジオ操作
  const addForm = document.getElementById("add-form");
  const ansWrap = document.getElementById("answer-form");
  function attachAddRadio(field, idx) {
    const r = document.createElement("input");
    r.type = "radio";
    r.name = "add-correct";
    r.value = idx;
    r.required = true;
    const lbl = document.createElement("label");
    lbl.className = "correct-label";
    lbl.textContent = "";
    lbl.prepend(r);
    field.prepend(lbl);
  }
  if (addForm && ansWrap) {
    ansWrap
      .querySelectorAll(".answer-field")
      .forEach((f, i) => attachAddRadio(f, i));
    ansWrap.addEventListener("click", (e) => {
      if (e.target.matches(".remove-btn")) {
        const flds = ansWrap.querySelectorAll(".answer-field");
        if (flds.length <= 2) return alert("最低2つ必要です");
        e.target.closest(".answer-field").remove();
        ansWrap.querySelectorAll(".answer-field").forEach((f, i) => {
          f.querySelector("label:not(.correct-label)").textContent = `Answer.${
            i + 1
          }:`;
          const rr = f.querySelector("input[type=radio]");
          if (rr) rr.value = i;
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
        attachAddRadio(div, cnt - 1);
      }
    });
    addForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(addForm),
        title = fd.get("title")?.trim(),
        description = fd.get("description")?.trim(),
        category = fd.get("category"),
        answers = fd.getAll("add-answer[]").map((v) => v.trim()),
        correctIndex = parseInt(fd.get("add-correct"), 10);
      if (!title || !description || !category)
        return alert("全て入力してください");
      const res = await fetch(api + "add", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            title,
            description,
            category,
            answers,
            correctIndex,
          }),
        }),
        j = await res.json();
      if (j.success) return location.reload();
      alert(j.error || "追加失敗");
    });
  }

  // ■ C: EDITモーダル／Answer＋正解ラジオ操作
  const editOverlay = document.getElementById("editOverlay"),
    editForm = document.getElementById("custom-edit-form"),
    editWrap = document.getElementById("edit-answer-fields");
  function attachEditRadio(field, idx, chk = false) {
    const r = document.createElement("input");
    r.type = "radio";
    r.name = "edit-correct";
    r.value = idx;
    r.required = true;
    if (chk) r.checked = true;
    const lbl = document.createElement("label");
    lbl.className = "correct-label";
    lbl.textContent = "";
    lbl.prepend(r);
    field.prepend(lbl);
  }
  if (editWrap) {
    const btn = document.createElement("button");
    btn.type = "button";
    btn.textContent = "＋追加";
    btn.classList.add("edit-add-btn");
    editWrap.parentNode.insertBefore(btn, editWrap.nextSibling);
    document.body.addEventListener("click", (e) => {
      if (e.target.matches("#edit-answer-fields .remove-btn")) {
        const flds = editWrap.querySelectorAll(".answer-field");
        if (flds.length <= 2) return alert("最低2つ必要です");
        e.target.closest(".answer-field").remove();
        editWrap.querySelectorAll(".answer-field").forEach((f, i) => {
          f.querySelector("label:not(.correct-label)").textContent = `Answer.${
            i + 1
          }:`;
          const rr = f.querySelector("input[type=radio]");
          if (rr) rr.value = i;
        });
      }
      if (e.target.matches(".edit-add-btn")) {
        const cnt = editWrap.querySelectorAll(".answer-field").length + 1;
        const div = document.createElement("div");
        div.className = "answer-field";
        div.innerHTML = `
            <label>Answer.${cnt}:</label>
            <input type="text" name="edit-answer[]" required>
            <button type="button" class="remove-btn">削除</button>`;
        editWrap.appendChild(div);
        attachEditRadio(div, cnt - 1);
      }
    });
    editOverlay
      .querySelector(".custom-close-btn")
      .addEventListener("click", () => {
        editOverlay.style.display = "none";
        currentEditItem = null;
      });
  }

  // ■ D: Delete / Edit 開く
  document.body.addEventListener("click", (e) => {
    // Delete
    const del = e.target.closest(".delete-btn");
    if (del) {
      if (!confirm("削除しますか？")) return;
      const item = del.closest(".aco-item");
      fetch(api + "delete", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: item.dataset.id }),
      })
        .then((r) => r.json())
        .then((j) => {
          if (j.success) item.remove();
          else alert(j.error || "削除失敗");
        });
      return;
    }
    // Edit 開く
    const ed = e.target.closest(".edit-btn");
    if (ed && editForm && editWrap) {
      const item = ed.closest(".aco-item");
      currentEditItem = item;
      // Title/Description/Category
      editForm.querySelector("#edit-title").value =
        item.querySelector(".aco-title").textContent;
      editForm.querySelector("#edit-description").value =
        item.querySelector(".aco-description").textContent;
      editForm.querySelector("#edit-category").value = [
        "HTML",
        "CSS",
        "JavaScript",
      ].find((v) => item.closest(`.${v}-aco`));
      // 既存Answersと correctIndex 取得
      const lis = Array.from(item.querySelectorAll(".aco-answers li")),
        correctIdx = lis.findIndex((li) => li.classList.contains("correct"));
      editWrap.innerHTML = "";
      lis.forEach((li, i) => {
        const div = document.createElement("div");
        div.className = "answer-field";

        // 1) テキストは trim() して前後の空白除去
        const text = li.textContent.trim();

        // 2) 先頭の改行・空白を消した一行文字列で innerHTML に渡す
        div.innerHTML =
          `<label>Answer.${i + 1}:</label>` +
          `<input type="text" name="edit-answer[]" value="${text}" required>` +
          `<button type="button" class="remove-btn">削除</button>`;

        editWrap.appendChild(div);
        attachEditRadio(div, i, i === correctIdx);
      });
      editOverlay.style.display = "flex";
    }
  });

  // ■ E: EDIT保存 → DB+DOM更新
  if (editForm) {
    editForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!currentEditItem) return;
      const fd = new FormData(editForm),
        payload = {
          id: currentEditItem.dataset.id,
          title: fd.get("title").trim(),
          description: fd.get("description").trim(),
          category: fd.get("category"),
          answers: fd.getAll("edit-answer[]").map((v) => v.trim()),
          correctIndex: parseInt(fd.get("edit-correct"), 10),
        },
        res = await fetch(api + "update", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        }),
        j = await res.json();
      if (!j.success) return alert(j.error || "更新失敗");
      // DOM更新
      currentEditItem.querySelector(".aco-title").textContent = payload.title;
      currentEditItem.querySelector(".aco-description").textContent =
        payload.description;
      const ul = currentEditItem.querySelector(".aco-answers");
      ul.innerHTML = payload.answers
        .map(
          (a, i) =>
            `<li${
              i === payload.correctIndex ? ' class="correct"' : ""
            }>${a}</li>`
        )
        .join("");
      const oldP = currentEditItem.closest(".aco-content"),
        newP = document.querySelector(`.${payload.category}-aco`);
      if (oldP && newP && oldP !== newP) newP.appendChild(currentEditItem);
      editOverlay.style.display = "none";
      currentEditItem = null;
    });
  }

  // ■ F: Sortable（既存そのまま）
  //   document.querySelectorAll(".aco-content").forEach((container) => {
  //     new Sortable(container, {
  //       animation: 150,
  //       draggable: ".aco-item",
  //       handle: ".aco-title",
  //       onStart: (e) => e.item.classList.add("dragging"),
  //       onEnd: (e) => e.item.classList.remove("dragging"),
  //     });
  //   });
});
