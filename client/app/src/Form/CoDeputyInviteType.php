<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoDeputyInviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstname', FormTypes\TextType::class)
                ->add('lastname', FormTypes\TextType::class)
                ->add('email', FormTypes\TextType::class)
                ->add('submit', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'co-deputy',
            'validation_groups' => ['codeputy_invite'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'co_deputy_invite';
    }
}
