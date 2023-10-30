<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class NoMoneyInType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reasonForNoMoneyIn', FormTypes\TextareaType::class, [
                'constraints' => [new NotBlank(['message' => 'moneyIn.reasonForNoMoneyIn.notBlank', 'groups' => ['no_money_exists']])],
            ])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function getBlockPrefix()
    {
        return 'no_money_exists';
    }
}
