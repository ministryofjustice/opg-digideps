<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['report_submitted']) {
            $builder
                ->add('files', FileType::class, [
                    'required' => false,
                    'multiple' => true,
                ])
                ->add('save', FormTypes\SubmitType::class);
        }
        $builder
            ->add('files', FileType::class, [
                'required' => false,
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => ['document'],
            'translation_domain' => 'report-documents',
            'report_submitted' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'report_document_upload';
    }
}
