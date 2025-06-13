document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modal");
  const modalTitle = document.getElementById("modal-title");
  const closeButton = document.querySelector(".close-button");
  const form = document.getElementById("modal-form");

  const sidebarItems = document.querySelectorAll(".sidebar ul li");

  sidebarItems.forEach((item) => {
    const text = item.textContent.trim();

    if (["User Name", "Email Address", "Pass Word"].includes(text)) {
      item.addEventListener("click", () => {
        modalTitle.textContent = `${text} 編集`;
        form.innerHTML = ""; // 以前の入力をクリア

        if (text === "Pass Word") {
          // パスワードの場合：3つの入力フィールドを追加
          form.appendChild(
            createInput("現在のパスワード", "current-password", "password")
          );
          form.appendChild(
            createInput("新しいパスワード", "new-password", "password")
          );
          form.appendChild(
            createInput(
              "新しいパスワード（確認）",
              "confirm-password",
              "password"
            )
          );
        } else {
          // それ以外は1つの入力フィールド
          form.appendChild(
            createInput(`${text} を入力してください`, "single-input", "text")
          );
        }

        modal.style.display = "block";
      });
    }
  });

  closeButton.addEventListener("click", () => {
    modal.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  });

  function createInput(placeholder, name, type = "text") {
    const input = document.createElement("input");
    input.type = type;
    input.name = name;
    input.placeholder = placeholder;
    input.style.display = "block";
    input.style.marginBottom = "1rem";
    input.style.width = "100%";
    input.style.padding = "0.5rem";
    input.style.fontSize = "1rem";
    input.style.boxSizing = "border-box";
    return input;
  }
});
