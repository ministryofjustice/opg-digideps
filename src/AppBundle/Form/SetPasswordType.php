<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
<<<<<<< HEAD
use Symfony\Component\OptionsResolver\OptionsResolver;
=======
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Constraints;
>>>>>>> ad0392ed076e36d87eb2bd1043148b3203e184fe

class SetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
<<<<<<< HEAD
                ->add('password'
                     , 'repeated'
                     , [ 'type'            => 'password'
                       , 'invalid_message' => $options['passwordMismatchMessage']
                       ]
                     )
                ->add('save', 'submit');
=======
            ->add('password', 'repeated', [
                'type' => 'password',
                'invalid_message' => $this->options['passwordMismatchMessage'],
            ]);

        if (!empty($this->options['showTermsAndConditions'])) {
            $builder->add('showTermsAndConditions', 'checkbox', [
                'mapped'=>false,
                'constraints' => [new Constraints\NotBlank(['message' => 'user.agreeTermsUse.notBlank', 'groups'=>['user_set_password']])]
            ]);
        }
        $builder->add('save', 'submit');
>>>>>>> ad0392ed076e36d87eb2bd1043148b3203e184fe
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'user-activate',
               'validation_groups' => ['user_set_password'],
        ])
        ->setRequired(['passwordMismatchMessage']);
    }

    public function getName()
    {
        return 'set_password';
    }
}
