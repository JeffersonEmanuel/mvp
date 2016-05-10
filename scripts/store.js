$(function () {
  if (typeof store === 'undefined' || store.length === 0) {
    console.log("The shelves are empty today");
    return;
  } else {
    console.log("Come and get your hot " + Object.keys(store).length + " items today!");
  }

  $('.product img').on('click', function (event) {
    if (event.target != this) return;
    event.preventDefault();

    var $product = ($(this).hasClass('product')) ? $(this) : $(this).closest('.product');
    var product = $product.attr('id');
    var category = $(this).closest('.category').attr('id');
    var id = category + '-' + product;

    var $modal = $('div.modal#' + id);
    var $modalButton = $product.find('.open-modal');

    $modalButton.leanModal({
      closeButton: '.close-modal'
    });
    $modalButton.click();
  });

  $('.add-to-cart').on('click', function (event) {
    event.preventDefault();

    $button = $(this);

    var id = $button.closest('.modal').attr('id');
    if (typeof id === 'undefined') {
      console.log("This is not for sale sir");
      return;
    }

    var $input = $button.parent().find('input[name="quantity"]');
    var quantity = $input.val();
    if (typeof quantity === 'undefined') {
      console.log("Sir, you are pointing at a wall");
      return;
    }

    $.get("store/addtocart", {
      id: id,
      quantity: quantity
    })
    .done(function(data) {
      if (data === "OK") {
        if (quantity > 0) {
          console.log("That's a nice " + id + " you got there");
        } else {
          console.log("You will be missed " + id);
        }
        $button.closest('.modal').find('.close-modal').click();
      } else {
        console.log("Well this is embarrassing");
      }
    })
    .fail(function() {
      console.log("That's no store!");
    });
  })
});
