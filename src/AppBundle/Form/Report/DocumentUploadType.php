<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DocumentUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileName', FileType::class, [
                'required' => false
            ])
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => ['document'],
            'translation_domain' => 'report-documents',
            'data_class' => Document::class
        ]);
    }

    public function getName()
    {
        return 'report_document_upload';
    }
}
