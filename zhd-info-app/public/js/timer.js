// 一定時間経過後にトップページにリダイレクトする関数
function redirectIfInactive() {
  window.location.href = location.host;
}

// 操作があった場合にタイマーをリセットする関数
function resetTimer() {
  clearTimeout(timer);
  startTimer();
}

// イベントリスナーを追加して操作を監視する
document.addEventListener("mousemove", resetTimer);
document.addEventListener("keydown", resetTimer);
document.addEventListener("scroll", resetTimer);

// タイマーを開始する関数
function startTimer() {
  timer = setTimeout(redirectIfInactive, 40 * 60000); // 40分操作がなかった場合にリダイレクトする
}

// 初回のタイマーを開始する
startTimer();