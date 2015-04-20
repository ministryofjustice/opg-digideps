<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientEditReportPeriodType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDate', 'date', [ 'widget' => 'text',
                                              'input' => 'datetime',
                                              'format' => 'yyyy-MM-dd',
                                              'invalid_message' => 'report.startDate.invalidMessage' ])
                
                ->add('endDate', 'date', [ 'widget' => 'text',
                                            'input' => 'datetime',
                                            'format' => 'yyyy-MM-dd',
                                            'invalid_message' => 'report.endDate.invalidMessage'
                                          ])

                ->add('save', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'client',
        ]);
    }
    
    public function getName()
    {
        return 'clientEditReportPeriod';
    }
}