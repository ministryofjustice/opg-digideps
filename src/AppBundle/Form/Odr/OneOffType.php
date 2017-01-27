<?php

namespace AppBundle\Form\Odr;

use AppBundle\Entity\Odr\OneOff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OneOffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('typeId', 'hidden')
                 ->add('present', 'checkbox');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
             'data_class' => OneOff::class,
             'validation_groups' => ['odr-one-off'],
             'translation_domain' => 'odr-income-benefits',
        ]);
    }

    public function getName()
    {
        return 'one_off';
    }
}
