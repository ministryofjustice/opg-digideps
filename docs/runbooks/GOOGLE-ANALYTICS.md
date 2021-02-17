# Google Analytics

## Event Tracking
We use Google Analytics to track events within the app to highlight patterns in user behaviour. This can be used to analyse or evaluate a change made to a page, for example:

 - Do additional form fields result in a drop in the amount of users submitting a form?
 - Has changes to the style of a button resulted in more people clicking it?
 - Do users scroll all the way to the bottom of the page or just click the continue button?

 Google Analytics accepts a number of data points related to an event:

 #### Category (required)
 Describes the page, module or component the event belongs to.

 #### Action (required)
 Describes the action taken by a user along with contextual detail.

 #### Label (optional)
 Provides additional information related to the event (link destination URL, form field name, button text, scroll percentage of page etc.).

  #### Value (optional)
  Assigns a numerical value to an event

  (thanks to https://mixedanalytics.com/blog/event-tracking-naming-strategy-for-google-analytics/ for the inspiration for the above)

### Naming Matrix
In order to be consistent with how we track and categorise events we have a predefined list of values for actions, categories and labels. Add new entries if the event you are creating doesn't fall under the existing values below:

#### Categories

| Category | Description |
|---|---|
| Lay Registration Form | Events taking place on the Lay registration form page |

#### Actions

| Action | Description |
|---|---|
| Clicked Sign Up Button | User clicked the sign up button |

### Click Tracking
In order to add click event tracking to an element on a page, add the following attributes:

     'data-attribute': 'ga-event',
     'data-action': '<GA ACTION VALUE>',
     'data-category': '<GA CATEGORY VALUE>',
     'data-label': '<GA LABEL VALUE>' (optional)
     'data-value': '<GA VALUE VALUE>' (optional)

If the element we want to track is a form submit button the values above can be provided to the twig `form_submit` helper function:

```twig
{{ form_start(form, {attr: {novalidate: 'novalidate', class: formClass } }) }}

    {{ form_input(form.firstname,'firstname') }}
    {{ form_input(form.lastname,'lastname') }}
    ...

{{ form_submit(form.save, 'save', {}, 'Form submitted', 'User Journeys', 'Button clicks', 1)
```
