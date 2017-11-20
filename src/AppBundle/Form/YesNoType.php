<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Generic type for Yes/No questions with
 * - single field yes/no (pass the name via ctor)
 * - notBlank validator
 * - save button
 */
class YesNoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($options['field'], 'choice', [
                'choices'     => $options['choices'],
                'expanded'    => true,
                'constraints' => [new NotBlank(['message' => "Please select either 'Yes' or 'No'", 'groups'=>'yesno_type_custom'])],
            ])
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([ 'validation_groups'  => ['yesno_type_custom']
                               , 'choices'            => ['yes' => 'Yes', 'no' => 'No']
                               ])
                 ->setRequired(['field'])
                 ->setAllowedTypes('translation_domain', 'string');
    }

    public function getName()
    {
        return 'yes_no';
    }
}
