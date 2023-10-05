<?php

namespace App\Form\Report\Debt;

use App\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class DebtsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('debts', FormTypes\CollectionType::class, [
                'entry_type' => DebtSingleType::class,
                'entry_options' => ['constraints' => new Valid()],
                'constraints' => new Valid(),
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
            'validation_groups' => ['debts'],
            'constraints' => new Valid(),
            'translation_domain' => 'report-debts',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'debt';
    }
}
