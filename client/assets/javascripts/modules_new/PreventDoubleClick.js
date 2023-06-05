const PreventDoubleClick = {
  init: function (document) {
    // Get all elements with the class "govuk-button"
    var buttons = document.getElementsByClassName("govuk-button");

    // Loop through each button element and apply the attribute
    for (var i = 0; i < buttons.length; i++) {
      buttons[i].setAttribute("data-prevent-double-click", "true");
    }
  }
}

export default PreventDoubleClick
