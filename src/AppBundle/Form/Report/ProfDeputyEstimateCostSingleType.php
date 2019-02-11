<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\ProfDeputyEstimateCost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfDeputyEstimateCostSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profDeputyEstimateCostTypeId', FormTypes\HiddenType::class)
            ->add('amount', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'error_bubbling' => false,
                'invalid_message' => 'profDeputyEstimateCost.amount.notNumeric',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $profDeputyEstimateCost = $event->getData();
            $form = $event->getForm();

            if ($profDeputyEstimateCost->getHasMoreDetails()) {
                $form->add('moreDetails', FormTypes\TextareaType::class, []);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfDeputyEstimateCost::class,
            'validation_groups' => ['prof-deputy-estimate-costs'],
            'translation_domain' => 'report-prof-deputy-costs-estimate',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'prof_cost_single';
    }
}
