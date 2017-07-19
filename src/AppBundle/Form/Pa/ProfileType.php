<?php

namespace AppBundle\Form\Pa;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ProfileType
 **
 * @package AppBundle\Form\Pa
 */
class ProfileType extends AbstractType
{
    /**
     * @var User
     */
    private $loggedInUser = null;

    /**
     * @param $loggedInUser
     */
    public function __construct(User $loggedInUser)
    {
        $this->loggedInUser = $loggedInUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text', ['required' => true])
            ->add('lastname' , 'text', ['required' => true])
            ->add('email'    , 'text', ['required' => true])
            ->add('jobTitle' , 'text', ['required' => !empty($this->loggedInUser)])
            ->add('phoneMain', 'text', ['required' => !empty($this->loggedInUser)]);

            if ($this->loggedInUser->isPaAdministrator()) {
                $builder->add('removeAdmin', 'choice', [
                    'choices' => ['remove-admin' => 'Give up administrator rights'],
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
                    'mapped' => false
                ]);
            }

        $builder->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'pa-profile',
            'validation_groups'  => ['user_details_pa'],
            'data_class'         => User::class,
        ]);
    }

    public function getName()
    {
        return 'profile';
    }
}
