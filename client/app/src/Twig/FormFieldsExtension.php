<?php

namespace OPG\Digideps\Frontend\Twig;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormFieldsExtension extends AbstractExtension
{
    private TranslatorInterface $translator;
    private Environment $environment;

    public function __construct(TranslatorInterface $translator, Environment $environment)
    {
        $this->translator = $translator;
        $this->environment = $environment;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('form_input', [$this, 'renderFormInput']),
            new TwigFunction('form_password', [$this, 'renderPasswordInput']),
            new TwigFunction('form_hidden', [$this, 'renderHiddenInput']),
            new TwigFunction('form_submit', [$this, 'renderFormSubmit']),
            new TwigFunction('form_errors', [$this, 'renderFormErrors']),
            new TwigFunction('form_errors_list', [$this, 'renderFormErrorsList']),
            new TwigFunction('form_select', [$this, 'renderFormDropDown']),
            new TwigFunction('form_known_date', [$this, 'renderFormKnownDate']),
            new TwigFunction('form_sort_code', [$this, 'renderFormSortCode']),
            new TwigFunction('form_checkbox_group', [$this, 'renderCheckboxGroup']),
            new TwigFunction('form_checkbox', [$this, 'renderCheckboxInput']),
            new TwigFunction('form_add_another', [$this, 'renderAddAnother']),
        ];
    }

    /**
     * @param array $options
     *
     * Required options
     * - addAnother: form element this add another component is associated with
     * - thingTranslationKey: the translation key in the yaml translations file for this page representing the "thing"
     *   the user is being asked about (will translate to "decision", "money transfer" etc. in the heading and the
     *   label on the element)
     * - translationDomain: the translation domain containing thingTranslationKey (e.g. "report-decisions")
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderAddAnother(array $options): void
    {
        echo $this->environment->render('@App/Components/Form/_add-another.html.twig', $options);
    }

    /**
     * @DEPRECATED
     * Renders form input field.
     */
    public function renderFormInput(FormView $element, string $elementName, array $vars = [], ?int $transIndex = null): void
    {
        // generate input field html using variables supplied
        echo $this->environment->render(
            '@App/Components/Form/_input.html.twig',
            array_merge(
                $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex),
                ['multiline' => in_array('textarea', $element->vars['block_prefixes'] ?? [])]
            )
        );
    }

    public function renderPasswordInput(FormView $element, array $vars = []): void
    {
        $domain = $element->parent->vars['translation_domain'];
        $vars['label'] = $this->translator->trans('signInForm.password.label', [], $domain);
        $vars['element'] = $element;
        echo $this->environment->render('@App/Components/Form/_password.html.twig', $vars);
    }

    public function renderHiddenInput(FormView $element, array $vars = []): void
    {
        $vars['element'] = $element;
        $vars['value'] = $element->vars['value'];
        echo $this->environment->render('@App/Components/Form/_hidden.html.twig', $vars);
    }

    /**
     * Renders form checkbox field.
     */
    public function renderCheckboxInput(FormView $element, string $elementName, array $vars = [], ?int $transIndex = null): void
    {
        echo $this->environment->render(
            '@App/Components/Form/_checkbox.html.twig',
            array_merge(
                $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex),
                ['type' => in_array('radio', $element->vars['block_prefixes']) ? 'radio' : 'checkbox']
            )
        );
    }

    public function renderCheckboxGroup(FormView $element, string $elementName, array $vars = [], ?int $transIndex = null): void
    {
        // enables getting the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        // sort hint text translation
        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;
        }

        if (isset($vars['legendText'])) {
            $legendText = $vars['legendText'];
        } else {
            // get legendText translation. Look for a .legend value, if there isn't one then try the top level
            $legendTextTrans = $this->translator->trans($translationKey . '.legend', [], $domain);

            if ($legendTextTrans != $translationKey . '.legend') {
                $legendText = $legendTextTrans;
            } else {
                $labelParams = isset($vars['labelParameters']) ? $vars['labelParameters'] : [];
                $legendTextTrans = $this->translator->trans($translationKey . '.label', $labelParams, $domain);
                if ($legendTextTrans != $translationKey . '.label') {
                    $legendText = $legendTextTrans;
                } else {
                    $legendText = null;
                }
            }
        }

        // generate input field html using variables supplied
        echo $this->environment->render('@App/Components/Form/_checkboxgroup.html.twig', [
            'classes' => isset($vars['classes']) ? $vars['classes'] : null,
            'disabled' => isset($vars['disabled']) ? $vars['disabled'] : false,
            'fieldSetClass' => isset($vars['fieldSetClass']) ? $vars['fieldSetClass'] : null,
            'formGroupClass' => isset($vars['formGroupClass']) ? $vars['formGroupClass'] : null,
            'legend' => array_merge([
                'text' => $legendText,
                'isPageHeading' => false,
                'caption' => false,
            ], $vars['legend'] ?? []),
            'legendClass' => isset($vars['legendClass']) ? $vars['legendClass'] : null,
            'useFormGroup' => isset($vars['useFormGroup']) ? $vars['useFormGroup'] : true,
            'hintText' => $hintText,
            'hintLink' => isset($vars['hintLink']) ? $vars['hintLink'] : null,
            'hintList' => empty($vars['hintList']) ? [] : $vars['hintList'],
            'element' => $element,
            'vertical' => isset($vars['vertical']) ? $vars['vertical'] : false,
            'items' => empty($vars['items']) ? [] : $vars['items'],
            'translationDomain' => $domain,
            'multitoggle' => empty($vars['multitoggle']) ? [] : $vars['multitoggle'],
            'extraAttrs' => $vars['extraAttrs'] ?? [],
        ]);
    }

    /**
     * Renders form select element.
     */
    public function renderFormDropDown(FormView $element, string $elementName, array $vars = [], ?int $transIndex = null): void
    {
        // generate input field html using variables supplied
        echo $this->environment->render(
            '@App/Components/Form/_select.html.twig',
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }

    public function renderFormKnownDate(FormView $element, string $elementName, array $vars = [], ?int $transIndex = null): void
    {
        ['translationKey' => $translationKey, 'domain' => $domain] = $this->getTranslationKeyAndDomain($element, $elementName, $transIndex);
        $showDay = $vars['showDay'] ?? 'true';

        // sort hint text translation with default fallback
        /** @var string|null $customHint */
        $customHint = $vars['hintText'] ?? null;
        $hintText = $this->getDateHintText($translationKey, $domain, $customHint);

        // get legendText translation
        /** @var array $legendParams */
        $legendParams = $vars['legendParameters'] ?? [];
        $legendText = $this->getLegendText($translationKey, $legendParams, $domain);

        /** @var array $legend */
        $legend = $vars['legend'] ?? [];

        echo $this->environment->render('@App/Components/Form/_known-date.html.twig', [
            'legend' => $this->buildLegendArray($legendText, $legend),
            'hintTextBold' => $vars['hintTextBold'] ?? null,
            'hintText' => $hintText,
            'element' => $element,
            'showDay' => $showDay,
            'legendTextRaw' => !empty($vars['legendRaw']),
            'required' => $vars['required'] ?? true,
        ]);
    }

    public function renderFormSortCode(FormView $element, string $elementName, array $vars = [], ?int $transIndex = null): void
    {
        // lets get the translation for class and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        // read domain from Form ption 'translation_domain'
        $domain = $element->parent->vars['translation_domain'];

        // sort hint text translation
        $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
        $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;

        // get legendText translation
        $legendTextTrans = $this->translator->trans($translationKey . '.legend', [], $domain);

        $legendText = ($legendTextTrans != $translationKey . '.legend') ? $legendTextTrans : null;

        $html = $this->environment->render('@App/Components/Form/_sort-code.html.twig', [
            'legend' => array_merge([
                'text' => $legendText,
                'isPageHeading' => false,
                'caption' => false,
            ], $vars['legend'] ?? []),
            'hintText' => $hintText,
            'element' => $element,
        ]);
        echo $html;
    }

    /**
     * @param string $elementName used to pick the translation by appending ".label"
     * @param array  $vars        [buttonClass => additional class. "disabled" supported]
     */
    public function renderFormSubmit(
        FormView $element,
        string $elementName,
        array $vars = [],
    ): void {
        $options = [
            // label comes from labelText (if defined, but throws warning) ,or elementname.label from the form translation domain
            'label' => $elementName . '.label',
            'element' => $element,
            'translationDomain' => isset($vars['labelTranslationDomain']) ? $vars['labelTranslationDomain'] : null,
            'buttonClass' => isset($vars['buttonClass']) ? $vars['buttonClass'] : null,
            'attr' => isset($vars['attr']) ? $vars['attr'] : null,
        ];

        // deprecated. only kept in order not to break forms that use it
        if (isset($vars['labelText'])) {
            $options['label'] = $vars['labelText'];
        }

        $html = $this->environment->render('@App/Components/Form/_button.html.twig', $options);

        echo $html;
    }

    /**
     * get individual field errors and render them inside the field
     * Usage: {{ form_errors(element) }}.
     */
    public function renderFormErrors(FormView $element): void
    {
        $html = $this->environment->render('@App/Components/Form/_errors.html.twig', [
            'element' => $element,
        ]);

        echo $html;
    }

    /**
     * get form errors list and render them inside Components/Alerts/error_summary.html.twig
     * Usage: {{ form_errors_list(form) }}.
     */
    public function renderFormErrorsList(FormView $form): void
    {
        $formErrorMessages = $this->getErrorsFromFormViewRecursive($form);

        $html = $this->environment->render('@App/Components/Alerts/_validation-summary.html.twig', [
            'formErrorMessages' => $formErrorMessages,
            'formUncaughtErrors' => empty($form->vars['errors']) ? [] : $form->vars['errors'],
        ]);

        echo $html;
    }

    private function getErrorsFromFormViewRecursive(FormView $elementsFormView): array
    {
        $ret = [];
        foreach ($elementsFormView as $elementFormView) {
            $elementFormErrors = empty($elementFormView->vars['errors']) ? [] : $elementFormView->vars['errors'];
            foreach ($elementFormErrors as $formError) { /* @var $error FormError */
                $ret[] = ['elementId' => $elementFormView->vars['id'], 'message' => $formError->getMessage()];
            }
            $ret = array_merge(
                $ret,
                $this->getErrorsFromFormViewRecursive($elementFormView)
            );
        }

        return $ret;
    }

    /**
     * @return array with vars labelText,labelParameters,hintText,element,labelClass, to pass into twig templates @App:Components/Form:*
     */
    private function getFormComponentTwigVariables(FormView $element, string $elementName, array $vars = [], ?int $transIndex = null): array
    {
        // lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;
        }

        // sort hintList text translation
        $hintListArray = null;
        if (!empty($vars['hasHintList'])) {
            $hintListParams = isset($vars['hintListParameters']) ? $vars['hintListParameters'] : [];
            $hintListTextTrans = $this->translator->trans($translationKey . '.hintList', $hintListParams, $domain);
            $hintListArray = array_filter(explode("\n", $hintListTextTrans));
        }

        // deprecated. Do not use labelText if possible. translation should happen in the view
        if (isset($vars['labelText']) && $vars['labelText']) {
            $labelText = $vars['labelText'];
        } else {
            $labelParams = isset($vars['labelParameters']) ? $vars['labelParameters'] : [];
            // label is translated directly here
            if ('' != $translationKey) {
                $labelText = $this->translator->trans($translationKey . '.label', $labelParams, $domain);
            } else {
                $labelText = '';
            }
        }

        // inputPrefix
        $inputPrefix = isset($vars['inputPrefix']) ? $this->translator->trans($vars['inputPrefix'], [], $domain) : null;

        $labelClass = isset($vars['labelClass']) ? $vars['labelClass'] : null;
        $inputClass = isset($vars['inputClass']) ? $vars['inputClass'] : null;
        $formGroupClass = isset($vars['formGroupClass']) ? $vars['formGroupClass'] : '';

        // Text to insert to the left of an input, e.g. * * * * for account
        $preInputText = null;
        if (!empty($vars['hasPreInput'])) {
            $preInputTextTrans = $this->translator->trans($translationKey . '.preInput', [], $domain);
            $preInputText = $preInputTextTrans;
        }

        return [
            'labelDataTarget' => empty($vars['labelDataTarget']) ? null : $vars['labelDataTarget'],
            'labelText' => $labelText,
            'hintText' => $hintText,
            'hintListArray' => $hintListArray,
            'element' => $element,
            'labelClass' => $labelClass,
            'inputClass' => $inputClass,
            'inputPrefix' => $inputPrefix,
            'useFormGroup' => $vars['useFormGroup'] ?? true,
            'dataModule' => $vars['dataModule'] ?? false,
            'formGroupClass' => $formGroupClass,
            'labelRaw' => !empty($vars['labelRaw']),
            'labelLink' => !empty($vars['labelLink']),
            'preInputText' => $preInputText,
            'jsEnabled' => $vars['jsEnabled'] ?? '',
            'label' => array_merge([
                'text' => $labelText,
                'isPageHeading' => false,
                'caption' => false,
            ], $vars['label'] ?? []),
            'extraAttrs' => $vars['extraAttrs'] ?? [],
        ];
    }

    /**
     * Extract hint text for date fields with optional override and default fallback.
     * Tries custom hint first, then translates from hint key, then falls back to default hint text.
     *
     * @param string $translationKey The translation key prefix
     * @param string $domain The translation domain
     * @param string|null $customHint Optional custom hint text to use instead of translation
     * @return string The hint text or default date hint if no translation found
     */
    private function getDateHintText(string $translationKey, string $domain, ?string $customHint = null): string
    {
        // Use custom hint if provided
        if (null !== $customHint) {
            return $customHint;
        }

        // Try to get hint text translation
        $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
        if ($hintTextTrans !== $translationKey . '.hint') {
            return $hintTextTrans;
        }

        // Fall back to default date hint text
        return $this->translator->trans('defaultDateHintText', [], 'common');
    }

    /**
     * Extract legend text from translation, falling back to label if legend is not available.
     *
     * @param string $translationKey The translation key prefix
     * @param array $labelParams Parameters for translation
     * @param string $domain The translation domain
     * @return string|null The translated legend text or null if neither legend nor label exists
     */
    private function getLegendText(string $translationKey, array $labelParams, string $domain): ?string
    {
        // Try to get legend translation first
        $legendTextTrans = $this->translator->trans($translationKey . '.legend', $labelParams, $domain);

        if ($legendTextTrans !== $translationKey . '.legend') {
            return $legendTextTrans;
        }

        // Fall back to label translation if legend doesn't exist
        $labelTextTrans = $this->translator->trans($translationKey . '.label', $labelParams, $domain);

        if ($labelTextTrans !== $translationKey . '.label') {
            return $labelTextTrans;
        }

        return null;
    }

    /**
     * Build the legend array structure used across multiple form components.
     *
     * @param string|null $legendText The legend text to display
     * @param array $customLegend Optional custom legend values to merge
     * @return array The formatted legend array
     */
    private function buildLegendArray(?string $legendText, array $customLegend = []): array
    {
        return array_merge([
            'text' => $legendText,
            'isPageHeading' => false,
            'caption' => false,
        ], $customLegend);
    }

    /**
     * Extract translation key and domain from FormView and parameters.
     * Handles optional transaction index for nested translations.
     *
     * @param FormView $element The form element
     * @param string $elementName The element name
     * @param int|null $transIndex Optional transaction index for nested translations
     * @return array{'translationKey':string, 'domain':string} containing [translationKey, domain]
     */
    private function getTranslationKeyAndDomain(FormView $element, string $elementName, ?int $transIndex = null): array
    {
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        /** @var string $domain */
        $domain = $element->parent->vars['translation_domain'];
        return ['translationKey' => $translationKey, 'domain' => $domain];
    }

    public function getName(): string
    {
        return 'form_input_extension';
    }
}
