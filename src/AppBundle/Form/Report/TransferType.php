<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TransferType extends AbstractType
{
    private $banks;

    /**
     * @param \AppBundle\Entity\Report\Account[] $banks
     */
    public function __construct(array $banks)
    {
        $this->banks = [];

        foreach ($banks as $bank) {
            /* $var $bank \AppBundle\Entity\Report\Account */
            $this->banks[$bank->getId()] = $bank->getBank().' '.$bank->getAccountTypeText().' (****'.$bank->getAccountNumber().')';
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accountFromId', 'choice', [
                'choices' => $this->banks, 'empty_value' => 'Please select', ]
            )->add('accountToId', 'choice', [
                'choices' => $this->banks, 'empty_value' => 'Please select', ]
            )
            ->add('amount', 'text')
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-transfers',
        ]);
    }

    public function getName()
    {
        return 'transfers';
    }
}
