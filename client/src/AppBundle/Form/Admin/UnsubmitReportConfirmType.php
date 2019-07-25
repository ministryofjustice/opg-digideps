<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class UnsubmitReportConfirmType extends AbstractType
{
    const DUE_DATE_OPTION_CUSTOM = 'custom';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('unsubmittedSection', FormTypes\HiddenType::class)
            ->add('startDate', FormTypes\HiddenType::class)
            ->add('endDate', FormTypes\HiddenType::class)
            ->add('dueDate', FormTypes\HiddenType::class)
            ->add('confirm', FormTypes\ChoiceType::class, [
                'choices'            => ['Yes' => 'yes', 'No' => 'no'],
                'mapped'             => false,
                'expanded'           => true,
                'constraints' => [new Constraints\NotBlank(['message' => "Please select either 'Yes' or 'No'"])],
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-clients',
            'name'               => 'report',
        ]);
    }
}
