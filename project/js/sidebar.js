const hamburger = document.querySelector(".hamburger-menu");
const sidebar = document.getElementById("sidebar");

// ハンバーガーメニューをクリックした際、サイドバーの表示状態を切り替える
hamburger.addEventListener("click", (e) => {
  sidebar.classList.toggle("active");
  // ハンバーガーメニュー内のクリックがdocumentのクリックイベントに伝播しないようにする
  e.stopPropagation();
});

// サイドバー以外の領域をクリックした場合、サイドバーを閉じる
document.addEventListener("click", (e) => {
  // クリックした要素がサイドバー、またはハンバーガーメニューに含まれていない場合
  if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
    sidebar.classList.remove("active");
  }
});
