<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class ManageReportConfirmType extends AbstractType
{
    const DUE_DATE_OPTION_CUSTOM = 'custom';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('save', FormTypes\SubmitType::class);

        if ($builder->getData()->isSubmitted()) {
            $builder->add('confirm', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'mapped' => false,
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => "Please select either 'Yes' or 'No'"])],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-clients',
            'name'               => 'report',
        ]);
    }
}
