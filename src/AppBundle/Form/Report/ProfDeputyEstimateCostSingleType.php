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
                'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                'invalid_message' => 'profDeputyEstimateCost.amount.notNumeric',
            ]);

        // add textarea to debts that has more details flag set to true
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $profDeputyEstimateCost = $event->getData();
            /* @var $profDeputyEstimateCost ProfDeputyEstimateCost */
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
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data \AppBundle\Entity\Report\ProfDeputyEstimateCost */
                $validationGroups = ['prof-deputy-estimate-costs'];

                if ($data->getAmount() && $data->getHasMoreDetails()) {
                    $validationGroups[] = 'prof-deputy-estimate-costs-more-details';
                }

                return $validationGroups;
            },
            'translation_domain' => 'report-prof-deputy-costs',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'prof_cost_single';
    }
}
