<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class ReportSubmitType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('reviewed_n_checked', 'choice', 
                                            [ 'choices' => [ 1 => 'I have reviewed and checked this report' ], 
                                              'multiple' => true, 
                                              'expanded' => true,
                                              'constraints' => [ new Constraints\NotBlank()] ])
                ->add('submit_report', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'report-overview',
        ]);
    }
    
    public function getName()
    {
        return 'report_submit';
    }
}