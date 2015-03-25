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
     * @var array
     */
    private $params;
    
    /**
     * @param type $translator
     * @param type $params
     */
    public function __construct(TranslatorInterface $translator, $params)
    {
        $this->translator = $translator;
        $this->params = $params;
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
            'form_cancel' => new \Twig_Function_Method($this, 'renderFormCancelLink'),
            'progress_bar' => new \Twig_Function_Method($this, 'progressBar'),
            'form_checkbox_group' => new \Twig_Function_Method($this, 'renderCheckboxGroup'),
            'tab' => new \Twig_Function_Method($this, 'renderTab'),
            'accordionLinks' => new \Twig_Function_Method($this, 'renderAccordionLinks'),
        ];
    }
    
    public function getFilters()
    {
        return [
            'country_name' => new \Twig_SimpleFilter('country_name', function($value) {
                return \Symfony\Component\Intl\Intl::getRegionBundle()->getCountryName($value);
            }),
        ];
    }
    
    /**
     * 
     * @param array $options keys: clickedPanel, allOpenHref, allClosedHref, firstPanelHref, secondPanelHref
     * @return array [ first => [open=>boolean, href=>], second => [open=>boolean, href=>] ]
     */
    public function renderAccordionLinks(array $options)
    {
        $clickedPanel = $options['clickedPanel'];
        $bothOpenHref = $options['bothOpenHref'];
        $allClosedHref = $options['allClosedHref'];
        $firstPanelHref = $options['firstPanelHref'];
        $secondPanelHref = $options['secondPanelHref'];
        
        // default: closed
        $ret = [
            'first' => [
                'open' => false,
                'href' => $firstPanelHref,
            ],
            'second' => [
                'open' => false,
                'href' => $secondPanelHref,
            ]
        ];
        
        switch ($clickedPanel) {
            case $firstPanelHref:
                $ret['first']['open'] = true;
                $ret['first']['href'] = $allClosedHref;
                $ret['second']['href'] = $bothOpenHref;
                break;
            
            case $secondPanelHref:
                $ret['second']['open'] = true;
                $ret['first']['href'] = $bothOpenHref;
                $ret['second']['href'] = $allClosedHref;
                break;
            
            case $bothOpenHref:
                $ret['first']['open'] = true;
                $ret['second']['open'] = true;
                $ret['first']['href'] = $secondPanelHref;
                $ret['second']['href'] = $firstPanelHref;
                break;
        }
        
        return $ret;
    }
    
    /**
     * 
     * @param string $barName
     * @param integer $activeStepNumber
     */
    public function progressBar($barName, $activeStepNumber)
    {
        if (empty($this->params['progress_bars'][$barName])) {
            return "[ Progress bar $barName not found or empty, check your configuration files ]";
        }
        
        $steps = [];
        // set classes and labels from translation
        foreach ($this->params['progress_bars'][$barName] as $stepNumber) {
            $steps[] = [
                'label' => $this->translator->trans($barName . '.' . $stepNumber . '.label', [], 'progress-bar'),
                'class' => (($stepNumber == $activeStepNumber)     ? ' progress--active '    : '')
                         . (($stepNumber < $activeStepNumber)      ? ' progress--completed ' : '')
                         . (($stepNumber == $activeStepNumber - 1) ? ' progress--previous '  : '')
            ];
        }
        
        echo $this->environment->render('AppBundle:Components/Navigation:_progress-indicator.html.twig', [
            'progressSteps' => $steps
        ]);
    }

    
    /**
     * Render tab component (used in report page)
     * - Reads elements from twig.yml, tabs section
     * - Translations taken from tabs.yml
     * - Active tab has class "active"
     * 
     * @param string $tabGroup
     * @param string $activeTab ID of the active tab (
     * @param array $pathOptions contains the params for all the URL in the tab
     * 
     * @return string
     */
    public function renderTab($tabGroup, $activeTab, array $pathOptions = [])
    {
        $activeClass ='active';
        
        if (empty($this->params['tabs'][$tabGroup])) {
            return "[ Tab $tabGroup not found or empty, check your configuration files ]";
        }
        
        $tabDataProvider = [];
        // set classes and labels from translation
        foreach ($this->params['tabs'][$tabGroup] as $tabId => $tabData) {
            $tabDataProvider[] = [
                'label' => $this->translator->trans($tabGroup . '.' . $tabId . '.label', [], 'tabs'),
                'class' => $activeTab == $tabId ? $activeClass : '',
                'tabId' => $tabId,
                'href' => [
                    'path' => $tabData['path'],
                    'params' => $pathOptions,
                ]
            ];
        }
        
        echo $this->environment->render('AppBundle:Components/Navigation:_tab-bar.html.twig', [
            'tabDataProvider' => $tabDataProvider
        ]);
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
     * form_checkbox_group(element, 'allowedCourtOrderTypes', {
       'legendClass' : 'form-label-bold',
       'fieldSetClass' : 'inline',
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

        //get legendText translation
        $legendTextTrans = $this->translator->trans($translationKey.'.legend', [],$domain);
        
        $legendText =  ($legendTextTrans != $translationKey.'.legend')? $legendTextTrans: null;
        
         //generate input field html using variables supplied
        echo $this->environment->render( 'AppBundle:Components/Form:_checkbox.html.twig', [
            'fieldSetClass' => isset($vars['fieldSetClass']) ? $vars['fieldSetClass']: null,
            'legendText' => $legendText,
            'legendClass' => isset($vars['legendClass']) ? $vars['legendClass']: null,
            'hintText' => $hintText,
            'element'  => $element,
            'items' => empty($vars['items']) ? [] : $vars['items'],
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

        //inputPrefix
        $inputPrefix = isset($vars['inputPrefix'])? $this->translator->trans($vars['inputPrefix'],[],$domain): null;

        $labelClass = isset($vars['labelClass']) ? $vars['labelClass']: null;
        $inputClass = isset($vars['inputClass']) ? $vars['inputClass']: null;
        $formGroupClass = isset($vars['formGroupClass']) ? $vars['formGroupClass']: "";
        
        return [ 
            'labelText' => $labelText,
            'hintText' => $hintText,
            'element'  => $element,
            'labelClass' => $labelClass,
            'inputClass' => $inputClass,
            'inputPrefix' => $inputPrefix,
            'formGroupClass' => $formGroupClass
        ];
    }
    
    public function getName()
    {
        return 'form_input_extension';
    }
}