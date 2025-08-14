jQuery(document).ready(function ($) {
  //      30/04/2024

  console.log("bi");

  function sendSecondRequest(result, slug, main_slug) {
    // result next versions...
    //var main_slug = jQuery('#main_slug').val();
    //console.log(slug);
    //console.log(main_slug);
    //console.log(result);

    jQuery.ajax({
      url: "https://billminozzi.com/httpapi/httpapi.php",
      withCredentials: true,
      timeout: 15000,
      method: "POST",
      crossDomain: true,
      data: {
        status: result, // Passa o resultado da primeira solicitação
        slug: slug,
        main_slug: main_slug,
      },
      complete: function (response) {
        // console.log(response);
      },
    }); // end ajax
  }

  // Dismiss
  jQuery(".bill-dismiss-one-hour").click(function (e) {
    e.preventDefault();

    const adminUrl = document
      .querySelector(".bill-dismiss-one-hour")
      .getAttribute("data-admin-url");

    if (adminUrl !== null) {
      //console.log(adminUrl);
      if (adminUrl && adminUrl.includes("/undefined")) {
        adminUrl = adminUrl.replace("/undefined", "");
      }
      //console.log(adminUrl);
    }

    var nonce = jQuery("#nonce").val();

    //alert();

    jQuery.ajax({
      url: ajaxurl,
      type: "POST", // Defina o tipo de solicitação como POST
      data: {
        action: "restore_classic_widgets_dismiss_pre_checkup_handler",
        nonce: nonce, // Adicione o nonce aos dados que você está enviando
      },
      success: function (response) {
        console.log(response);
        $(".bill-installation-msg").remove();

        const currentPageURL = window.location.href;
        if (
          currentPageURL.includes("page=restore_classic_widgets_pre-checkup")
        ) {
          window.location.href = adminUrl; // + "?page=easy_update_urls_admin_page";
        } else {
          window.location.reload();
        }
      },
    });
  });

  // Finished
  jQuery(".bill-install-finished").click(function (e) {
    e.preventDefault();
    //alert('clicou Finished');

    var adminUrl = $("#data-admin-url-finished").val();

    // console.log(adminUrl);

    var nonce = jQuery("#nonce").val();
    var main_slug = jQuery("#main_slug").val();

    $.ajax({
      url: ajaxurl, // Substitua com a URL correta para sua ação AJAX
      type: "POST", // Defina o tipo de solicitação como POST
      data: {
        action: "restore_classic_widgets_finished_pre_checkup_handler",
        nonce: nonce,
      },
      success: function (response) {
        // console.log(response);

        sendSecondRequest(response, " ", main_slug);
        alert("Thank you for installing!");
        window.location.href = adminUrl;
      },
      error: function (xhr, status, error) {
        console.error("Erro ao Finalizar:", error);
        console.error("Erro ao Finalizar:", status);
        console.error("Erro ao Finalizar:", xhr);

        alert("Error, please, try again later!");
      },
    });
  });

  // Install wp Memory
  jQuery(".bill-install-plugin-now").click(function (e) {
    e.preventDefault();
    // Mostrar o botão de espera e esconder o botão "Install WPmemory Free"
    jQuery("#loading-spinner").show();
    jQuery("#bill-install-wpmemory").hide();
    var nonce = jQuery("#nonce").val();
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "restore_classic_widgets_install_plugin",
        slug: "wp-memory",
        nonce: nonce,
      },
      success: function (response) {
        var main_slug = jQuery("#main_slug").val();
        var slug = "wp-memory";

        sendSecondRequest(response, slug, main_slug);
        console.log(response);
        if (response.trim() === "OK") {
          alert("Plugin WPmemory Installed Successfully.");
        }
      },
      error: function (xhr, status, error) {
        console.error("Erro ao instalar o plugin:", error);
        alert(
          "Ocorreu um erro ao instalar o plugin. Por favor, tente novamente mais tarde."
        );
      },
      complete: function () {
        jQuery("#loading-spinner").prop("disabled", true);
        jQuery("#loading-spinner").text("Installed");
      },
    });
  });

  // install plugin wptools
  jQuery(".bill-install-wpt-plugin-now").click(function (e) {
    e.preventDefault();
    jQuery("#loading-spinner").show();
    jQuery("#bill-install-wptools").hide();
    var nonce = jQuery("#nonce").val();
    // alert(nonce);
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "restore_classic_widgets_install_plugin",
        slug: "wptools",
        nonce: nonce,
      },
      success: function (response) {
        console.log(response);
        var main_slug = jQuery("#main_slug").val();
        var slug = "wptools";
        sendSecondRequest(response, slug, main_slug);
        if (response.trim() === "OK") {
          alert("Plugin WPtools Installed Successfully.");
        }
      },
      error: function (xhr, status, error) {
        console.error("Erro ao instalar o plugin:", error);
        alert(
          "Ocorreu um erro ao instalar o plugin. Por favor, tente novamente mais tarde."
        );
      },
      complete: function () {
        jQuery("#loading-spinner").prop("disabled", true);
        jQuery("#loading-spinner").text("Installed");
      },
    });
  });

  // bill-install-now
  jQuery(".bill-install-now").click(function (e) {
    e.preventDefault();

    // bill-wrap-install
    jQuery("#bill-wrap-install-modal").show();

    jQuery("#loading-spinner").show();
    jQuery(".bill-install-now").hide();

    var main_slug = jQuery("#main_slug").val();
    var clickedButtonId = this.id;
    var slug = clickedButtonId.substring(1);

    $("#billimagewaitfbl").show();

    $billmodal = $("#bill-wrap-install");
    //console.log($billmodal);
    $billmodal.prependTo($("#wpcontent")).slideDown();
    $("html, body").scrollTop(0);

    $("#billpluginslugModal").html(slug);
    var nonce = jQuery("#nonce").val();

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "restore_classic_widgets_install_plugin",
        slug: slug,
        nonce: nonce,
      },
      success: function (response) {
        //var main_slug = jQuery('#main_slug').val();
        // slug = clickedButtonId;
        // remove underline...
        slug = clickedButtonId.substring(1);

        sendSecondRequest(response, clickedButtonId, main_slug);

        console.log(response);
        if (response.trim() === "OK") {
          alert("Plugin " + slug + " Installed Successfully.");
          jQuery(".bill-install-now").show();
        }
        // window.location.reload();
      },
      error: function (xhr, status, error) {
        console.error("Erro ao instalar o plugin:", error);
        alert(
          "Ocorreu um erro ao instalar o plugin. Por favor, tente novamente mais tarde."
        );
      },
      complete: function () {
        slug = clickedButtonId.substring(1);
        jQuery("#loading-spinner").prop("disabled", true);
        jQuery("#loading-spinner").text("Installed");
        alert("Plugin " + slug + " Installed Successfully.");
        window.location.reload();
      },
    });
  });
});
