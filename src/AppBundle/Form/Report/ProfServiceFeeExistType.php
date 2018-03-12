<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Translation\TranslatorInterface;

class ProfServiceFeeExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentProfPaymentsReceived', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ])
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-prof-current-fees',
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = ['current-prof-payments-received'];

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'prof_service_fees';
    }
}
