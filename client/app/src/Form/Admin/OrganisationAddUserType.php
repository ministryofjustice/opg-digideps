<?php

namespace App\Form\Admin;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationAddUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', FormTypes\EmailType::class, [
                'required' => true,
                'constraints' => [
                    new Email()
                ]
            ])
            ->add('retrieve', FormTypes\SubmitType::class)
            ->add('confirm', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-organisation-users',
        ]);
    }
}
