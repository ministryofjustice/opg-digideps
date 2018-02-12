<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\Ndr\OneOff;
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

    public function getName()
    {
        return 'unsubmitted_section';
    }
}
