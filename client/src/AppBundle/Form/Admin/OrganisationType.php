<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class OrganisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('name', FormTypes\TextType::class)
            ->add('emailIdentifier', FormTypes\HiddenType::class)
            ->add('emailIdentifierType', FormTypes\ChoiceType::class, [
                'choices' => [
                    'They own an email domain' => 'domain',
                    'They have an email address on a shared domain' => 'address',
                ],
                'expanded' => true,
                'mapped' => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'organisation.emailIdentifierType.notBlank'])
                ],
            ])
            ->add('emailAddress', FormTypes\TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'organisation.emailAddress.notBlank',
                        'groups' => 'email-address',
                    ]),
                    new Constraints\Length([
                        'max' => 256,
                        'maxMessage' => 'organisation.emailAddress.maxLength',
                        'groups' => 'email-address',
                    ]),
                    new Constraints\Email([
                        'message' => 'organisation.emailAddress.invalid',
                        'groups' => 'email-address',
                    ])
                ]
            ])
            ->add('emailDomain', FormTypes\TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'organisation.emailDomain.notBlank',
                        'groups' => 'email-domain',
                    ]),
                    new Constraints\Length([
                        'max' => 256,
                        'maxMessage' => 'organisation.emailDomain.maxLength',
                        'groups' => 'email-domain',
                    ]),
                    new Constraints\Regex([
                        'pattern' => '/^[^ @]+$/i',
                        'htmlPattern' => '^[^ @]+$',
                        'message' => 'organisation.emailDomain.invalid',
                        'groups' => 'email-domain',
                    ])
                ]
            ])
            ->add('isActivated', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => true, 'No' => false],
                'expanded' => true,
                'data' => false,
            ])
            ->add('save', FormTypes\SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $field = 'emailAddress';

            if (isset($data['emailIdentifierType']) && $data['emailIdentifierType'] === 'domain') {
                $field = 'emailDomain';
            }

            $data['emailIdentifier'] = $data[$field];
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-organisations',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $type = $form->get('emailIdentifierType')->getData();

                if ($type === 'domain') {
                    return ['Default', 'email-domain'];
                } else if ($type === 'address') {
                    return ['Default', 'email-address'];
                }

                return ['Default'];
            },
        ]);
    }
}
