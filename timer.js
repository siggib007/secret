function startTimer(duration, display) {
  var timer = duration,
    minutes, seconds;
  setInterval(function () {
    minutes = parseInt(timer / 60, 10)
    seconds = parseInt(timer % 60, 10);

    minutes = minutes < 10 ? "0" + minutes : minutes;
    seconds = seconds < 10 ? "0" + seconds : seconds;

    display.textContent = minutes + ":" + seconds;

    if (--timer < 0) {
      window.location.replace("/index.php");;
    }
  }, 1000);
}

window.onload = function () {
  var display = document.querySelector('#time'),
    iSeconds = 60 * display.textContent;
  startTimer(iSeconds, display);
};
