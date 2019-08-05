<?php

namespace AppBundle\Form\Settings;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProfileType
 **
 *
 */
class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $loggedInUser = $builder->getData();

        $builder
            ->add('firstname', FormTypes\TextType::class, ['required' => true])
            ->add('lastname', FormTypes\TextType::class, ['required' => true])
            ->add('address1', FormTypes\TextType::class)
            ->add('address2', FormTypes\TextType::class)
            ->add('address3', FormTypes\TextType::class)
            ->add('addressPostcode', FormTypes\TextType::class)
            ->add('addressCountry', FormTypes\CountryType::class, ['preferred_choices' => ['', 'GB'], 'placeholder' => 'Please select ...',])
            ->add('phoneMain', FormTypes\TextType::class, ['required' => true])
            ->add('phoneAlternative', FormTypes\TextType::class)
            ->add('email', FormTypes\TextType::class, ['required' => true]);

        if ($loggedInUser->isDeputyOrg()) {
            $builder->add('jobTitle', FormTypes\TextType::class, ['required' => true]);
        }

        if ($loggedInUser->isOrgAdministrator()) {
            $builder->add('removeAdmin', FormTypes\CheckboxType::class, [
                    'mapped' => false
                ]);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'settings',
            'data_class'         => User::class,
        ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'profile';
    }
}
