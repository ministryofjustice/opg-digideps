/**
 * A component which makes one input/textarea optional or required,
 * depending on whether another input/textarea has a value set.
 *
 * The target field should not be marked with required=false, as the required
 * attribute and "optional" label are handled by this component.
 *
 * See breakdown.html.twig for an example of usage.
 */
const ToggleRequired = {
  init: function(document) {
    const toggleRequireds = document.querySelectorAll('.js-toggle-required')

    toggleRequireds.forEach(toggleRequired => {
      const triggerElement = toggleRequired.querySelector('.js-toggle-required-trigger')
      const targetElement = toggleRequired.querySelector('.js-toggle-required-target')
      const optionalLabelSuffix = toggleRequired.getAttribute('data-optional-label-suffix')
      const requiredMessage = toggleRequired.getAttribute('data-required-message')
      const optionalMessage = toggleRequired.getAttribute('data-optional-message')

      if (!(triggerElement) || !targetElement || !optionalLabelSuffix || !requiredMessage || !optionalMessage) {
        console.error("ERROR: ToggleRequired element is misconfigured")
        return
      }

      // span element inside the label for the "(optional)" suffix;
      // this is added/removed rather than hidden, as hiding it will mean
      // it is still visible to assistive technologies
      let optionalSpan = document.createElement('span')
      optionalSpan.innerText = optionalLabelSuffix.trim()

      // aria-live area for notifying assistive technologies when the
      // required/optional status of the target field changes;
      // this is permanently included on the page
      let toggleMessage = document.createElement('span')
      toggleMessage.setAttribute('aria-live', 'polite')
      toggleMessage.classList.add('visually-hidden')

      targetElement.appendChild(toggleMessage)

      targetElement.toggleRequiredConfig = {
        optionalSpan: optionalSpan,
        toggleMessage: toggleMessage,
        requiredMessage: requiredMessage,
        optionalMessage: optionalMessage
      }

      const handler = this.makeHandler(triggerElement, targetElement)

      triggerElement.addEventListener('input', handler)
      triggerElement.addEventListener('paste', handler)
      triggerElement.addEventListener('change', handler)

      // set starting required attribute and optional label on target element
      handler()
    })
  },

  // if the triggerElement has a value, add the required attribute to the
  // targetElement;
  // if it has no value, show "(optional)" in the targetElement's label and
  // remove the required attribute
  makeHandler: function(triggerElement, targetElement) {
    const labelElement = targetElement.querySelector('label')
    const targetInputElement = targetElement.querySelector('input, textarea')
    const config = targetElement.toggleRequiredConfig

    return function() {
      const greaterThanZero = parseFloat(triggerElement.value.trim()) > 0

      if (greaterThanZero) {
        // required: remove the "optional" label suffix
        if (labelElement.contains(config.optionalSpan)) {
          labelElement.removeChild(config.optionalSpan)
        }

        targetInputElement.setAttribute('required', 'required')

        // set required message in aria-live area
        config.toggleMessage.textContent = config.requiredMessage
      } else {
        // optional: add the "optional" label suffix
        if (!labelElement.contains(config.optionalSpan)) {
          labelElement.appendChild(config.optionalSpan)
        }

        targetInputElement.removeAttribute('required')

        // set optional message in aria-live area
        config.toggleMessage.textContent = config.optionalMessage
      }
    }
  }
}

export default ToggleRequired
