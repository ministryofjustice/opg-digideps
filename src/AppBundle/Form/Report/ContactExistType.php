<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hasContacts', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => 'contact.noContactsChoice.notBlank', 'groups' => ['contact-exist']])],
            ])
            ->add('reasonForNoContacts', FormTypes\TextareaType::class)
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-contacts',
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = ['contact-exist'];
                if ($form['hasContacts']->getData() === 'no') {
                    $validationGroups = ['reasonForNoContacts'];
                }

                return $validationGroups;
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'contact_exist';
    }
}
