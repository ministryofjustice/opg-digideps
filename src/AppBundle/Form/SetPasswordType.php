<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class SetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', 'repeated', [ 'type'            => 'password', 'invalid_message' => $options['passwordMismatchMessage']
                       ]
                     );
        if (!empty($options['showTermsAndConditions'])) {
            $builder->add('showTermsAndConditions', 'checkbox', [
                'mapped'=>false,
                'constraints' => [new Constraints\NotBlank(['message' => 'user.agreeTermsUse.notBlank', 'groups'=>['user_set_password']])]
            ]);
        }
        $builder->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
              'translation_domain'     => 'user-activate',
              'validation_groups'      => ['user_set_password'],
              'showTermsAndConditions' => false
        ])
        ->setRequired(['passwordMismatchMessage']);
    }

    public function getName()
    {
        return 'set_password';
    }
}
