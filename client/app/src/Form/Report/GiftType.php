<?php

namespace OPG\Digideps\Frontend\Form\Report;

use OPG\Digideps\Frontend\Entity\Report\Gift;
use OPG\Digideps\Frontend\Entity\Report\Report;
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

        /** @var Report $report */
        $report = $options['report'];
        if (!empty($report->getBankAccountOptions()) && $report->canLinkToBankAccounts()) {
            $builder->add('bankAccountId', FormTypes\ChoiceType::class, [
                'choices' => $report->getBankAccountOptions(),
                'placeholder' => 'Please select',
                'required' => false,
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
