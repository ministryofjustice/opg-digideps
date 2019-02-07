<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfDeputyCostHowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profDeputyCostsHowChargedFixed', FormTypes\CheckboxType::class)
            ->add('profDeputyCostsHowChargedAssessed', FormTypes\CheckboxType::class)
            ->add('profDeputyCostsHowChargedAgreed', FormTypes\CheckboxType::class)
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
             'validation_groups' => ['prof-deputy-costs-how-changed'],
             'translation_domain' => 'report-prof-deputy-costs',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'deputy_costs';
    }
}
