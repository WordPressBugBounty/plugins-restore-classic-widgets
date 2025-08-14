// Estas variáveis e funções são globais para este script e precisam estar
// disponíveis imediatamente. Não devem esperar pelo document.ready.

var errorQueue = [];
var timeout;

function isBot() {
  const bots = [
    "bot",
    "googlebot",
    "bingbot",
    "facebook",
    "slurp",
    "twitter",
    "yahoo",
  ];
  const userAgent = navigator.userAgent.toLowerCase();
  return bots.some((bot) => userAgent.includes(bot));
}

function sendErrorsToServer() {
  // Acessa o objeto global 'billErrorCatcherData' passado pelo PHP
  if (errorQueue.length > 0 && typeof billErrorCatcherData !== "undefined") {
    var message = errorQueue.join(" | ");
    var xhr = new XMLHttpRequest();

    var nonce = billErrorCatcherData.nonce;
    var ajaxurl = billErrorCatcherData.ajaxurl;

    xhr.open("POST", ajaxurl);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
      if (200 !== xhr.status) {
        // console.log('AJAX error');
      }
    };

    xhr.send(
      "action=restore_classic_widgets_js_error_catched&_wpnonce=" +
        nonce +
        "&restore_classic_widgets_js_error_catched=" +
        encodeURIComponent(message)
    );
    errorQueue = [];
  }
}

// Estes listeners são anexados ao objeto 'window' global e precisam
// estar ativos o mais cedo possível, não depois do DOM estar pronto.
window.onerror = function (msg, url, line) {
  var errorMessage = ["Message: " + msg, "URL: " + url, "Line: " + line].join(
    " - "
  );

  if (isBot()) {
    return;
  }

  errorQueue.push(errorMessage);
  if (errorQueue.length >= 5) {
    sendErrorsToServer();
  } else {
    clearTimeout(timeout);
    timeout = setTimeout(sendErrorsToServer, 5000);
  }
};

window.addEventListener("beforeunload", sendErrorsToServer);
