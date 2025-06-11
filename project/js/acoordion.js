function toggleAccordion() {
  const content = document.getElementById("problemContent");
  const arrow = document.getElementById("accordionArrow");

  content.classList.toggle("open");
  arrow.classList.toggle("rotate");

  // ▼ or ▲ 文字を切り替える場合（任意）
  if (content.classList.contains("open")) {
    arrow.textContent = "▲";
  } else {
    arrow.textContent = "▼";
  }
}
