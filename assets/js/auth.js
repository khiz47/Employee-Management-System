$(document).ready(function () {
  $("#loginForm").on("submit", function (e) {
    e.preventDefault();

    const form = $(this);
    const button = form.find("button");
    const alertBox = $("#loginAlert");

    alertBox.html("");
    button.prop("disabled", true).text("Signing in...");

    $.ajax({
      url: "includes/functions.php",
      type: "POST",
      dataType: "json",
      data: {
        action: "login",
        email: form.find('[name="email"]').val(),
        password: form.find('[name="password"]').val(),
      },
      success: function (res) {
        if (res.status) {
          window.location.href = res.data.redirect;
        } else {
          alertBox.html(`<div class="alert alert-danger">${res.message}</div>`);
        }
      },
      error: function () {
        alertBox.html(
          `<div class="alert alert-danger">Something went wrong. Please try again.</div>`,
        );
      },
      complete: function () {
        button.prop("disabled", false).text("Sign In");
      },
    });
  });
});
