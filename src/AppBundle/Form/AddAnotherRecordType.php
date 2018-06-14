<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddAnotherRecordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addAnother', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'mapped' => false,
                'constraints' => [new NotBlank(['message' => "Please select either 'Yes' or 'No'"])],
            ])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([])
            ->setRequired(['translation_domain'])
            ->setAllowedTypes('translation_domain', 'string');
    }

    public function getBlockPrefix()
    {
        return 'add_another';
    }
}
