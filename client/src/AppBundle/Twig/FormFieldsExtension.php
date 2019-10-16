<?php

namespace AppBundle\Twig;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

class FormFieldsExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('form_input', [$this, 'renderFormInput'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_submit', [$this, 'renderFormSubmit'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_errors', [$this, 'renderFormErrors'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_errors_list', [$this, 'renderFormErrorsList'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_select', [$this, 'renderFormDropDown'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_known_date', [$this, 'renderFormKnownDate'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_sort_code', [$this, 'renderFormSortCode'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_checkbox_group', [$this, 'renderCheckboxGroup'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_checkbox', [$this, 'renderCheckboxInput'], ['needs_environment' => true]),
        ];
    }

    /**
     * @DEPRECATED
     * Renders form input field.
     *
     * @param mixed  $element
     * @param string $elementName
     * @param int    $transIndex
     * @param array  $vars
     */
    public function renderFormInput(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
    {
        //generate input field html using variables supplied
        echo $env->render(
            'AppBundle:Components/Form:_input.html.twig',
            array_merge(
                $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex),
                ['multiline' => in_array('textarea', $element->vars['block_prefixes'])]
            )
        );
    }

    /**
     * Renders form checkbox field.
     *
     * @param mixed  $element
     * @param string $elementName
     * @param int    $transIndex
     * @param array  $vars
     */
    public function renderCheckboxInput(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
    {
        echo $env->render(
            'AppBundle:Components/Form:_checkbox.html.twig',
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
    public function renderCheckboxGroup(Twig_Environment $env, FormView $element, $elementName, $vars, $transIndex = null)
    {
        //lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        //sort hint text translation
        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;
        }

        if (isset($vars['legendText'])) {
            $legendText = $vars['legendText'];
        } else {

            //get legendText translation. Look for a .legend value, if there isn't one then try the top level
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

         //generate input field html using variables supplied
        echo $env->render('AppBundle:Components/Form:_checkboxgroup.html.twig', [
            'classes' => isset($vars['classes']) ? $vars['classes'] : null,
            'disabled' => isset($vars['disabled']) ? $vars['disabled'] : false,
            'fieldSetClass' => isset($vars['fieldSetClass']) ? $vars['fieldSetClass'] : null,
            'formGroupClass' => isset($vars['formGroupClass']) ? $vars['formGroupClass'] : null,
            'legendText' => $legendText,
            'legendClass' => isset($vars['legendClass']) ? $vars['legendClass'] : null,
            'useFormGroup' => isset($vars['useFormGroup']) ? $vars['useFormGroup'] : true,
            'hintText' => $hintText,
            'element' => $element,
            'vertical' => isset($vars['vertical']) ? $vars['vertical'] : false,
            'items' => empty($vars['items']) ? [] : $vars['items'],
            'translationDomain' => $domain,
        ]);
    }

    /**
     * @DEPRECATED
     *
     */
    public function renderCheckboxGroupNew(Twig_Environment $env, FormView $element, $elementName, $vars, $transIndex = null)
    {
        //lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        //sort hint text translation
        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;
        }

        if (isset($vars['legendText'])) {
            $legendText = $vars['legendText'];
        } else {

            //get legendText translation. Look for a .legend value, if there isn't one then try the top level
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

        //generate input field html using variables supplied
        echo $env->render('AppBundle:Components/Form:_checkboxgroup_new.html.twig', [
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
     * @param mixed  $element
     * @param string $elementName
     * @param int    $transIndex
     * @param array  $vars
     */
    public function renderFormDropDown(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
    {
        //generate input field html using variables supplied
        echo $env->render(
            'AppBundle:Components/Form:_select.html.twig',
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }

    public function renderFormKnownDate(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
    {
        //lets get the translation for class and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        // read domain from Form ption 'translation_domain'
        $domain = $element->parent->vars['translation_domain'];

        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        if (isset($vars['showDay'])) {
            $showDay = $vars['showDay'];
        } else {
            $showDay = 'true';
        }

        //sort hint text translation
        $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
        if ($hintTextTrans !== $translationKey . '.hint') {
            $hintText = $hintTextTrans;
        } else {
            $hintText = $this->translator->trans('defaultDateHintText', [], 'common');
        }

        //get legendText translation
        $legendParams = isset($vars['legendParameters']) ? $vars['legendParameters'] : [];

        $legendTextTrans = $this->translator->trans($translationKey . '.legend', $legendParams, $domain);

        if ($legendTextTrans != $translationKey . '.legend') {
            $legendText = $legendTextTrans;
        } else {
            // the
            $legendTextTrans = $this->translator->trans($translationKey . '.label', $legendParams, $domain);
            if ($legendTextTrans != $translationKey . '.label') {
                $legendText = $legendTextTrans;
            } else {
                $legendText = null;
            }
        }

        $html = $env->render('AppBundle:Components/Form:_known-date.html.twig', [
            'legendText' => $legendText,
            'hintText' => $hintText,
            'element' => $element,
            'showDay' => $showDay,
            'legendTextRaw' => !empty($vars['legendRaw']), ]);
        echo $html;
    }

    public function renderFormSortCode(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
    {
        //lets get the translation for class and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        // read domain from Form ption 'translation_domain'
        $domain = $element->parent->vars['translation_domain'];

        //sort hint text translation
        $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
        $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;

        //get legendText translation
        $legendTextTrans = $this->translator->trans($translationKey . '.legend', [], $domain);

        $legendText = ($legendTextTrans != $translationKey . '.legend') ? $legendTextTrans : null;

        $html = $env->render('AppBundle:Components/Form:_sort-code.html.twig', ['legendText' => $legendText,
                                                                                                'hintText' => $hintText,
                                                                                                'element' => $element,
                                                                                              ]);
        echo $html;
    }

    /**
     * @param mixed  $element
     * @param string $elementName used to pick the translation by appending ".label"
     * @param array  $vars        [buttonClass => additional class. "disabled" supported]
     */
    public function renderFormSubmit(Twig_Environment $env, $element, $elementName, array $vars = [])
    {
        $options = [
            // label comes from labelText (if defined, but throws warning) ,or elementname.label from the form translation domain
            'label' => $elementName . '.label',
            'element' => $element,
            'translationDomain' => isset($vars['labelTranslationDomain']) ? $vars['labelTranslationDomain'] : null,
            'buttonClass' => isset($vars['buttonClass']) ? $vars['buttonClass'] : null,
        ];

        // deprecated. only kept in order not to break forms that use it
        if (isset($vars['labelText'])) {
            $options['label'] = $vars['labelText'];
        }

        $html = $env->render('AppBundle:Components/Form:_button.html.twig', $options);

        echo $html;
    }

    /**
     * get individual field errors and render them inside the field
     * Usage: {{ form_errors(element) }}.
     *
     * @param $element
     */
    public function renderFormErrors(Twig_Environment $env, $element)
    {
        $html = $env->render('AppBundle:Components/Form:_errors.html.twig', [
            'element' => $element,
        ]);

        echo $html;
    }

    /**
     * get form errors list and render them inside Components/Alerts:error_summary.html.twig
     * Usage: {{ form_errors_list(form) }}.
     *
     * @param FormView $form
     */
    public function renderFormErrorsList(Twig_Environment $env, FormView $form)
    {
        $formErrorMessages = $this->getErrorsFromFormViewRecursive($form);

        $html = $env->render('AppBundle:Components/Alerts:_validation-summary.html.twig', [
            'formErrorMessages' => $formErrorMessages,
            'formUncaughtErrors' => empty($form->vars['errors']) ? [] : $form->vars['errors'],
        ]);

        echo $html;
    }

    /**
     * @param FormView $elementsFormView
     *
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
     * @param \Symfony\Component\Form\FormView $element
     * @param string                           $elementName
     * @param array                            $vars
     * @param string|null                      $transIndex
     *
     * @return array with vars labelText,labelParameters,hintText,element,labelClass, to pass into twig templates AppBundle:Components/Form:*
     */
    private function getFormComponentTwigVariables($element, $elementName, array $vars, $transIndex)
    {
        //lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;
        }

        //sort hintList text translation
        $hintListArray = null;
        if (!empty($vars['hasHintList'])) {
            $hintListTextTrans = $this->translator->trans($translationKey . '.hintList', [], $domain);
            $hintListArray = array_filter(explode("\n", $hintListTextTrans));
        }

        // deprecated. Do not use labelText if possible. translation should happen in the view
        if (isset($vars['labelText']) && $vars['labelText']) {
            $labelText = $vars['labelText'];
        } else {
            $labelParams = isset($vars['labelParameters']) ? $vars['labelParameters'] : [];
            // label is translated directly here
            $labelText = $this->translator->trans($translationKey . '.label', $labelParams, $domain);
        }

        //inputPrefix
        $inputPrefix = isset($vars['inputPrefix']) ? $this->translator->trans($vars['inputPrefix'], [], $domain) : null;

        $labelClass = isset($vars['labelClass']) ? $vars['labelClass'] : null;
        $inputClass = isset($vars['inputClass']) ? $vars['inputClass'] : null;
        $formGroupClass = isset($vars['formGroupClass']) ? $vars['formGroupClass'] : '';

        //Text to insert to the left of an input, e.g. * * * * for account
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
            'useFormGroup' => isset($vars['useFormGroup']) ? $vars['useFormGroup'] : true,
            'formGroupClass' => $formGroupClass,
            'labelRaw' => !empty($vars['labelRaw']),
            'preInputText' => $preInputText,
        ];
    }

    public function getName()
    {
        return 'form_input_extension';
    }
}
