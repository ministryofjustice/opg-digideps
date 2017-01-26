<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class MoneyTransferExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('noTransfersToAdd', 'choice', [
                'choices' => [0 => 'Yes', 1 => 'No'],
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => 'transfer.exist.notBlank', 'groups' => ['exist']])],
            ])
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-money-transfer',
            'validation_groups' => ['exist'],
        ]);
    }

    public function getName()
    {
        return 'money_transfer_exist';
    }
}
