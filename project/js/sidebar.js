const hamburger = document.querySelector(".hamburger-menu");
const sidebar = document.getElementById("sidebar");

hamburger.addEventListener("click", () => {
  sidebar.classList.toggle("active");
});
