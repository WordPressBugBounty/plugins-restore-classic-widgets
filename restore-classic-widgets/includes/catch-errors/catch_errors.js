// catch javascript errors
(function () {
  var errorQueue = [];
  var restore_classic_widgets_timeout;
  var errorMessage = "";

  function isBot() {
    const bots = [
      "crawler",
      "spider",
      "baidu",
      "duckduckgo",
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

  // Listener para erros padrão de JavaScript.
  // Ele começa a "ouvir" assim que o script é carregado.
  window.addEventListener("error", function (event) {
    var msg = event.message;
    if (msg === "Script error.") {
      console.error("Script error detected - maybe problem cross-origin");
      return;
    }
    errorMessage = [
      "Message: " + msg,
      "URL: " + event.filename,
      "Line: " + event.lineno,
    ].join(" - ");

    if (isBot()) {
      return;
    }
    errorQueue.push(errorMessage);
    handleErrorQueue();
  });

  // Listener para erros modernos de Promises (ex: async/await).
  window.addEventListener("unhandledrejection", function (event) {
    errorMessage = "Promise Rejection: " + (event.reason || "Unknown reason");
    if (isBot()) {
      return;
    }
    errorQueue.push(errorMessage);
    handleErrorQueue();
  });

  function handleErrorQueue() {
    if (errorQueue.length >= 5) {
      sendErrorsToServer();
    } else {
      clearTimeout(restore_classic_widgets_timeout);
      restore_classic_widgets_timeout = setTimeout(sendErrorsToServer, 5000);
    }
  }

  function sendErrorsToServer() {
    if (errorQueue.length > 0) {
      var message = errorQueue.join(" | ");
      var xhr = new XMLHttpRequest();

      if (typeof restore_classic_widgets_error_data === "undefined") {
        console.error(
          "restore_classic_widgets_error_data is not defined. Was wp_localize_script used correctly?"
        );
        return;
      }

      var nonce = restore_classic_widgets_error_data.nonce;
      var ajaxurl = restore_classic_widgets_error_data.ajaxurl;

      xhr.open("POST", ajaxurl);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onload = function () {
        if (xhr.status === 200) {
          // Sucesso
        } else {
          console.log("Error sending log:", xhr.status);
        }
      };
      xhr.onerror = function () {
        console.error("Request failed");
      };

      var params =
        "action=restore_classic_widgets_minozzi_js_error_catched&_wpnonce=" +
        nonce +
        "&restore_classic_widgets_js_error_catched=" +
        encodeURIComponent(message);
      xhr.send(params);

      errorQueue = [];
    }
  }

  window.addEventListener("beforeunload", sendErrorsToServer);
})(); // A IIFE é fechada e executada aqui.