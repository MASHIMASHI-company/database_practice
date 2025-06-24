function toggleAccordion(clickedElem) {
  const content = clickedElem.nextElementSibling; // クリックされた要素の次の兄弟要素（問題文など）
  const arrow = clickedElem.querySelector(".arrow"); // クリックされた要素内の矢印部分

  content.classList.toggle("open");
  arrow.classList.toggle("rotate");

  if (content.classList.contains("open")) {
    arrow.textContent = "▲";
  } else {
    arrow.textContent = "▼";
  }
}

document.body.addEventListener("click", (e) => {
  const tog = e.target.closest(".toggle");
  if (!tog) return;
  document.querySelector(`.${tog.dataset.target}`)?.classList.toggle("open");
  tog.classList.toggle("active");
});
