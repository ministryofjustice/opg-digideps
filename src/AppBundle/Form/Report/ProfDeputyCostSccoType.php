<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfDeputyCostSccoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profDeputyCostsAmountToScco', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'error_bubbling' => false,
            ])
            ->add('profDeputyCostsReasonBeyondEstimate', FormTypes\TextareaType::class)
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => ['prof-deputy-costs-scco'],
            'translation_domain' => 'report-prof-deputy-costs'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'deputy_costs_previous';
    }
}
