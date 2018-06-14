<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType; use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfServicePreviousFeesEstimateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('previousProfFeesEstimateGiven', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => 'fee.previousProfFeesEstimateGivenChoice.notBlank', 'groups' => ['current-prof-payments-received']])],
            ])
            ->add('profFeesEstimateSccoReason', FormTypes\TextareaType::class)
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-prof_service_fee',
            'validation_groups' => ['previous-prof-fees-estimate-choice'],
            'translation_domain' => 'report-prof-current-fees',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'prof_service_fees';
    }
}
