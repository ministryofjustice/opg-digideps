<?php

namespace AppBundle\Form\Report;

use AppBundle\Validator\Constraints\Chain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Entity\Report\Account;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class MoneyTransactionType extends AbstractType
{
    private $step;
    private $categories;
    private $ids;

    /**
     * MoneyTransactionType constructor.
     * @param $step
     * @param $categories
     * @param $ids
     */
    public function __construct($step, $categories, $ids)
    {
        $this->step = (int)$step;
        $this->categories = $categories;
        $this->ids = $ids;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');

        if ($this->step === 1) {
            $builder->add('category', 'choice', [
                'choices' =>  $this->categories,
                'expanded' => true,
            ]);
        }

        if ($this->step === 2) {
            $builder->add('id', 'choice', [
                'choices' => $this->ids,
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('moreDetails', 'textarea');
            $builder->add('amounts', 'collection', [
                'entry_type' => 'number',
                'allow_add' => true, //allow new fields added with JS
                'entry_options' => [
                    'error_bubbling' => false,
                    'precision' => 2,
                    'grouping' => true,
                    'invalid_message' => 'moneyIn.form.amounts.type',
                ],
            ]);
//            $builder->add('createdAt', 'date', ['widget' => 'text',
//                'mapped' => false, // Not in the model
//                'input' => 'datetime',
//                'format' => 'dd-MM-yyyy',
//                'invalid_message' => 'Enter a valid date',
//            ]);
        }

        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'account';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-money-transaction',
            'choice_translation_domain' => 'report-money-transaction',
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData();
                /* @var $data \AppBundle\Entity\Report\Transaction */

                $validationGroups = [];

                if ($this->step === 1) {
                    $validationGroups = ['transaction-in-category'];
                }

                if ($this->step === 2) {
                    $validationGroups = ['transaction-in-id'];
                }

                if ($this->step === 3) {
                    $validationGroups = ['transaction-in-amounts'];
                }

                return $validationGroups;
            },
        ]);
    }
}
