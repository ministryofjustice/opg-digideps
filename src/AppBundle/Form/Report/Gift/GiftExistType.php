<?php

namespace AppBundle\Form\Report\Gift;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class GiftExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('giftsExist', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
//                'constraints' => [new NotBlank(['message' => 'gifts.giftsExist.notBlank', 'groups' => ['exist']])],
            ])
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-gifts',
            'validation_groups' => ['gifts-exist'],
        ]);
    }

    public function getName()
    {
        return 'gift_exist';
    }
}
