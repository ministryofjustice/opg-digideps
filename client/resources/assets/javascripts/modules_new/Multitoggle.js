// only works for yes/no radio buttons;
// see client/app/templates/Report/Lifestyle/step.html.twig for an example
module.exports = {
  init: function(document) {
    document.addEventListener('click', function (event) {
      let elem = event.target

      if (elem && elem.matches('input[type="radio"][data-multitoggle-on],input[type="radio"][data-multitoggle-off]')) {
        console.log('MULTITOGGLE')
        console.log(elem)
      }
    })
  }
}
