<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProfDeputyCostInterimType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profDeputyInterimCosts', FormTypes\CollectionType::class, [
                'entry_type' => ProfDeputyCostInterimSingleType::class,
                'constraints' => new Valid(),
            ])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
             'data_class' => Report::class,
             'translation_domain' => 'report-prof-deputy-costs',
             'validation_groups' => ['prof-deputy-interim-costs'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'costs_interims';
    }
}
