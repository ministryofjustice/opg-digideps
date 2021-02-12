module.exports = function () {
  // - Find element with data-attribute = ga-event
  // - Get values of data-label, data-category, data-action, data-value
  // - Write macro to create element with the data attributes included - hard code category based on macroname?
  // - Add onclickevent with relevant ga code to element:
  //
  // gtag('event', <action>, {
  //     'event_category': <category>,
  //     'event_label': <label>,
  //     'value': <value>
  //     });
}
