<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Constraints;

class ReasonForBalance extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reasonForBalance','textarea', [ 'constraints' => [ new Constraints\NotBlank([ 'message' => 'balance.bad.reason']) ]])
            ->add('save', 'submit');
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'report-accounts'
        ]);
    }

    public function getName()
    {
        return 'form';
    }
}
