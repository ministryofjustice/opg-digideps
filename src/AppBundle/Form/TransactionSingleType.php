<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AppBundle\Entity\AccountTransaction;

class TransactionSingleType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         $builder 
                 ->add('id', 'hidden')
                 ->add('type', 'hidden')
                 ->add('amount', 'number', [
                     'error_bubbling' => false,
                     'precision' => 2,
                     'invalid_message'=>'account.moneyInOut.amount.notNumeric'
                 ]);
         
         $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $transaction = $event->getData(); /* @var $accountTransaction Transaction */
            $form = $event->getForm();

            if ($transaction->getHasMoreDetails()) {
                $form->add('moreDetails', 'textarea');
            }
        });
     }
     
     public function setDefaultOptions(OptionsResolverInterface $resolver)
     {
         $resolver->setDefaults( [
             'data_class' => 'AppBundle\Entity\Transaction',
             'validation_groups' => ['transactions'],
             'translation_domain' => 'report-transactions',
        ]);
     }
     
     public function getName()
     {
         return 'transaction_single';
     }
     
}
