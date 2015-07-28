<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Constraints;

class ReasonForNoDecisionType extends AbstractType{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('reason','textarea', [ 'constraints' => [ new Constraints\NotBlank([ 'message' => 'decision.no-decision-reason.notBlank'])
                     ]])
                ->add('mode','hidden')
                ->add('saveReason', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
              'translation_domain' => 'report-decisions'
        ]);
    }
    
    public function getName()
    {
        return 'reason_for_no_decision';
    }
}
