<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\Report\UnsubmittedSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnsubmittedSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('id', FormTypes\HiddenType::class)
                ->add('present', FormTypes\CheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
             'data_class' => UnsubmittedSection::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'unsubmitted_section';
    }
}
