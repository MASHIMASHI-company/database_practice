const modalSignUp = document.getElementById("modalSignUp");
const openSignUp = document.getElementById("openSignUp");
const closeSignUp = document.getElementById("closeSignUp");

openSignUp.onclick = function (e) {
  e.preventDefault();
  modalSignUp.style.display = "block";
};

closeSignUp.onclick = function () {
  modalSignUp.style.display = "none";
};

window.addEventListener("click", function (event) {
  if (event.target == modalSignUp) {
    modalSignUp.style.display = "none";
  }
});
