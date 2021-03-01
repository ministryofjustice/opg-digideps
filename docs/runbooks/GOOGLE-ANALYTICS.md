# Google Analytics

## Event Tracking
We use Google Analytics to track events within the app to highlight patterns in user behaviour. This can be used to analyse or evaluate a change made to a page, for example:

 - Do additional form fields result in a drop in the amount of users submitting a form?
 - Has changes to the style of a button resulted in more people clicking it?
 - Do users scroll all the way to the bottom of the page or just click the continue button?

 Google Analytics accepts a number of data points related to an event:

 #### Category (required)
 Describes the page, module or component the event belongs to.

 Use the format `{Page Title}:{Sub Section i.e. in a form, optional}`.

 e.g. Category: Finances: Outgoings

 #### Action (required)
 Describes the action taken by a user along with contextual detail.

 Use the format `{event}:{ Element Type}:{Element Specifics}`.

e.g. Click: Button: Sign Up

 #### Label (optional)
 Provides additional information related to the event (link destination URL, form field name, button text, scroll percentage of page etc.).

 Use the format `{Human summary and additional detail} {path uri with any query params}`.

e.g. Clicked link to help page /help?from=homepage

 #### Value (optional)
 Assigns a numerical value to an event

  (thanks to https://mixedanalytics.com/blog/event-tracking-naming-strategy-for-google-analytics/ for the inspiration for the above)

### Click Tracking
In order to add click event tracking to an element on a page, add the following attributes:

     'data-attribute': 'ga-event',
     'data-ga-action': '<GA ACTION VALUE>',
     'data-ga-category': '<GA CATEGORY VALUE>',
     'data-ga-label': '<GA LABEL VALUE>'
     'data-ga-value': '<GA VALUE VALUE>' (optional)

If the element we want to track is a form submit button the values above can be provided to the twig `form_submit_ga` [helper function](client/src/Twig/FormFieldsExtension.php):

The category, action and label should be assigned to variables using gaCategory, gaAction and gaLabel respectively to make searching and maintenance easier.

e.g.

```twig
    {% set gaCategory = 'pageTitle.registration-details' | trans %}
    {% set gaAction = 'Click: Button: Save User Details' %}
    {% set gaLabel = 'Clicked save user details button on ' ~ app.request.requesturi %}

    {{ form_start(form, {attr: {novalidate: 'novalidate', class: formClass } }) }}

        {{ form_input(form.firstname,'firstname') }}
        {{ form_input(form.lastname,'lastname') }}
        ...

        {{ form_submit_ga(
            form.save,
            'form.editYourDetails.controls.save',
            gaCategory,
            gaAction,
            gaLabel,
            null,
            {'buttonClass': 'behat-link-save'})
        }}

    {{ form_end }}
```
