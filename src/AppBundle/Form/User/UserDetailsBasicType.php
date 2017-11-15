<?php

namespace AppBundle\Form\User;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDetailsBasicType extends AbstractType
{
    private $user;

    public function __construct(User $user)
    {
        $this->setUser($user);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstname', 'text')
                ->add('lastname', 'text')
                ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'settings',
            'validation_groups' => 'user_details_basic',
        ]);
    }

    public function getName()
    {
        return 'user_details';
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
}
