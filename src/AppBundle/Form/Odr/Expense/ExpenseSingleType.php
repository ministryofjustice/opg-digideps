<?php

namespace AppBundle\Form\Odr\Expense;

use AppBundle\Entity\Odr\Expense;
use AppBundle\Entity\Odr\IncomeBenefit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ExpenseSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('explanation', 'text', [
                     'required' => true,
                 ])
                 ->add('amount', 'number');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
             'data_class' => Expense::class,
             'validation_groups' => ['odr-expenses'],
             'translation_domain' => 'odr-expenses',
        ]);
    }

    public function getName()
    {
        return 'odr_expenses_single';
    }
}
