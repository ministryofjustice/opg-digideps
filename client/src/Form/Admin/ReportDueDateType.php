<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportDueDateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => array_flip([
                'keep' => 'reportManage.form.dueDateChoice.choices.keep',
                3 => 'reportManage.form.dueDateChoice.choices.3weeks',
                4 => 'reportManage.form.dueDateChoice.choices.4weeks',
                5 => 'reportManage.form.dueDateChoice.choices.5weeks',
                'custom' => 'reportManage.form.dueDateChoice.choices.custom',
            ]),
            'translation_domain' => 'admin-clients',
            'choices_as_values' => true,
            'expanded' => true,
            'multiple' => false,
            'mapped' => false,
            'constraints' => [
                new NotBlank(['message' => 'report.dueDateChoice.notBlank']),
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
