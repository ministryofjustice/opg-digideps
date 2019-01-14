<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfDeputyCostEstimatesHowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profDeputyCostsHowChargedFixed', FormTypes\CheckboxType::class)
            ->add('profDeputyCostsHowChargedEstimates', FormTypes\ChoiceType::class, [
                'choices'  => ['fixed' => 'fixed', 'assessed' => 'assessed', 'both' => 'both'],
                'expanded' => true
            ])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
             'validation_groups' => ['prof-deputy-costs-how-changed-estimates'],
             'translation_domain' => 'report-prof-deputy-costs-estimates',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'deputy_costs_estimates';
    }
}
