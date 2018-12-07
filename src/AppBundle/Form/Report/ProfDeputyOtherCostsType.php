<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProfDeputyOtherCostsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
                ->add('profDeputyOtherCosts', FormTypes\CollectionType::class, [
                'entry_type' => ProfDeputyOtherCostSingleType::class,
                'constraints' => new Valid(),
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Report\Report',
//            'validation_groups' => ['prof-other-costs'],
            'constraints' => new Valid(),
            'translation_domain' => 'report-prof-deputy-costs',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'deputy_other_costs';
    }
}
