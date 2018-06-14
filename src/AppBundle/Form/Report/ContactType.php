<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contactName', FormTypes\TextType::class, ['label' => 'Contact name'])
                ->add('relationship', FormTypes\TextType::class, ['label' => 'Relationship to the client'])
                ->add('explanation', FormTypes\TextareaType::class, ['label' => 'Reason for contact'])
                ->add('address', FormTypes\TextType::class)
                ->add('address2', FormTypes\TextType::class)
                ->add('county', FormTypes\TextType::class)
                ->add('postcode', FormTypes\TextType::class)
                ->add('id', FormTypes\HiddenType::class)
                ->add('country', FormTypes\CountryType::class, [
                      'preferred_choices' => ['GB'],
                      'empty_value' => 'form.country.defaultOption',
                ])
                ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-contacts',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'contact';
    }
}
