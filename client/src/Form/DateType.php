<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType as ParentDateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'text',
            'input' => 'datetime',
            'format' => 'yyyy-MM-dd',
        ]);
    }

    public function getParent(): string
    {
        return ParentDateType::class;
    }
}
