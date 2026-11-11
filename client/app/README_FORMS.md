# FormType

For each form, create a "FormType" as before, except that you extend `OPG\Digideps\Frontend\Form\Component\AbstractFormType` instead of `\Symfony\Component\Form\AbstractType`.

`AbstractFormType` requires you to:
- Define the translation domain of **all** translations in the form (including validation errors)
- Define a prefix for the validation key (This is prefixed to keys you pass later. Note that a dot is added for you)
- Define an array of `OPG\Digideps\Frontend\Form\Component\AbstractInput`
  - All possible options are in the `OPG\Digideps\Frontend\Form\Component` namespace
  - Refer to the constructors of each input type for possible configurations

Inside the controller call `->createRenderableForm`. This will return a utility object which contains the Symfony form internally. Usually you will use these methods on that object:

- `->handleRequest(Request $request): bool` which will read the request content if the http method implies form submission and return true if and only if the form is submitted and valid
- `->render()` returns an object implementing RenderableInterface (wrapper around FormView)
- `->recreate()` creates a copy of the form with all fields blanked
- `->addError(FormError $error)` add form level error
- All methods as found in `ValidatingForm` are directly available to extract data

# Inputs

To define a new input type:
- Create a new class implementing RenderableInterface (and the relevant twig template) under `OPG\Digideps\Frontend\Components` which renders the input. This should contain as little logic as possible.
- Create a new class under `OPG\Digideps\Frontend\Form\Component` extending `OPG\Digideps\Frontend\Form\Component\AbstractInput` (or `AbstractRepeatedInput`).
