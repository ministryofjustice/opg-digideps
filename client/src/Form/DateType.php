<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType as ParentDateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'text',
            'input' => 'datetime',
            'format' => 'yyyy-MM-dd'
        ]);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return ParentDateType::class;
    }
}
