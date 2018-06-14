<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, ['constraints' => [new Constraints\NotBlank(['message' => 'login.email.notBlank']),
                                                             new Constraints\Email(['message' => 'login.email.inValid']), ],
                                        ])
                ->add('password', PasswordType::class, ['constraints' => new Constraints\NotBlank(['message' => 'login.password.notBlank'])])
                ->add('login', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'signin',
              'csrf_message' => 'We’re sorry, but we’re having trouble signing you in at the moment. Please try again in a few minutes.',
              'csrf_token_id' => 'ddloginform',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'login';
    }
}
