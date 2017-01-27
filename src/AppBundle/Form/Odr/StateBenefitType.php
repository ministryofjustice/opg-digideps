<?php

namespace AppBundle\Form\Odr;

use AppBundle\Entity\Odr\StateBenefit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StateBenefitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('typeId', 'hidden')
                 ->add('present', 'checkbox');

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $record = $event->getData();
            $form = $event->getForm();

            if ($record->getHasMoreDetails()) {
                $form->add('moreDetails', 'textarea');
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
             'data_class' => StateBenefit::class,
             'validation_groups' => ['odr-one-off'],
             'translation_domain' => 'odr-income-benefits',
        ]);
    }

    public function getName()
    {
        return 'state_benefit';
    }
}
