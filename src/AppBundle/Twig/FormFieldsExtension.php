<?php
namespace AppBundle\Twig;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormError;

class FormFieldsExtension extends \Twig_Extension
{
    private $translator;
    private $environment;
    
    
    public function __construct($translator)
    {
        $this->translator = $translator;
    }
    
    public function initRuntime(\Twig_Environment $environment) {
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
            'form_cancel' => new \Twig_Function_Method($this, 'renderFormCancelLink'),
            'step_progress_class' => new \Twig_Function_Method($this, 'stepProgressClass')
        ];
    }
    
    /**
     * Calculate classes needed for each step for user registration
     * 
     * @param integer $step
     * @param integer $currentStep
     * @param array $classes keys: active, completed, previous
     * @return type
     */
    public function stepProgressClass($step, $currentStep, array $classes)
    {
        $return = [];
        if ($step == $currentStep) {
            $return[] = $classes['active'];
        }
        if ($step < $currentStep) {
            $return[] = $classes['completed'];
        }
        if ($step == $currentStep - 1) {
            $return[] = $classes['previous'];
        }
        
        return implode(' ', $return);
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
        $domain = $element->parent->vars['translation_domain'];
        
        //sort hint text translation
        $hintTextTrans =  $this->translator->trans($translationKey.'.hint', [],$domain);
        $hintText =  ($hintTextTrans != $translationKey.'.hint')? $hintTextTrans: null;
        
        //get legendText translation
        $legendTextTrans = $this->translator->trans($translationKey.'.legend', [],$domain);
        
        $legendText =  ($legendTextTrans != $translationKey.'.legend')? $legendTextTrans: null;
        
        $html = $this->environment->render('AppBundle:Components/Form:_known-date.html.twig', [ 'legendText' => $legendText,
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
        $html = $this->environment->render('AppBundle:Components/Form:_button.html.twig', [ 'labelText' => $labelText, 
                                                                                            'element'  => $element,
                                                                                            'buttonClass' => $buttonClass
                                                                                         ]);
        
        echo $html;
    }
    
    
    /**
     * get form errors lits and render them inside Components/Alerts:error_summary.html.twig
     * Usage: {{ form_errors_list(form) }}
     * 
     * @param FormView $form
     */
    public function renderFormErrorsList(FormView $form)
    {
        $formErrorMessages = [];
        foreach ($form as $elementFormView) { /*@var $elementFormView FormView */
            $elementFormErrors = empty($elementFormView->vars['errors']) ? [] : $elementFormView->vars['errors'];
            foreach ($elementFormErrors as $formError) { /* @var $error FormError */ 
                $formErrorMessages[] = $formError->getMessage();
            }
        }
        

        $html = $this->environment->render('AppBundle:Components/Alerts:_validation-summary.html.twig', [
            'formErrorMessages' => $formErrorMessages
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

        //sort out labelText translation
        $labelText = isset($vars['labelText'])? $vars['labelText']: $this->translator->trans($translationKey.'.label',[],$domain);

        $labelClass = isset($vars['labelClass']) ? $vars['labelClass']: null;
        
        return [ 
            'labelText' => $labelText,
            'hintText' => $hintText,
            'element'  => $element,
            'labelClass' => $labelClass
        ];
    }
    
    public function getName()
    {
        return 'form_input_extension';
    }
}