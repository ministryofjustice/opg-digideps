<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Form\Type\AccountNumberType;

class AccountTransactionsType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         $builder 
                 ->add('id', 'hidden')
                 ->add('moneyIn',  'collection', ['type' => new AccountTransactionSingleType()])
                 ->add('moneyOut', 'collection', ['type' => new AccountTransactionSingleType()])
                 ->add('saveMoneyIn', 'submit')
                 ->add('saveMoneyOut', 'submit')
                ;
     }
     
     public function setDefaultOptions(OptionsResolverInterface $resolver)
     {
         $resolver->setDefaults( [
             'data_class' => 'AppBundle\Entity\Account',
             'validation_groups' => ['transactions'],
             // enable validation on AccountTransactionSingleType collections
             'cascade_validation' => true,
        ]);
     }
     
     public function getName()
     {
         return 'transactions';
     }
     
}