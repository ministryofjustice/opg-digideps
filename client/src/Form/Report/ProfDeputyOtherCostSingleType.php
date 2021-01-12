<?php

namespace App\Form\Report;

use App\Entity\Report\Debt;
use App\Entity\Report\ProfDeputyOtherCost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfDeputyOtherCostSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profDeputyOtherCostTypeId', FormTypes\HiddenType::class)
            ->add('amount', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                'invalid_message' => 'profDeputyOtherCost.amount.notNumeric',
            ]);

        // add textarea to debts that has more details flag set to true
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $profDeputyOtherCost = $event->getData();
            /* @var $profDeputyOtherCost ProfDeputyOtherCost */
            $form = $event->getForm();

            if ($profDeputyOtherCost->getHasMoreDetails()) {
                $form->add('moreDetails', FormTypes\TextareaType::class, []);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfDeputyOtherCost::class,
            'validation_groups' => ['prof-deputy-other-costs'],
            'translation_domain' => 'report-prof-deputy-costs',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'prof_cost_single';
    }
}
