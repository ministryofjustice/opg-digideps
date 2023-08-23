<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InactiveAdminReportFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('inactivityPeriod',
                FormTypes\TextType::class,
                [
                    'required' => true,
                    'attr' => ['maxlength' => 2],
                ]
            )
            ->add('submitAndDownload', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-metrics',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin';
    }
}
