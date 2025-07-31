<?php

namespace App\Twig;

use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
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

    public function getFunctions()
    {
        return [
            new TwigFunction('form_input', [$this, 'renderFormInput']),
            new TwigFunction('form_submit', [$this, 'renderFormSubmit']),
            new TwigFunction('form_submit_ga', [$this, 'renderGATrackedFormSubmit']),
            new TwigFunction('form_errors', [$this, 'renderFormErrors']),
            new TwigFunction('form_errors_list', [$this, 'renderFormErrorsList']),
            new TwigFunction('form_select', [$this, 'renderFormDropDown']),
            new TwigFunction('form_known_date', [$this, 'renderFormKnownDate']),
            new TwigFunction('form_sort_code', [$this, 'renderFormSortCode']),
            new TwigFunction('form_checkbox_group', [$this, 'renderCheckboxGroup']),
            new TwigFunction('form_checkbox', [$this, 'renderCheckboxInput']),
        ];
    }

    /**
     * @DEPRECATED
     * Renders form input field.
     *
     * @param string $elementName
     * @param int    $transIndex
     */
    public function renderFormInput($element, $elementName, array $vars = [], $transIndex = null)
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

    /**
     * Renders form checkbox field.
     *
     * @param string $elementName
     * @param int    $transIndex
     */
    public function renderCheckboxInput($element, $elementName, array $vars = [], $transIndex = null)
    {
        echo $this->environment->render(
            '@App/Components/Form/_checkbox.html.twig',
            array_merge(
                $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex),
                ['type' => in_array('radio', $element->vars['block_prefixes']) ? 'radio' : 'checkbox']
            )
        );
    }

    /**
     * @DEPRECATED
     *
     * //TODO consider refactor using getFormComponentTwigVariables
     */
    public function renderCheckboxGroup(FormView $element, $elementName, $vars, $transIndex = null)
    {
        // lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex.'.'.$elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        // sort hint text translation
        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey.'.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey.'.hint') ? $hintTextTrans : null;
        }

        if (isset($vars['legendText'])) {
            $legendText = $vars['legendText'];
        } else {
            // get legendText translation. Look for a .legend value, if there isn't one then try the top level
            $legendTextTrans = $this->translator->trans($translationKey.'.legend', [], $domain);

            if ($legendTextTrans != $translationKey.'.legend') {
                $legendText = $legendTextTrans;
            } else {
                $labelParams = isset($vars['labelParameters']) ? $vars['labelParameters'] : [];
                $legendTextTrans = $this->translator->trans($translationKey.'.label', $labelParams, $domain);
                if ($legendTextTrans != $translationKey.'.label') {
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
            'element' => $element,
            'vertical' => isset($vars['vertical']) ? $vars['vertical'] : false,
            'items' => empty($vars['items']) ? [] : $vars['items'],
            'translationDomain' => $domain,
            'multitoggle' => empty($vars['multitoggle']) ? [] : $vars['multitoggle'],
        ]);
    }

    /**
     * @DEPRECATED
     */
    public function renderCheckboxGroupNew(FormView $element, $elementName, $vars, $transIndex = null)
    {
        // lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex.'.'.$elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        // sort hint text translation
        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey.'.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey.'.hint') ? $hintTextTrans : null;
        }

        if (isset($vars['legendText'])) {
            $legendText = $vars['legendText'];
        } else {
            // get legendText translation. Look for a .legend value, if there isn't one then try the top level
            $legendTextTrans = $this->translator->trans($translationKey.'.legend', [], $domain);

            if ($legendTextTrans != $translationKey.'.legend') {
                $legendText = $legendTextTrans;
            } else {
                $labelParams = isset($vars['labelParameters']) ? $vars['labelParameters'] : [];
                $legendTextTrans = $this->translator->trans($translationKey.'.label', $labelParams, $domain);
                if ($legendTextTrans != $translationKey.'.label') {
                    $legendText = $legendTextTrans;
                } else {
                    $legendText = null;
                }
            }
        }

        // generate input field html using variables supplied
        echo $this->environment->render('@App/Components/Form/_checkboxgroup_new.html.twig', [
            'fieldSetClass' => isset($vars['fieldSetClass']) ? $vars['fieldSetClass'] : null,
            'legendText' => $legendText,
            'legendClass' => isset($vars['legendClass']) ? $vars['legendClass'] : null,
            'hintText' => $hintText,
            'element' => $element,
            'vertical' => isset($vars['vertical']) ? $vars['vertical'] : false,
            'items' => empty($vars['items']) ? [] : $vars['items'],
            'translationDomain' => $domain,
        ]);
    }

    /**
     * Renders form select element.
     *
     * @param string $elementName
     * @param int    $transIndex
     */
    public function renderFormDropDown($element, $elementName, array $vars = [], $transIndex = null)
    {
        // generate input field html using variables supplied
        echo $this->environment->render(
            '@App/Components/Form/_select.html.twig',
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }

    public function renderFormKnownDate($element, $elementName, array $vars = [], $transIndex = null)
    {
        // read domain from Form ption 'translation_domain'
        $domain = $element->parent->vars['translation_domain'];

        $translationKey = (!is_null($transIndex)) ? $transIndex.'.'.$elementName : $elementName;

        if (isset($vars['showDay'])) {
            $showDay = $vars['showDay'];
        } else {
            $showDay = 'true';
        }

        // sort hint text translation
        $hintTextTrans = $this->translator->trans($translationKey.'.hint', [], $domain);
        if (isset($vars['hintText']) && !empty($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } elseif ($hintTextTrans !== $translationKey.'.hint') {
            $hintText = $hintTextTrans;
        } else {
            $hintText = $this->translator->trans('defaultDateHintText', [], 'common');
        }

        // get legendText translation
        $legendParams = isset($vars['legendParameters']) ? $vars['legendParameters'] : [];

        $legendTextTrans = $this->translator->trans($translationKey.'.legend', $legendParams, $domain);

        if ($legendTextTrans != $translationKey.'.legend') {
            $legendText = $legendTextTrans;
        } else {
            // the
            $legendTextTrans = $this->translator->trans($translationKey.'.label', $legendParams, $domain);
            if ($legendTextTrans != $translationKey.'.label') {
                $legendText = $legendTextTrans;
            } else {
                $legendText = null;
            }
        }

        $html = $this->environment->render('@App/Components/Form/_known-date.html.twig', [
            'legend' => array_merge([
                'text' => $legendText,
                'isPageHeading' => false,
                'caption' => false,
            ], $vars['legend'] ?? []),
            'hintTextBold' => $vars['hintTextBold'] ?? null,
            'hintText' => $hintText,
            'element' => $element,
            'showDay' => $showDay,
            'legendTextRaw' => !empty($vars['legendRaw']), ]);
        echo $html;
    }

    public function renderFormSortCode($element, $elementName, array $vars = [], $transIndex = null)
    {
        // lets get the translation for class and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex.'.'.$elementName : $elementName;
        // read domain from Form ption 'translation_domain'
        $domain = $element->parent->vars['translation_domain'];

        // sort hint text translation
        $hintTextTrans = $this->translator->trans($translationKey.'.hint', [], $domain);
        $hintText = ($hintTextTrans != $translationKey.'.hint') ? $hintTextTrans : null;

        // get legendText translation
        $legendTextTrans = $this->translator->trans($translationKey.'.legend', [], $domain);

        $legendText = ($legendTextTrans != $translationKey.'.legend') ? $legendTextTrans : null;

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
        $element,
        $elementName,
        array $vars = [],
    ) {
        $options = [
            // label comes from labelText (if defined, but throws warning) ,or elementname.label from the form translation domain
            'label' => $elementName.'.label',
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
     * @param string      $elementName        used to pick the translation by appending ".label"
     * @param array       $vars               [buttonClass => additional class. "disabled" supported]
     * @param string|null $gaTrackingCategory (required) Use the format {Page Title}:{Sub Section i.e. in a form} (sub section optional)
     * @param string|null $gaTrackingAction   (required) Use the format {event}: { Element Type}: {Element Specifics}
     * @param string|null $gaTrackingLabel    (required) Use the format {Human summary and additional detail} {path uri with any query params}
     * @param int|null    $gaTrackingValue    (optional) a numerical value that related to the event
     *
     * See GOOGLE-ANALYTICS.md for usage
     */
    public function renderGATrackedFormSubmit(
        $element,
        $elementName,
        string $gaTrackingCategory,
        string $gaTrackingAction,
        ?string $gaTrackingLabel = null,
        ?int $gaTrackingValue = null,
        array $vars = [],
    ) {
        $vars['attr'] = $this->addGaAttrsToElementAttrs(
            $gaTrackingCategory,
            $gaTrackingAction,
            $gaTrackingLabel,
            $gaTrackingValue,
            $vars['attr'] ?? []
        );

        return $this->renderFormSubmit($element, $elementName, $vars);
    }

    /**
     * @return array
     */
    private function addGaAttrsToElementAttrs(
        string $gaTrackingCategory,
        string $gaTrackingAction,
        string $gaTrackingLabel,
        ?int $gaTrackingValue,
        ?array $attrs = [],
    ) {
        $attrs = is_null($attrs) ? [] : $attrs;

        $gaTrackingAttrs = [
            'data-attribute' => 'ga-event',
            'data-ga-action' => $gaTrackingAction,
            'data-ga-category' => $gaTrackingCategory,
            'data-ga-label' => $gaTrackingLabel,
            'data-ga-value' => strval($gaTrackingValue),
        ];

        return array_merge($attrs, $gaTrackingAttrs);
    }

    /**
     * get individual field errors and render them inside the field
     * Usage: {{ form_errors(element) }}.
     */
    public function renderFormErrors($element)
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
    public function renderFormErrorsList(FormView $form)
    {
        $formErrorMessages = $this->getErrorsFromFormViewRecursive($form);

        $html = $this->environment->render('@App/Components/Alerts/_validation-summary.html.twig', [
            'formErrorMessages' => $formErrorMessages,
            'formUncaughtErrors' => empty($form->vars['errors']) ? [] : $form->vars['errors'],
        ]);

        echo $html;
    }

    /**
     * @return array
     */
    private function getErrorsFromFormViewRecursive(FormView $elementsFormView)
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
     * @param FormView    $element
     * @param string      $elementName
     * @param string|null $transIndex
     *
     * @return array with vars labelText,labelParameters,hintText,element,labelClass, to pass into twig templates @App:Components/Form:*
     */
    private function getFormComponentTwigVariables($element, $elementName, array $vars, $transIndex)
    {
        // lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex.'.'.$elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey.'.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey.'.hint') ? $hintTextTrans : null;
        }

        // sort hintList text translation
        $hintListArray = null;
        if (!empty($vars['hasHintList'])) {
            $hintListParams = isset($vars['hintListParameters']) ? $vars['hintListParameters'] : [];
            $hintListTextTrans = $this->translator->trans($translationKey.'.hintList', $hintListParams, $domain);
            $hintListArray = array_filter(explode("\n", $hintListTextTrans));
        }

        // deprecated. Do not use labelText if possible. translation should happen in the view
        if (isset($vars['labelText']) && $vars['labelText']) {
            $labelText = $vars['labelText'];
        } else {
            $labelParams = isset($vars['labelParameters']) ? $vars['labelParameters'] : [];
            // label is translated directly here
            if ('' != $translationKey) {
                $labelText = $this->translator->trans($translationKey.'.label', $labelParams, $domain);
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
            $preInputTextTrans = $this->translator->trans($translationKey.'.preInput', [], $domain);
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
            'useFormGroup' => isset($vars['useFormGroup']) ? $vars['useFormGroup'] : true,
            'dataModule' => isset($vars['dataModule']) ? $vars['dataModule'] : false,
            'formGroupClass' => $formGroupClass,
            'labelRaw' => !empty($vars['labelRaw']),
            'labelLink' => !empty($vars['labelLink']),
            'preInputText' => $preInputText,
            'label' => array_merge([
                'text' => $labelText,
                'isPageHeading' => false,
                'caption' => false,
            ], $vars['label'] ?? []),
        ];
    }

    public function getName()
    {
        return 'form_input_extension';
    }
}
