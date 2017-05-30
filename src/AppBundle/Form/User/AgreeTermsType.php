<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AgreeTermsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id', 'hidden')
                ->add('agreeTermsUse', 'checkbox', [
                     'constraints' => new NotBlank(['message' => '....']),
                 ])
                ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'pa-terms-use',
            'validation_groups' => ['agree-terms-use'],
        ]);
    }

    public function getName()
    {
        return 'agree_terms';
    }
}
