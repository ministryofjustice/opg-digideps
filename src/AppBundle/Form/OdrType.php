<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

class OdrType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDate', DateType::class, [
            'widget' => 'choice',
            'label' => 'Start Date'
        ])
            ->add('save', 'submit', ['label' => 'Update']);
    }

    public function getName()
    {
        return 'odr';
    }
}
