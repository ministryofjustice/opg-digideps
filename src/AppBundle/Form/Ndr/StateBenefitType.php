<?php

namespace AppBundle\Form\Ndr;

use AppBundle\Entity\Ndr\StateBenefit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StateBenefitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('typeId', FormTypes\HiddenType::class)
                 ->add('present', FormTypes\CheckboxType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $record = $event->getData();
            $form = $event->getForm();

            if ($record->getHasMoreDetails()) {
                $form->add('moreDetails', FormTypes\TextareaType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
             'data_class' => StateBenefit::class,
             'validation_groups' => ['ndr-one-off'],
             'translation_domain' => 'ndr-income-benefits',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'state_benefit';
    }
}
