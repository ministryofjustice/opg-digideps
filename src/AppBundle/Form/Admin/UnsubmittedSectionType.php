<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\Report\UnsubmittedSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnsubmittedSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('id', 'hidden')
                ->add('present', 'checkbox')
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
