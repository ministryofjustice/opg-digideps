<?php
namespace AppBundle\Twig;

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
                    'form_submit' => new \Twig_Function_Method($this, 'renderFormSubmit')
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
        //lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex))? $transIndex.'.'.$elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];
        
        //sort hint text translation
        $hintTextTrans =  $this->translator->trans($translationKey.'.hint', [],$domain);
        $hintText =  ($hintTextTrans != $translationKey.'.hint')? $hintTextTrans: null;
       
        //sort out labelText translation
        $labelText = isset($vars['labelText'])? $vars['labelText']: $this->translator->trans($translationKey.'.label');
        
        $labelClass = isset($vars['labelClass']) ? $vars['labelClass']: null;
        
        //generate input field html using variables supplied
        $html = $this->environment->render('AppBundle:Components/Form:_input.html.twig', [ 'labelText' => $labelText, 
                                                                                           'hintText' => $hintText,
                                                                                           'element'  => $element,
                                                                                           'labelClass' => $labelClass
                                                                                         ]);
        echo $html;
    }
    
    public function renderFormSubmit()
    {
    }
    
    public function getName()
    {
        return 'form_input_extension';
    }
}