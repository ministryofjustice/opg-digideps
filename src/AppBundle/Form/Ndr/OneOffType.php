<?php

namespace AppBundle\Form\Ndr;

use AppBundle\Entity\Ndr\OneOff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OneOffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('typeId', 'hidden')
                 ->add('present', 'checkbox');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
             'data_class' => OneOff::class,
             'validation_groups' => ['ndr-one-off'],
             'translation_domain' => 'ndr-income-benefits',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'one_off';
    }
}
