<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('wishToProvideDocumentation', FormTypes\ChoiceType::class, [
            'choices'  => ['Yes' => 'yes', 'No' => 'no'],
            'expanded' => true,
        ]);

        $builder->add('save', FormTypes\SubmitType::class);
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
