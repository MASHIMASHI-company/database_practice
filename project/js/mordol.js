document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modal");
  const modalTitle = document.getElementById("modal-title");
  const closeButton = document.querySelector(".close-button");
  const form = document.getElementById("modal-form");
  const saveButton = document.getElementById("save-button");

  const sidebarItems = document.querySelectorAll(".sidebar ul li");

  const sideModal = (text) => {
    form.innerHTML = ""; // 前回の入力内容クリア

    if (text === "Pass Word") {
      // パスワード編集なら3つの入力フィールド
      form.appendChild(
        createInput("現在のパスワード", "current-password", "password")
      );
      form.appendChild(
        createInput("新しいパスワード", "new-password", "password")
      );
      form.appendChild(
        createInput("新しいパスワード（確認）", "confirm-password", "password")
      );
    } else {
      // その他の場合は1つの入力フィールド
      const placeholderText = `${text} を入力してください`;
      const inputElem = createInput(placeholderText, "single-input", "text");

      if (text === "User Name") {
        inputElem.value = SESSION_USERNAME || ""; // セッション変数からユーザーネームを取得
      } else if (text === "Email Address") {
        inputElem.value = SESSION_EMAIL || ""; // セッション変数からメールアドレスを取得
      }
      form.appendChild(inputElem);
    }
  };

  sidebarItems.forEach((item) => {
    const text = item.textContent.trim();

    if (["User Name", "Email Address", "Pass Word"].includes(text)) {
      item.addEventListener("click", () => {
        modalTitle.textContent = `${text} 編集`;
        sideModal(text);
        modal.classList.add("show");
      });
    }
  });

  // モーダルを閉じる処理
  closeButton.addEventListener("click", () => {
    modal.classList.remove("show");
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.remove("show");
    }
  });

  // 保存ボタン押下時の処理：フォーム内容をサーバー側にPOSTする
  saveButton.addEventListener("click", () => {
    const editType = modalTitle.textContent.replace(" 編集", "").trim();

    // バリデーション
    if (editType === "Pass Word") {
      const currentPasswordElem = form.querySelector(
        'input[name="current-password"]'
      );
      const newPasswordElem = form.querySelector('input[name="new-password"]');
      const confirmPasswordElem = form.querySelector(
        'input[name="confirm-password"]'
      );

      const currentPassword = currentPasswordElem
        ? currentPasswordElem.value.trim()
        : "";
      const newPassword = newPasswordElem ? newPasswordElem.value.trim() : "";
      const confirmPassword = confirmPasswordElem
        ? confirmPasswordElem.value.trim()
        : "";

      // 新しいパスワードが5文字以上かどうかチェック
      if (newPassword.length < 5) {
        alert("新しいパスワードは5文字以上にしてください。");
        return;
      }

      // 新しいパスワードと確認用パスワードが一致するかチェック
      if (newPassword !== confirmPassword) {
        alert("新しいパスワードと確認用パスワードが一致しません。");
        return;
      }

      // ※ クライアント側では「現在のパスワード」との一致は確認できないので、
      // サーバー側で password_verify() を利用してチェックしてください。
      // 単純に、現在のパスワードと新しいパスワードが同じ場合は変更しない例
      if (currentPassword === newPassword) {
        alert("新しいパスワードは現在のパスワードと異なるものにしてください。");
        return;
      }
    } else if (editType === "Email Address") {
      const emailElem = form.querySelector('input[name="single-input"]');
      const email = emailElem ? emailElem.value.trim() : "";
      // 簡易的なメール形式の正規表現チェック
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        alert("正しいメールアドレスを入力してください。");
        return;
      }
    }

    // バリデーションパスの場合、フォームデータの送信に進む
    const formData = new FormData(form);
    formData.append("editType", editType);

    fetch("update_db.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          console.log("データベース更新成功:", data);
          modal.classList.remove("show");

          // ユーザーネーム編集の場合、更新された名前で表示を更新する
          if (editType === "User Name" && data.new_username) {
            // 例えば、footer内の<div>にユーザーネームが表示されていると仮定
            const usernameDisplay = document.querySelector("footer div");
            if (usernameDisplay) {
              usernameDisplay.textContent = data.new_username;
              SESSION_USERNAME = data.new_username; // セッション変数も更新
              sideModal(editType); // モーダルの内容も更新
            }
          } else if (editType === "Email Address" && data.new_email) {
            // Email Address の場合も同様に更新
            SESSION_EMAIL = data.new_email; // セッション変数も更新
            sideModal(editType); // モーダルの内容も更新
          }
        } else {
          console.error("更新エラー:", data.error);
          alert(`更新に失敗しました: ${data.error}`);
        }
      })
      .catch((error) => {
        console.error("ネットワークエラー:", error);
        alert("通信エラーが発生しました。");
      });
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
