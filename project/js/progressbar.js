(function () {
  function updateGauge(current, max) {
    const bar = document.querySelector(".status-bar");
    const lvl = bar?.querySelector(".level");
    if (!bar || !lvl) return;
    const pct = Math.min(1, current / max) * 100;
    bar.style.backgroundSize = pct + "% 100%";
    lvl.textContent = `${current}/${max}`;
  }

  window.updateGauge = updateGauge;
})();
