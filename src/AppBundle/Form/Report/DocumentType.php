<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('wishToProvideDocumentation', 'choice', [
            'choices'  => ['yes' => 'Yes', 'no' => 'No'],
            'expanded' => true,
        ]);

        $builder->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-documents',
            'validation_groups'  => function (FormInterface $form) {
                /* @var $data Report */
                $data = $form->getData();

                $validationGroups = ['wish-to-provide-documentation'];
                if ($data->getWishToProvideDocumentation() == 'yes') {
                    $validationGroups = ['documents-provide-documents'];
                }

                return $validationGroups;
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'document';
    }
}
