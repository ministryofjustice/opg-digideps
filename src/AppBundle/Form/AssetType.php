<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Asset form
 * 
 * note: title is hidden (filled from the controller based on AssetTypeTitle form)
 */
class AssetType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder->add('title', 'hidden') //use the AssetTypeTile to display the title
                ->add('value', 'number', [
                    'precision' => 2, 
                    'invalid_message' => 'asset.value.type'
                ])
                ->add('description', 'textarea')
                ->add('valuationDate', 'date',[ 'widget' => 'text',
                                                 'input' => 'datetime',
                                                 'format' => 'dd-MM-yyyy',
                                                 'invalid_message' => 'Enter a valid date'
                                              ])
                ->add('id', 'hidden')
                ->add('save', 'submit');
    }
    
    public function getName() 
    {
        return 'asset';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'report-assets',
        ]);
    }
}
