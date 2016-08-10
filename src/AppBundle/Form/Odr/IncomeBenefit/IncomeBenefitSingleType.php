<?php

namespace AppBundle\Form\Odr\IncomeBenefit;

use AppBundle\Entity\Odr\IncomeBenefit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class IncomeBenefitSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('typeId', 'hidden')
                 ->add('present', 'checkbox');

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $incomeBenefit = $event->getData(); /* @var $accountTransaction IncomeBenefit */
            $form = $event->getForm();

            if ($incomeBenefit->getHasMoreDetails()) {
                $form->add('moreDetails', 'textarea');
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
             'data_class' => 'AppBundle\Entity\Odr\IncomeBenefit',
             'validation_groups' => ['odr-state-benefits'],
             'translation_domain' => 'odr-income-benefits',
        ]);
    }

    public function getName()
    {
        return 'odr_income_benefit_single';
    }
}
