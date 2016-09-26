<?php

namespace AppBundle\Form\Odr\Action;

use AppBundle\Entity\Odr\Odr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('actionPropertyMaintenance', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ))
            ->add('actionPropertySellingRent', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ))
            ->add('actionPropertyBuy', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ))
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData();
                /* @var $data Odr */
                $validationGroups = ['action-property'];

                return $validationGroups;
            },
            'translation_domain' => 'odr-action-property',
        ]);
    }

    public function getName()
    {
        return 'odr_action_property';
    }
}
