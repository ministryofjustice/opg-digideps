<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Gift;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType; use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('explanation', FormTypes\TextareaType::class, [
                'required' => true,
            ])
            ->add('amount', FormTypes\NumberType::class, [
                'precision' => 2,
                'grouping' => true,
                'invalid_message' => 'gifts.amount.type',
            ]);

        $reportType = $options['report']->getType();

        if (!empty($options['report']->getBankAccountOptions()) && (in_array($reportType, ['102', '102-4']))) {
            $builder->add('bankAccountId', FormTypes\ChoiceType::class, [
                    'choices' => $options['report']->getBankAccountOptions(),
                    'empty_value' => 'Please select'
                ]);
        }

        $builder ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Gift::class,
            'validation_groups' => ['gift'],
            'translation_domain' => 'report-gifts',
        ])
        ->setRequired(['user', 'report']);
    }

    public function getBlockPrefix()
    {
        return 'gifts_single';
    }
}
