<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class ReportSubmitType extends AbstractType
{  
    private $translator;
    
    public function __construct(Translator $translator) 
    {
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('reviewed_n_checked', 'choice', 
                                            [ 'choices' => [ 1 => $this->translator->trans('reportSubmit.checkbox.label') ], 
                                              'multiple' => true, 
                                              'expanded' => true,
                                              'constraints' => new Constraints\NotBlank(),
                                              'empty_data' => null ])
                ->add('submitReport', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'report-common',
        ]);
    }
    
    public function getName()
    {
        return 'report_submit';
    }
}