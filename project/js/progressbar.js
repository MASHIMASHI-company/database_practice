(function () {
  /**
   * ゲージを更新する関数
   * @param {number} current 現在値
   * @param {number} max     最大値
   */
  function updateGauge(current, max) {
    const bar = document.querySelector(".status-bar");
    const lvl = bar?.querySelector(".level");
    if (!bar || !lvl) return;
    const pct = Math.min(1, current / max) * 100;
    // バー背景を left→right に伸ばす
    bar.style.backgroundSize = pct + "% 100%";
    // テキストは常に同じ位置に表示
    lvl.textContent = `${current}/${max}`;
  }

  // グローバルで呼べるように
  window.updateGauge = updateGauge;

  // 初期表示（HTML に書いてある "0/300" 等を読み取って）
  document.addEventListener("DOMContentLoaded", () => {
    const lvl = document.querySelector(".status-bar .level");
    if (!lvl) return;
    const [cur, max] = lvl.textContent.split("/").map((v) => parseInt(v, 10));
    updateGauge(cur, max);
  });
})();
