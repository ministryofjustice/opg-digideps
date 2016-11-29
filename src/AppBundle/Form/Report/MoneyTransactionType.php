<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Transaction;
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
    private $type;
    private $selectedGroup;
    private $selectedCategory;

    /**
     * MoneyTransactionType constructor.
     * @param integer $step
     * @param $type in|out
     * @param string $selectedGroup
     * @param null|string $selectedCategory
     */
    public function __construct($step, $type, $selectedGroup, $selectedCategory = null)
    {
        $this->step = (int)$step;
        $this->type = $type;
        $this->selectedGroup = $selectedGroup;
        $this->selectedCategory = $selectedCategory;
    }

    private function getGroups()
    {
        $ret = [];

        foreach(Transaction::$categories as $cat){
            list($categoryId, $hasDetails, $order, $groupId, $type) = $cat;
            if ($type == $this->type) {
                $ret[$groupId] = 'form.group.entries.' . $groupId;
            }
        }

        return array_unique($ret);
    }

    private function getCategories()
    {
        $ret = [];

        foreach(Transaction::$categories as $cat){
            list($categoryId, $hasDetails, $order, $groupId, $type) = $cat;
            if ($groupId == $this->selectedGroup) {
                $ret[$categoryId] = 'form.category.entries.' . $categoryId . '.label';
            }
        }

        return $ret;
    }

    /**
     * @return boolean
     */
    private function isDescriptionMandatory()
    {
        foreach(Transaction::$categories as $cat){
            list($categoryId, $hasDetails, $order, $groupId, $type) = $cat;
            if ($categoryId == $this->selectedCategory) {
                return $hasDetails;
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');

        if ($this->step === 1) {
            $builder->add('group', 'choice', [
                'choices' =>  $this->getGroups(),
                'expanded' => true,
            ]);
        }

        if ($this->step === 2) {
            $builder->add('category', 'choice', [
                'choices' =>  $this->getCategories(),
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('description', 'textarea', [
                'required' => $this->isDescriptionMandatory(),
            ]);
//            $builder->add('amount', 'collection', [
//                'entry_type' => 'number',
//                'allow_add' => true, //allow new fields added with JS
//                'entry_options' => [
//                    'error_bubbling' => false,
//                    'precision' => 2,
//                    'grouping' => true,
//                    'invalid_message' => 'moneyIn.form.amounts.type',
//                ],
//            ]);

            $builder->add('amount', 'number', [
                'precision' => 2,
                'grouping' => true,
                'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                'invalid_message' => 'moneyIn.form.amount.type',
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
                    $validationGroups[] = 'transaction-in-group';
                }
                if ($this->step === 2) {
                    $validationGroups[] = 'transaction-in-category';
                }
                if ($this->step === 3) {
                    $validationGroups[] = 'transaction-in-amount';
                    if ($this->isDescriptionMandatory()) {
                        $validationGroups[] = 'transaction-in-description';
                    }
                }

                return $validationGroups;
            },
        ]);
    }
}
