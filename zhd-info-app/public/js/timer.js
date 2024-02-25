// 一定時間経過後にトップページにリダイレクトする関数
function redirectIfInactive() {
  window.location.href = location.host;
}
// ログアウト関数
function logout() {
  if($('#logoutForm').length)$('#logoutForm').submit();
}

// 操作があった場合にタイマーをリセットする関数
function resetTimer() {
  clearTimeout(timer1);
  clearInterval(timer2);
  startTimer();
}

// イベントリスナーを追加して操作を監視する
document.addEventListener("mousemove", resetTimer);
document.addEventListener("keydown", resetTimer);
document.addEventListener("scroll", resetTimer);

// タイマーを開始する関数
function startTimer() {
  timer1 = setTimeout(redirectIfInactive, 40 * 60000); // 40分操作がなかった場合にリダイレクトする
  
  let targetTime = Date.now() + (5 * 60 * 1000);
  timer2 = setInterval(function(){
    if (Date.now() > targetTime) {
      clearInterval(timer2);
      if($('#logoutForm').length)$('#logoutForm').submit();
    }

  }, 500);
}

// 初回のタイマーを開始する
startTimer();