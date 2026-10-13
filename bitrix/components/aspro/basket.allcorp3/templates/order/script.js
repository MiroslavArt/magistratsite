$(document).ready(function () {
  $(".btn-pay").on("click", function () {
    $(this).siblings(".payment-info").slideToggle();
  });
});
