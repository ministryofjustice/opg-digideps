<?php

namespace AppBundle\Form\Odr\IncomeBenefit;

use AppBundle\Entity\Odr\Odr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OneOffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('oneOff', 'collection', [
                'type' => new IncomeBenefitSingleType(),
            ])
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Odr\Odr',
            'validation_groups' => ['odr-one-off'],
            'cascade_validation' => true,
            'translation_domain' => 'odr-income-benefits',
        ]);
    }

    public function getName()
    {
        return 'odr_income_one_off';
    }
}
