<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class ReasonForNoContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('reasonForNoContacts', 'textarea', ['constraints' => [new Constraints\NotBlank(['message' => 'contact.no-contact-reason.notBlank']),
                     ]])
                ->add('save', 'submit');

        $builder->setAction($options['action']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'report-contacts',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'reason_for_no_contact';
    }
}
