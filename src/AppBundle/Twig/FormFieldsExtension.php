<?php
namespace AppBundle\Twig;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormError;
use Symfony\Component\Translation\TranslatorInterface;

class FormFieldsExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    
    /**
     * @var \Twig_Environment
     */
    private $environment;
    
    /**
     * @param type $translator
     * @param type $params
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    public function initRuntime(\Twig_Environment $environment)
    {
        parent::initRuntime($environment);
        $this->environment = $environment;
    }
    
    public function getFunctions()
    {
        return [
            'form_input' => new \Twig_Function_Method($this, 'renderFormInput'),
            'form_submit' => new \Twig_Function_Method($this, 'renderFormSubmit'),
            'form_errors_list' => new \Twig_Function_Method($this, 'renderFormErrorsList'),
            'form_select' => new \Twig_Function_Method($this, 'renderFormDropDown'),
            'form_known_date' => new \Twig_Function_Method($this, 'renderFormKnownDate'),
            'form_sort_code' => new \Twig_Function_Method($this, 'renderFormSortCode'),
            'form_cancel' => new \Twig_Function_Method($this, 'renderFormCancelLink'),
            'form_checkbox_group' => new \Twig_Function_Method($this, 'renderCheckboxGroup'),
            'form_checkbox' => new \Twig_Function_Method($this, 'renderCheckboxInput')
        ];
    }

    /**
     * Renders form input field
     *
     * @param type $element
     * @param type $elementName
     * @param type $transIndex
     * @param array $vars
     */
    public function renderFormInput($element, $elementName,array $vars = [], $transIndex = null )
    {
        //generate input field html using variables supplied
        echo $this->environment->render(
            'AppBundle:Components/Form:_input.html.twig',
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }
    
    /**
     * Renders form checkbox field
     *
     * @param type $element
     * @param type $elementName
     * @param type $transIndex
     * @param array $vars
     */
    public function renderCheckboxInput($element, $elementName,array $vars = [], $transIndex = null )
    {
        echo $this->environment->render(
            'AppBundle:Components/Form:_checkbox.html.twig',
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }
    
     
    /**
     * form_checkbox_group(element, 'allowedCourtOrderTypes', {
       'legendClass' : 'form-label-bold',
       'fieldSetClass' : 'inline',
       'vertical': true,
       'items': [
           {'labelClass': 'block-label', 'elementClass': 'checkbox' },
           {'labelClass': 'inline-label', 'elementClass': 'checkbox' }
        ]
       })
     */
    public function renderCheckboxGroup(FormView $element, $elementName, $vars, $transIndex = null)
    {
        //lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex))? $transIndex.'.'.$elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        //sort hint text translation
        $hintTextTrans =  $this->translator->trans($translationKey.'.hint', [],$domain);
        $hintText =  ($hintTextTrans != $translationKey.'.hint')? $hintTextTrans: null;
        
        //get legendText translation. Look for a .legend value, if there isn't one then try the top level
        $legendTextTrans = $this->translator->trans($translationKey.'.legend', [],$domain);
        
        if (isset($vars['legendText'])) {
            $legendText = $vars['legendText'];
        } else {

            if ($legendTextTrans != $translationKey . '.legend') {
                $legendText = $legendTextTrans;
            } else {
                $legendTextTrans = $this->translator->trans($translationKey . '.label', [], $domain);
                if ($legendTextTrans != $translationKey . '.label') {
                    $legendText = $legendTextTrans;
                } else {
                    $legendText = null;
                }
            }
        }
        
         //generate input field html using variables supplied
        echo $this->environment->render( 'AppBundle:Components/Form:_checkboxgroup.html.twig', [
            'fieldSetClass' => isset($vars['fieldSetClass']) ? $vars['fieldSetClass']: null,
            'formGroupClass' => isset($vars['formGroupClass']) ? $vars['formGroupClass']: null,
            'legendText' => $legendText,
            'legendClass' => isset($vars['legendClass']) ? $vars['legendClass']: null,
            'hintText' => $hintText,
            'element'  => $element,
            'vertical' => isset($vars['vertical']) ? $vars['vertical']: false,
            'items' => empty($vars['items']) ? [] : $vars['items'],
            'translationDomain' => $domain
        ]);
    }
    
    /**
     * Renders form select element
     *
     * @param type $element
     * @param type $elementName
     * @param type $transIndex
     * @param array $vars
     */
    public function renderFormDropDown($element, $elementName,array $vars = [], $transIndex = null )
    {
        //generate input field html using variables supplied
        echo $this->environment->render(
            'AppBundle:Components/Form:_select.html.twig', 
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }
    
    public function renderFormKnownDate($element, $elementName,array $vars = [], $transIndex = null)
    {
        //lets get the translation for class and labelText
        $translationKey = (!is_null($transIndex))? $transIndex.'.'.$elementName : $elementName;
        // read domain from Form ption 'translation_domain'
        $domain = $element->parent->vars['translation_domain'];
        
        $translationKey = (!is_null($transIndex))? $transIndex.'.'.$elementName : $elementName;
        if (isset($vars['showDay'])) {
            $showDay = $vars['showDay'];
        } else {
            $showDay = 'true';
        }
        
        //sort hint text translation
        $hintTextTrans =  $this->translator->trans($translationKey.'.hint', [],$domain);
        $hintText =  ($hintTextTrans != $translationKey.'.hint')? $hintTextTrans: null;
        
        //get legendText translation
        $legendParams = isset($vars['legendParameters']) ? $vars['legendParameters'] : [];
        
        $legendTextTrans = $this->translator->trans($translationKey.'.legend', $legendParams, $domain);    

        if ($legendTextTrans != $translationKey.'.legend') {
            $legendText = $legendTextTrans;
        } else {
            $legendTextTrans = $this->translator->trans($translationKey . '.label', [],$domain);
            if ($legendTextTrans != $translationKey.'.label') {
                $legendText = $legendTextTrans;
            } else {
                $legendText = null;
            }
        }

        $legendTextTransJS = $this->translator->trans($translationKey.'.legendjs', $legendParams, $domain);
        $legendTextJS =  ($legendTextTransJS != $translationKey.'.legendjs')? $legendTextTransJS: null;
        
        $html = $this->environment->render('AppBundle:Components/Form:_known-date.html.twig', [ 'legendText' => $legendText,
                                                                                                'legendTextJS' => $legendTextJS,
                                                                                                'hintText' => $hintText,
                                                                                                'element' => $element,
                                                                                                'showDay' => $showDay,
                                                                                                'legendTextRaw' => !empty($vars['legendRaw'])]);
        echo $html;
    }
    
    public function renderFormSortCode($element, $elementName,array $vars = [], $transIndex = null)
    {
        //lets get the translation for class and labelText
        $translationKey = (!is_null($transIndex))? $transIndex.'.'.$elementName : $elementName;
        // read domain from Form ption 'translation_domain'
        $domain = $element->parent->vars['translation_domain'];
        
        //sort hint text translation
        $hintTextTrans =  $this->translator->trans($translationKey.'.hint', [],$domain);
        $hintText =  ($hintTextTrans != $translationKey.'.hint')? $hintTextTrans: null;
        
        //get legendText translation
        $legendTextTrans = $this->translator->trans($translationKey.'.legend', [],$domain);
        
        $legendText =  ($legendTextTrans != $translationKey.'.legend')? $legendTextTrans: null;
        
        $html = $this->environment->render('AppBundle:Components/Form:_sort-code.html.twig', [ 'legendText' => $legendText,
                                                                                                'hintText' => $hintText,
                                                                                                'element' => $element
                                                                                              ]);
        echo $html;
    }
    
    
    
    /**
     * @param type $element
     * @param type $elementName
     * @param array $vars
     * @param type $transIndex
     */
    public function renderFormSubmit($element, $elementName, array $vars = [], $transIndex = null )
    {
        //lets get the translation for class and labelText
        $translationKey = (!is_null($transIndex))? $transIndex.'.'.$elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        //sort out labelText translation
        $labelText = isset($vars['labelText'])? $vars['labelText']: $this->translator->trans($translationKey.'.label', [], $domain );
        $buttonClass = isset($vars['buttonClass']) ? $vars['buttonClass']: null;

        //generate input field html using variables supplied
        $html = $this->environment->render('AppBundle:Components/Form:_button.html.twig',
            [
                'labelText' => $labelText,
                'element'  => $element,
                'buttonClass' => $buttonClass
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
                $ret[] = ['elementId'=>$elementFormView->vars['id'], 'message'=>$formError->getMessage()];
            }
            $ret = array_merge(
                $ret, 
                $this->getErrorsFromFormViewRecursive($elementFormView)
            );
        }

        return $ret;
    }
    
    /**
     * get form errors lits and render them inside Components/Alerts:error_summary.html.twig
     * Usage: {{ form_errors_list(form) }}
     * 
     * @param FormView $form
     */
    public function renderFormErrorsList(FormView $form)
    {
        $formErrorMessages = $this->getErrorsFromFormViewRecursive($form);
        
        $html = $this->environment->render('AppBundle:Components/Alerts:_validation-summary.html.twig', [
            'formErrorMessages' => $formErrorMessages,
            'formUncaughtErrors' => empty($form->vars['errors']) ? [] : $form->vars['errors']
        ]);
        
        echo $html;
    }
    
    
    
    /**
     * @param array $vars
     * @throws type
     */
    public function renderFormCancelLink(array $vars = [])
    {
        $linkClass = isset($vars['linkClass'])? $vars['linkClass'] : null;
        
        if(!isset($vars['href'])){
            throw new \Exception("You must specify 'href' for cancel link");
        }
        
        $html = $this->environment->render('AppBundle:Components/Form:_cancel.html.twig', [ 'linkClass' => $linkClass,
                                                                                            'href' => $vars['href']
                                                                                          ]);
        
        echo $html;
    }
    
    /**
     * @param \Symfony\Component\Form\FormView $element
     * @param string $elementName
     * @param array $vars
     * @param string|null $transIndex
     * 
     * @return array with vars labelText,hintText,element,labelClass, to pass into twig templates AppBundle:Components/Form:*
     */
    private function getFormComponentTwigVariables($element, $elementName, array $vars, $transIndex)
    {
        //lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex))? $transIndex.'.'.$elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];
        
        //sort hint text translation
        $hintTextTrans =  $this->translator->trans($translationKey.'.hint', [],$domain);
        $hintText =  ($hintTextTrans != $translationKey.'.hint')? $hintTextTrans: null;

        //sort hintList text translation
        $hintListTextTrans =  $this->translator->trans($translationKey.'.hintList', [],$domain);
        $hintListEntriesText = ($hintListTextTrans != $translationKey.'.hintList') ? array_filter(explode("\n", $hintListTextTrans)) : [];
        
        //sort out labelText translation
        $labelParams = isset($vars['labelParameters']) ? $vars['labelParameters'] : [];
        $labelText = isset($vars['labelText'])? $vars['labelText']: $this->translator->trans($translationKey.'.label', $labelParams, $domain);

        //inputPrefix
        $inputPrefix = isset($vars['inputPrefix'])? $this->translator->trans($vars['inputPrefix'],[],$domain): null;

        $labelClass = isset($vars['labelClass']) ? $vars['labelClass']: null;
        $inputClass = isset($vars['inputClass']) ? $vars['inputClass']: null;
        $formGroupClass = isset($vars['formGroupClass']) ? $vars['formGroupClass']: "";
        
        //Text to insert to the left of an input, e.g. * * * * for account
        $preInputTextTrans =  $this->translator->trans($translationKey.'.preInput', [],$domain);
        $preInputText =  ($preInputTextTrans != $translationKey.'.preInput')? $preInputTextTrans: null;
        
        return [ 
            'labelText' => $labelText,
            'hintText' => $hintText,
            'hintListArray' => $hintListEntriesText,
            'element'  => $element,
            'labelClass' => $labelClass,
            'inputClass' => $inputClass,
            'inputPrefix' => $inputPrefix,
            'formGroupClass' => $formGroupClass,
            'labelRaw' => !empty($vars['labelRaw']),
            'preInputText' => $preInputText
        ];
    }
    
    public function getName()
    {
        return 'form_input_extension';
    }
}
