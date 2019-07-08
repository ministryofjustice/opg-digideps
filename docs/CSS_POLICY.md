# CSS Policy

We use the [GOVUK.UK Design System][govuk-ds] to provide as much as our styling as possible:

- To benefit from their accessibility, performance and usability concerns
- To be in-line with other GOV.UK services
- To reduce our maintenance requirements
- To be compliant with GDS standards

Where possible, **GOV.UK Design System components and patterns should be used**.

## Custom app CSS

Where the GOV.UK Design System does not meet our needs, we _may_ write custom CSS for the DigiDeps app. When doing so, the following rules should be followed:

- Reusable components should be designed as Twig templates or macros, so they can easily be updated in the future
  - Each component should have its own SCSS file, only containing classes related to that component. This makes CSS easy to find and transport.
- CSS should use classes, rather than IDs, attributes or tag selectors
- All custom CSS classes should be prefixed `opg-`, to differentiate them from GOV.UK classes which are prefixed `govuk-`
- [Block, Element, Modifier (BEM)][bem] CSS methodology should be used
- Where suitable (e.g. typography, colours, spacing) [GOV.UK Sass variables][govuk-ds-variables] should be used

These rules keep our custom CSS close to the GOV.UK Design System, making code easier to review and maintain, and easing our route to new components when they are released.

### Linting

We lint our custom CSS to ensure that it meets our standards. This is reported during the Gulp build process, but it's generally easier to enable linting in your code editor so you get feedback in realtime.

You need to enable a linted which recognises the `.sass-lint.yml` configuration file. Examples:

- [Atom][atom-linter]
- [Sublime Text][sublime-text-linter]
- [VS Code][vs-code-linter]

## Specific cases

### Radio buttons with revealed content

In some situations, we use radio buttons to conditionally reveal content ([GOV.UK Design System example][conditional-radios]). Previously we defined all the revealed content under the radio buttons, and passed an ID to the radio button/checkbox template.

This functionality is now properly supported by the GOV.UK Design System, and requires the conditional content to be _between_ the radio buttons. To enable this, the conditional content should be defined as a Twig variable and that variable should be passed to the template with the `conditional` setting:

```twig
{% set conditional_email_address %}
    {{ form_input(form.email, (page ~ '.form.email')) }}
{% endset %}

{% set conditional_phone %}
    {{ form_input(form.phone, (page ~ '.form.phone')) }}
{% endset %}

{{ form_checkbox_group(form.howContact, (page ~ '.form.howContact'), {
    'legendText' : (page ~ '.form.howContact.label') | trans(transOptions, translationDomain),
    'items': [
        {'conditional': conditional_email_address},
        {'conditional': conditional_phone}
    ]
}) }}
```

[govuk-ds]: https://design-system.service.gov.uk/
[govuk-ds-variables]: https://github.com/alphagov/govuk-frontend/tree/master/src/settings
[bem]: https://css-tricks.com/bem-101/
[conditional-radios]: https://design-system.service.gov.uk/components/radios/#conditionally-revealing-content
[atom-linter]: https://atom.io/packages/linter-sass-lint
[sublime-text-linter]: https://packagecontrol.io/packages/SublimeLinter-contrib-sass-lint
[vs-code-linter]: https://marketplace.visualstudio.com/items?itemName=glen-84.sass-lint
