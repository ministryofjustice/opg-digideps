<?php

namespace AppBundle\Form\Ndr\Debt;

use AppBundle\Entity\Debt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebtSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('debtTypeId', FormTypes\HiddenType::class)
            ->add('amount', FormTypes\NumberType::class, [
                'precision' => 2,
                'grouping' => true,
                'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                'invalid_message' => 'debt.amount.notNumeric',
            ]);

        // add textarea to debts that has more details flag set to true
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $debt = $event->getData();
            /* @var $debt Debt */
            $form = $event->getForm();

            if ($debt->getHasMoreDetails()) {
                $form->add('moreDetails', FormTypes\TextareaType::class, [
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Ndr\Debt',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data \AppBundle\Entity\Ndr\Debt */
                $validationGroups = ['debts'];

                if ($data->getAmount() && $data->getHasMoreDetails()) {
                    $validationGroups[] = 'debts-more-details';
                }

                return $validationGroups;
            },
            'translation_domain' => 'ndr-debts',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'debt_single';
    }
}
