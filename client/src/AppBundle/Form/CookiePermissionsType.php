<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class CookiePermissionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, $options)
    {
        $builder
            ->add('usage', FormTypes\ChoiceType::class, [
                'choices'            => ['Yes' => true, 'No' => false],
                'mapped'             => false,
                'expanded'           => true,
                'constraints' => [new Constraints\NotNull(['message' => "Please select either 'Yes' or 'No'"])],
            ])
            ->add('confirm', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'cookies',
        ]);
    }
}
