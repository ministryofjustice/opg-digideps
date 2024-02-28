<?php

namespace App\Form\Report;

use App\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class DoesMoneyInExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('moneyInExists', FormTypes\ChoiceType::class, [
                'choices' => [
                    'existPage.form.choices.yes' => Report::YES_MONEY_EXISTS,
                    'existPage.form.choices.no' => Report::NO_MONEY_EXISTS
                ],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'moneyIn.moneyInChoice.notBlank', 'groups' => 'does-money-in-exist'])],
            ])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-money-in',
            'validation_groups' => ['does-money-in-exist'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'does_money_in_exist';
    }
}
