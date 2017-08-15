<?php

namespace AppBundle\Form\Settings;

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
    protected $validationGroups;

    /**
     * ProfileType constructor.
     * @param $validationGroups array
     */
    public function __construct($validationGroups)
    {
        $this->validationGroups = $validationGroups;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $loggedInUser = $builder->getData();

        $builder
            ->add('firstname', 'text', ['required' => true])
            ->add('lastname' , 'text', ['required' => true])
            ->add('address1' , 'text')
            ->add('address2' , 'text')
            ->add('address3' , 'text')
            ->add('addressPostcode' , 'text')
            ->add('addressCountry', 'country', ['preferred_choices' => ['', 'GB'], 'empty_value' => 'Please select ...',])
            ->add('phoneMain', 'text', ['required' => true])
            ->add('phoneAlternative', 'text')
            ->add('email'    , 'text', ['required' => true]);

            if ($loggedInUser->isDeputyPa()) {
                $builder->add('jobTitle', 'text', ['required' => true]);
            }

            if ($loggedInUser->isPaAdministrator()) {
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
            'translation_domain' => 'settings',
            'validation_groups'  => $this->validationGroups,
            'data_class'         => User::class,
        ]);
    }

    public function getName()
    {
        return 'profile';
    }
}
