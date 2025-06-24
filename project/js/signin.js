// モーダル要素の取得
const modalSignIn = document.getElementById("modalSignIn");
const modalSignUp = document.getElementById("modalSignUp");

// 各フォーム要素の取得
const formSignIn = document.getElementById("formSignIn");
const formSignUp = document.getElementById("formSignUp");

// ボタン・閉じるアイコンの取得
const openSignIn = document.getElementById("openSignIn");
const openSignUp = document.getElementById("openSignUp");
const closeSignIn = document.getElementById("closeSignIn");
const closeSignUp = document.getElementById("closeSignUp");

// モーダルを開く（「show」クラスのみ追加）
openSignIn.addEventListener("click", (e) => {
  e.preventDefault();
  modalSignIn.classList.add("show");
});
openSignUp.addEventListener("click", (e) => {
  e.preventDefault();
  modalSignUp.classList.add("show");
});

// モーダルを閉じるとき（×ボタン）の処理：style.display の設定は削除し、クラスの削除のみ
closeSignIn.addEventListener("click", () => {
  modalSignIn.classList.remove("show");
  const errorMsg = document.getElementById("signInError");
  if (errorMsg) { errorMsg.style.display = "none"; }
  formSignIn.reset();
});
closeSignUp.addEventListener("click", () => {
  modalSignUp.classList.remove("show");
  const serverError = document.getElementById("serverSignUpError");
  if (serverError) { serverError.style.display = "none"; }
  document.getElementById("signUpError").style.display = "none";
  formSignUp.reset();
});

// 画面外クリック時にモーダルを閉じ、フォームリセットを実行（style.display の設定は削除）
window.addEventListener("click", (e) => {
  if (e.target === modalSignIn) {
    modalSignIn.classList.remove("show");
    const errorMsg = document.getElementById("signInError");
    if (errorMsg) { errorMsg.style.display = "none"; }
    formSignIn.reset();
  }
  if (e.target === modalSignUp) {
    modalSignUp.classList.remove("show");
    const serverError = document.getElementById("serverSignUpError");
    if (serverError) { serverError.style.display = "none"; }
    document.getElementById("signUpError").style.display = "none";
    formSignUp.reset();
  }
});

// Sign Up フォーム: パスワード一致・文字数チェック、Ajax 重複チェック
document.getElementById("formSignUp").addEventListener("submit", async (e) => {
  e.preventDefault();
  const pw1 = document.getElementById("signup-password").value;
  const pw2 = document.getElementById("signup-password2").value;
  if (pw1 !== pw2) {
    alert("パスワードが一致しません");
    return;
  }
  if (pw1.length < 5) {
    alert("パスワードは5文字以上でなければなりません");
    return;
  }
  const username = document.getElementById("signup-username").value.trim();
  const email = document.getElementById("signup-email").value.trim();
  const errorDiv = document.getElementById("signUpError");

  try {
    const response = await fetch(`index.php?checkDuplicate=1&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`);
    const result = await response.json();
    let errorMessages = [];
    if (result.usernameExists) {
      errorMessages.push("そのユーザー名は使用されています");
    }
    if (result.emailExists) {
      errorMessages.push("そのメールアドレスは使用されています");
    }
    if (errorMessages.length > 0) {
      errorDiv.innerHTML = errorMessages.join("<br>");
      errorDiv.style.display = "block";
      return; // 重複があれば送信を中断
    } else {
      errorDiv.style.display = "none";
      e.target.submit();  // 問題なければフォーム送信
    }
  } catch (err) {
    console.error(err);
  }
});

// Optional: 入力後 (blur イベント) に Ajax 重複チェックを実施
const signupUsernameInput = document.getElementById("signup-username");
const signupEmailInput = document.getElementById("signup-email");
signupUsernameInput.addEventListener("blur", () => checkDuplicate());
signupEmailInput.addEventListener("blur", () => checkDuplicate());

function checkDuplicate() {
  const username = signupUsernameInput.value.trim();
  const email = signupEmailInput.value.trim();
  const errorDiv = document.getElementById("signUpError");
  if (username === "" && email === "") {
    errorDiv.style.display = "none";
    return;
  }
  fetch(`index.php?checkDuplicate=1&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`)
    .then(response => response.json())
    .then(result => {
      let errorMessages = [];
      if (result.usernameExists) { errorMessages.push("そのユーザー名は使用されています"); }
      if (result.emailExists) { errorMessages.push("そのメールアドレスは使用されています"); }
      if (errorMessages.length > 0) {
        errorDiv.innerHTML = errorMessages.join("<br>");
        errorDiv.style.display = "block";
      } else {
        errorDiv.style.display = "none";
      }
    })
    .catch(err => console.error(err));
}
