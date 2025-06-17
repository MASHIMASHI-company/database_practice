let currentIndex = 0;

function renderQuestion(index) {
  const quiz = window.quizData[index];
  document.getElementById('problem-statement').textContent = quiz.content;

  const optionsDiv = document.getElementById('options');
  optionsDiv.innerHTML = '';
  quiz.choices.forEach(choice => {
    const label = document.createElement('label');
    const input = document.createElement('input');
    input.type = 'radio';
    input.name = 'q' + index;
    input.value = choice.index_number;
    label.appendChild(input);
    label.appendChild(document.createTextNode(choice.choice_text));
    optionsDiv.appendChild(label);
  });

  document.querySelector('.level').textContent = `${index + 1}/${window.quizData.length}`;
  document.querySelector('.q').textContent = `Q${index + 1}`;
}

async function checkAndGo() {
  const quiz = window.quizData[currentIndex];
  const selected = document.querySelector(`input[name="q${currentIndex}"]:checked`);
  if (!selected) {
    alert('選択肢を選んでください！');
    return;
  }

  try {
    await fetch(window.quizSaveUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        quizId: quiz.id,             // ここをquiz.idに変更
        choiceIndex: selected.value
      })
    });
  } catch (e) {
    alert('通信エラーで回答の保存に失敗しました。');
    return;
  }

  currentIndex++;
  if (currentIndex < window.quizData.length) {
    renderQuestion(currentIndex);
  } else {
    window.location.href = 'result.php?tag=' + encodeURIComponent(window.quizTag);
  }
}

window.addEventListener('DOMContentLoaded', () => {
  renderQuestion(currentIndex);
});
