<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TransactionsType extends AbstractType
{
    private $property;

    /**
     * TransactionsType constructor.
     *
     * @param $property
     */
    public function __construct($property)
    {
        if (!in_array($property, ['transactionsIn', 'transactionsOut'])) {
            throw new \InvalidArgumentException(__METHOD__.": $property not valid");
        }
        $this->property = $property;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('id', 'hidden')
                 ->add($this->property,  'collection', ['type' => new TransactionSingleType()])
                 ->add('save', 'submit')
                ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
             'data_class' => 'AppBundle\Entity\Report\Report',
             'validation_groups' => ['transactions'],
             // enable validation on AccountTransactionSingleType collections
             'cascade_validation' => true,
             'translation_domain' => 'report-transactions',
        ]);
    }

    public function getName()
    {
        return 'transactions';
    }
}
