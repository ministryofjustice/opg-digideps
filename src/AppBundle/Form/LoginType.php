<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Constraints;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', ['constraints' => [new Constraints\NotBlank(['message' => 'login.email.notBlank']),
                                                             new Constraints\Email(['message' => 'login.email.inValid']), ],
                                        ])
                ->add('password', 'password', ['constraints' => new Constraints\NotBlank(['message' => 'login.password.notBlank'])])
                ->add('login', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'signin',
        ]);
    }

    public function getName()
    {
        return 'login';
    }
}
