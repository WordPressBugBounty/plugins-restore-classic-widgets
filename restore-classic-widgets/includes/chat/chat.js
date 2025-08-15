jQuery(document).ready(function ($) {
  let chatVersion = "2.00";
  const billChatMessages = $("#chat-messages");
  const billChatForm = $("#chat-form");
  const billChatInput = $("#chat-input");
  const billChaterrorMessage = $("#error-message");
  let billChatLastMessageCount = 0;

  const autoCheckupButtons = $("#auto-checkup, #auto-checkup2");
  if (autoCheckupButtons.length === 0) {
  }

  let billChat_inactivityTimer;
  let billChat_userHasInteracted = false;

  function billChat_triggerPulseAnimation() {
    if (billChat_userHasInteracted) return;

    autoCheckupButtons.addClass("pulse-button");

    setTimeout(function () {
      autoCheckupButtons.removeClass("pulse-button");

      if (!billChat_userHasInteracted) {
        billChat_inactivityTimer = setTimeout(
          billChat_triggerPulseAnimation,
          15000
        );
      }
    }, 6000);
  }

  function billChat_resetInactivityTimer() {
    if (billChat_userHasInteracted) return;

    clearTimeout(billChat_inactivityTimer);

    autoCheckupButtons.removeClass("pulse-button");

    billChat_inactivityTimer = setTimeout(billChat_triggerPulseAnimation, 8000);
  }

  function billChat_stopAnimationFeature() {
    billChat_userHasInteracted = true;
    clearTimeout(billChat_inactivityTimer);
    autoCheckupButtons.removeClass("pulse-button");
  }

  $(document).on("mousemove keypress click", billChat_resetInactivityTimer);

  billChat_resetInactivityTimer();

  function billChatEscapeHtml(text) {
    return $("<div>").text(text).html();
  }

  $.ajax({
    url: restore_classic_widgets_data.ajax_url,
    type: "POST",
    data: {
      action: "restore_classic_widgets_chat_reset_messages",
      security: restore_classic_widgets_data.reset_nonce, // Enviar o nonce
    },
    success: function () { },
    error: function (xhr, status, error) {
      console.error(
        restore_classic_widgets_data.reset_error,
        error,
        xhr.responseText
      );
    },
  });

  function billChatLoadMessages() {
    $.ajax({
      url: restore_classic_widgets_data.ajax_url,
      method: "POST",
      data: {
        action: "restore_classic_widgets_chat_load_messages",
        security: restore_classic_widgets_data.reset_nonce, // <-- CORRIGIDO
        last_count: billChatLastMessageCount,
      },
      success: function (response, status, xhr) {
        try {
          if (typeof response === "string") {
            response = JSON.parse(response);
          }
          if (Array.isArray(response.messages)) {
            if (response.message_count > billChatLastMessageCount) {
              billChatLastMessageCount = response.message_count;
              response.messages.forEach(function (message) {
                if (message.text && message.sender) {
                  if (message.sender === "user") {
                    billChatMessages.append(
                      '<div class="user-message">' +
                      billChatEscapeHtml(message.text) +
                      "</div>"
                    );
                  } else if (message.sender === "chatgpt") {
                    let processedText = billChatEscapeHtml(message.text);
                    processedText = processedText.replace(
                      /\*\*(.*?)\*\*/g,
                      "<strong>$1</strong>"
                    );
                    billChatMessages.append(
                      '<div class="chatgpt-message">' + processedText + "</div>"
                    );
                  }
                }
              });
              billChatMessages.scrollTop(billChatMessages[0].scrollHeight);
              $(".spinner999").css("display", "none");
              setTimeout(function () {
                $("#chat-form button").prop("disabled", false);
              }, 2000);
            }
          } else {
            console.error(
              restore_classic_widgets_data.invalid_response_format,
              response
            );
            $(".spinner999").css("display", "none");
            $("#chat-form button").prop("disabled", false);
          }
        } catch (err) {
          console.error(
            restore_classic_widgets_data.response_processing_error,
            err,
            response
          );
          $(".spinner999").css("display", "none");
          $("#chat-form button").prop("disabled", false);
        }
      },
      error: function (xhr, status, error) {
        console.error(
          restore_classic_widgets_data.ajax_error,
          error,
          xhr.responseText
        );
        $(".spinner999").css("display", "none");
        $("#chat-form button").prop("disabled", false);
      },
    });
  }

  $("#chat-form button").on("click", function (e) {
    e.preventDefault();

    billChat_stopAnimationFeature();

    const clickedButtonId = $(this).attr("id");
    const message = billChatInput.val().trim();
    const chatType =
      clickedButtonId === "auto-checkup" || clickedButtonId === "auto-checkup2"
        ? clickedButtonId
        : $("#chat-type").length
          ? $("#chat-type").val()
          : "default";

    if (
      chatType === "auto-checkup" ||
      chatType === "auto-checkup2" ||
      (chatType !== "auto-checkup" &&
        chatType !== "auto-checkup2" &&
        message !== "")
    ) {
      $(".spinner999").css("display", "block");
      $("#chat-form button").prop("disabled", true);
      $.ajax({
        url: restore_classic_widgets_data.ajax_url,
        method: "POST",
        data: {
          action: "restore_classic_widgets_chat_send_message",
          security: restore_classic_widgets_data.reset_nonce, // <-- CORRIGIDO
          message: message,
          chat_type: chatType,
          chat_version: chatVersion,
        },
        timeout: 60000,
        success: function () {
          setTimeout(function () {
            billChatInput.val("");
          }, 2000);
          billChatLoadMessages();
        },
        error: function (xhr, status, error) {
          billChaterrorMessage
            .text(restore_classic_widgets_data.send_error)
            .show();
          $(".spinner999").css("display", "none");
          $("#chat-form button").prop("disabled", false);
          setTimeout(() => billChaterrorMessage.fadeOut(), 5000);
        },
      });
    } else {
      billChaterrorMessage
        .text(restore_classic_widgets_data.empty_message_error)
        .show();
      setTimeout(() => billChaterrorMessage.fadeOut(), 3000);
    }
  });

  setInterval(() => {
    if (billChatMessages.is(":visible")) {
      billChatLoadMessages();
    }
  }, 3000);

  billChatMessages.empty();
});