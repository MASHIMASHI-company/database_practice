function checkAndGo() {
  const selected = document.querySelector('input[name="q1"]:checked');
  if (selected) {
    window.location.href = "result.php";
  } else {
    alert("選択肢を選んでください！");
  }
}
