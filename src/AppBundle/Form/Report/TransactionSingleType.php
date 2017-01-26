<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\MoneyTransaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TransactionSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('id', 'hidden')
                 ->add('type', 'hidden')
                 ->add('amounts', 'collection', [
                      'entry_type' => 'number',
                      'allow_add' => true, //allow new fields added with JS
                      'entry_options' => [
                         'error_bubbling' => false,
                         'precision' => 2,
                         'grouping' => true,
                         'invalid_message' => 'account.moneyInOut.amount.notNumeric',
                      ],
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
        $resolver->setDefaults([
             'data_class' => MoneyTransaction::class,
             'validation_groups' => ['transactions'],
             'translation_domain' => 'report-transactions',
        ]);
    }

    public function getName()
    {
        return 'transaction_single';
    }
}
