<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssetType extends AbstractType
{
    private $config;
    
    /** 
     * @param array $config
     */
    public function __construct($config) 
    {
        $this->config = $config;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder->add('title', 'choice', [ 'choices' => [] ])
                ->add('value', 'number', [ 'grouping' => true, 'precision' => 2 ])
                ->add('description', 'textarea')
                ->add('valuation_date', 'date',[ 'widget' => 'text',
                                                 'input' => 'datetime',
                                                 'format' => 'yyyy-MM-dd',
                                                 'invalid_message' => ''
                                              ]);
    }
    
    public function getName() 
    {
        return 'asset';
    }
}