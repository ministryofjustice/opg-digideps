<?php

namespace App\Form\Type;

use App\Form\Subscriber\SanitizeSubscriber;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class SanitizedTextAreaType extends TextareaType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new SanitizeSubscriber());
    }

    public function getBlockPrefix(): string
    {
        return 'textarea';
    }
}
