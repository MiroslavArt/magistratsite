const readyBasketHandler = function () {
  const ajaxBasket = document.querySelector(".ajax_basket");

  document.addEventListener("click", function (e) {
    if (typeofExt(e.target.closest("#close-basket")) !== "null") {
      ajaxBasket.classList.remove("opened");
    }
  });
};

document.addEventListener("DOMContentLoaded", readyBasketHandler);
