document.addEventListener("DOMContentLoaded", function () {
  const toggles = document.querySelectorAll(".toggle");

  toggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const targetClass = this.dataset.target;
      const target = document.querySelector(`.${targetClass}`);

      if (target) {
        const isOpen = target.style.display === "block";

        // 閉じる
        if (isOpen) {
          target.style.display = "none";
          this.classList.remove("active");
        }
        // 開く
        else {
          target.style.display = "block";
          this.classList.add("active");
        }
      }
    });
  });
});
