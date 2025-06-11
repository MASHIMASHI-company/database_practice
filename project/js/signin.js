// // モーダル要素を取得
// const modalSignIn = document.getElementById("modalSignIn");
// const modalSignUp = document.getElementById("modalSignUp");

// // ボタン＆閉じるアイコン
// const openSignIn = document.getElementById("openSignIn");
// const closeSignIn = document.getElementById("closeSignIn");
// const openSignUp = document.getElementById("openSignUp");
// const closeSignUp = document.getElementById("closeSignUp");

// // モーダルを開く
// openSignIn.addEventListener("click", (e) => {
//   e.preventDefault();
//   modalSignIn.style.display = "block";
// });
// openSignUp.addEventListener("click", (e) => {
//   e.preventDefault();
//   modalSignUp.style.display = "block";
// });

// // モーダルを閉じる
// closeSignIn.addEventListener(
//   "click",
//   () => (modalSignIn.style.display = "none")
// );
// closeSignUp.addEventListener(
//   "click",
//   () => (modalSignUp.style.display = "none")
// );

// // モーダル外をクリックしたら閉じる
// window.addEventListener("click", (e) => {
//   if (e.target === modalSignIn) modalSignIn.style.display = "none";
//   if (e.target === modalSignUp) modalSignUp.style.display = "none";
// });

// // Optional: SignUp フォームでパスワード一致チェック
// document.getElementById("formSignUp").addEventListener("submit", (e) => {
//   const pw1 = document.getElementById("signup-password").value;
//   const pw2 = document.getElementById("signup-password2").value;
//   if (pw1 !== pw2) {
//     e.preventDefault();
//     alert("パスワードが一致しません");
//   }
// });
