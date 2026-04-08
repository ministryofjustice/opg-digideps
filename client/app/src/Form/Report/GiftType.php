<?php

namespace App\Form\Report;

use App\Entity\Report\Gift;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('explanation', FormTypes\TextareaType::class, [
                'required' => true,
            ])
            ->add('amount', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'invalid_message' => 'gifts.amount.type',
            ]);

        $reportType = $options['report']->getType();

        if (!empty($options['report']->getBankAccountOptions()) && $options['report']->canLinkToBankAccounts()) {
            $builder->add('bankAccountId', FormTypes\ChoiceType::class, [
                'choices' => $options['report']->getBankAccountOptions(),
                'placeholder' => 'Please select',
            ]);
        }

        $builder->add('saveAndContinue', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gift::class,
            'validation_groups' => ['gift'],
            'translation_domain' => 'report-gifts',
        ])
        ->setRequired(['user', 'report']);
    }

    public function getBlockPrefix(): string
    {
        return 'gifts_single';
    }
}
