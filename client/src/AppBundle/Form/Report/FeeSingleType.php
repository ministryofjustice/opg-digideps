<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Fee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeeSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('feeTypeId', FormTypes\HiddenType::class)
            ->add('amount', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                'invalid_message' => 'fee.amount.notNumeric',
            ]);

        // add textarea to fees that has more details flag set to true
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $fee = $event->getData();
            /* @var $fee Fee */
            $form = $event->getForm();

            if ($fee->getHasMoreDetails()) {
                $form->add('moreDetails', FormTypes\TextareaType::class, [
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Report\Fee',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data \AppBundle\Entity\Report\Fee */
                $validationGroups = ['fees'];

                if ($data->getAmount() && $data->getHasMoreDetails()) {
                    $validationGroups[] = 'fees-more-details';
                }

                return $validationGroups;
            },
            'translation_domain' => 'report-pa-fee-expense',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'fee_single';
    }
}
