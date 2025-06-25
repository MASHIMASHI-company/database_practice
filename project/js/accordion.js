function toggleAccordion(clickedElem) {
  const content = clickedElem.nextElementSibling; // クリックされた要素の次の兄弟要素（問題文など）
  const arrow = clickedElem.querySelector(".arrow"); // クリックされた要素内の矢印部分

  content.classList.toggle("open");
  arrow.classList.toggle("rotate");
}