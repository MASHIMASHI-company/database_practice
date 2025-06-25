function toggleAccordion(clickedElem) {
  const content = clickedElem.nextElementSibling;
  const arrow = clickedElem.querySelector(".arrow");

  const isOpen = content.classList.contains("open");

  if (isOpen) {
    content.style.maxHeight = 0;
    content.classList.remove("open");
    arrow.classList.remove("rotate");
  } else {
    content.classList.add("open");
    content.style.maxHeight = content.scrollHeight + "px";
    arrow.classList.add("rotate");
  }
}