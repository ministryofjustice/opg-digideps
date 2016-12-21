<?php

namespace AppBundle\Form\Odr;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DeputyExpenseAddAnotherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addAnother', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'mapped' => false,
                'constraints' => [new NotBlank(['message' => 'odr.expenses.addAnother.notBlank'])],
            ))
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-deputy-expenses',
        ]);
    }

    public function getName()
    {
        return 'odr_expense_add_another';
    }
}
