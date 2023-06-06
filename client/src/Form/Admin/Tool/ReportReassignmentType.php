<?php

namespace App\Form\Admin\Tool;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportReassignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstClientId', FormTypes\TextType::class)
            ->add('secondClientId', FormTypes\TextType::class)
            ->add('submit', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-tools',
        ]);
    }
}
