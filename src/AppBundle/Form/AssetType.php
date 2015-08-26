<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AssetType extends AbstractType
{
    /**
     * @var array 
     */
    private $assetDropdownKeys;
    
    /**
     * @var Translator 
     */
    private $translator;
    
    /**
     * @var string 
     */
    private $translatorDomain;
    
    
    public function __construct(array $assetDropdownKeys, TranslatorInterface $translator, $translatorDomain)
    {
        $this->assetDropdownKeys = $assetDropdownKeys;
        $this->translator = $translator;
        $this->translatorDomain = $translatorDomain;
    }

    /**
     * @return array with choices for the "title" dropdown element
     */
    public function getTitleChoices()
    {
        if (empty($this->assetDropdownKeys)) {
            return [];
        }
        
        $ret = [];
        
        // translate keys and order by name
        foreach($this->assetDropdownKeys as $key) {
            $translation = $this->translator->trans('dropdown.'.$key, [], $this->translatorDomain);
            $ret[$translation] = $translation;
        }
        // order by name (keep position for the last element)
        $last = array_pop($ret);
        // order by name
        asort($ret);
        $ret[$last] = $last;
        
        return $ret;
    }
    
    
    
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder->add('title', 'choice', [ 'choices' => $this->getTitleChoices(), 'empty_value' => 'Please select' ])
                ->add('value', 'number', [ 
                    'grouping' => true, 
                    'precision' => 2, 
                    'invalid_message' => 'asset.value.type'
                ])
                ->add('description', 'textarea')
                ->add('valuationDate', 'date',[ 'widget' => 'text',
                                                 'input' => 'datetime',
                                                 'format' => 'dd-MM-yyyy',
                                                 'invalid_message' => 'invalid date'
                                              ])
                ->add('id', 'hidden')
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'report-assets',
        ]);
    }
    
    public function getName() 
    {
        return 'asset';
    }
}