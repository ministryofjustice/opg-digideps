<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AgreeTermsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id', FormTypes\HiddenType::class)
                ->add('agreeTermsUse', FormTypes\CheckboxType::class, [
                     'constraints' => new NotBlank(['message' => '....']),
                 ])
                ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'pa-terms-use',
            'validation_groups' => ['agree-terms-use'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'agree_terms';
    }
}
