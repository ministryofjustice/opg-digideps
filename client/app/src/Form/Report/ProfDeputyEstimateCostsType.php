<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProfDeputyEstimateCostsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profDeputyManagementCostAmount', FormTypes\NumberType::class, [
                'scale' => 2,
                'error_bubbling' => false,
                'grouping' => true,
                'constraints' => new Valid(),
            ])
            ->add('profDeputyEstimateCosts', FormTypes\CollectionType::class, [
                'entry_type' => ProfDeputyEstimateCostSingleType::class,
                'entry_options' => ['constraints' => new Valid()],
                'constraints' => new Valid(),
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Report\Report',
            'validation_groups' => ['prof-deputy-estimate-costs', 'prof-deputy-estimate-management-costs'],
            'constraints' => new Valid(),
            'translation_domain' => 'report-prof-deputy-costs-estimate',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'deputy_estimate_costs';
    }
}
