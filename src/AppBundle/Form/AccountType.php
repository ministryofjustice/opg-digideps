<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Form\Type\AccountNumberType;
use Symfony\Component\Validator\Constraints as Constraints;

class AccountType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         $builder ->add('bank', 'text')
                  ->add('openingDate', 'date', [ 'widget' => 'text',
                                                 'input' => 'datetime',
                                                 'format' => 'yyyy-MM-dd',
                                                 'invalid_message' => 'account.openingDate.invalidMessage'
                                          ])
                  ->add('openingBalance','text')
                  ->add('sortCode',new SortCodeType(), [ 'constraints' => new Constraints\NotBlank() ])
                  ->add('accountNumber', new AccountNumberType())
                  ->add('save', 'submit');
     }
     
     public function setDefaultOptions(OptionsResolverInterface $resolver)
     {
         $resolver->setDefaults( [
            'translation_domain' => 'report-accounts'
        ]);
     }
     
     public function getName()
     {
         return 'account';
     }
     
}