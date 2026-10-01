<?php

namespace OPG\Digideps\Frontend\Form\Admin;

use OPG\Digideps\Frontend\Entity\Report\UnsubmittedSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnsubmittedSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('id', HiddenType::class)
            ->add('present', CheckboxType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
             'data_class' => UnsubmittedSection::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'unsubmitted_section';
    }
}
