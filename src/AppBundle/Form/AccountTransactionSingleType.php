<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AccountTransactionSingleType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         $builder 
                 ->add('id', 'hidden')
//                 ->add('hasMoreDetails', 'hidden')
                 ->add('amount', 'text');
         
         $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $accountTransaction = $event->getData();
            $form = $event->getForm();

            // check if the Product object is "new"
            // If no data is passed to the form, the data is "null".
            // This should be considered a new "Product"
            if ($accountTransaction->getHasMoreDetails()) {
                $form->add('moreDetails', 'textarea');
            }
        });
     }
     
     public function setDefaultOptions(OptionsResolverInterface $resolver)
     {
         $resolver->setDefaults( [
             'data_class' => 'AppBundle\Entity\AccountTransaction',
        ]);
     }
     
     public function getName()
     {
         return 'transaction_single';
     }
     
}