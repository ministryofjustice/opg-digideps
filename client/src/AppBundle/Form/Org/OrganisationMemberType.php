<?php

namespace AppBundle\Form\Org;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use AppBundle\Validator\Constraints\EmailSameDomain;
use AppBundle\Validator\Constraints\EmailSameDomainValidator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class OrganisationMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $targetUser   = $options['targetUser'];

        $builder
            ->add('firstname', FormTypes\TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'user.firstname.notBlankOtherUser']),
                ]
            ])
            ->add('lastname', FormTypes\TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'user.lastname.notBlankOtherUser']),
                ]
            ])
            ->add('email', FormTypes\TextType::class, [
                'required' => true,
                'constraints' => [
                    new EmailSameDomain(['message' => '', 'groups' => ['email_same_domain']]),
                ]
            ])
            ->add('jobTitle', FormTypes\TextType::class, ['required' => !empty($targetUser)])
            ->add('phoneMain', FormTypes\TextType::class, ['required' => !empty($targetUser)]);

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'org-organisation',
            'data_class'         => User::class,
            'validation_groups'  => ['org_team_add', 'email_same_domain'],
            'targetUser'         => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'organisation_member';
    }
}
