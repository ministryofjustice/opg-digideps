<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssetType extends AbstractType
{
    private $titles;
    
    /** 
     * @param array $titles
     */
    public function __construct($titles) 
    {
        $this->titles = $titles;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder->add('title', 'choice', [ 'choices' => $this->titles, 'empty_value' => 'Please select' ])
                ->add('value', 'number', [ 'grouping' => true, 'precision' => 2 ])
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