<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\MoneyTransaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Type;

class MoneyTransactionType extends AbstractType
{
    private $step;
    private $type;
    private $selectedGroup;
    private $selectedCategory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * MoneyTransactionType constructor.
     *
     * @param $step
     * @param $type
     * @param TranslatorInterface $translator
     * @param $clientFirstName
     * @param $selectedGroup
     * @param null $selectedCategory
     */
    public function __construct($step, $type, TranslatorInterface $translator, $clientFirstName, $selectedGroup, $selectedCategory = null)
    {
        $this->step = (int) $step;
        $this->type = $type;
        $this->selectedGroup = $selectedGroup;
        $this->selectedCategory = $selectedCategory;
        $this->translator = $translator;
        $this->clientFirstName = $clientFirstName;
    }

    private function getGroups()
    {
        $ret = [];

        foreach (MoneyTransaction::$categories as $cat) {
            list($categoryId, $hasDetails, $order, $groupId, $type) = $cat;
            if ($type == $this->type) {
                $ret[$groupId] = $this->translate('form.group.entries.'.$groupId);
            }
        }

        return array_unique($ret);
    }

    private function getCategories()
    {
        $ret = [];

        foreach (MoneyTransaction::$categories as $cat) {
            list($categoryId, $hasDetails, $order, $groupId, $type) = $cat;
            if ($groupId == $this->selectedGroup) {
                $ret[$categoryId] = $this->translate('form.category.entries.'.$categoryId.'.label');
            }
        }

        return $ret;
    }

    /**
     * @return bool
     */
    private function isDescriptionMandatory()
    {
        foreach (MoneyTransaction::$categories as $cat) {
            list($categoryId, $hasDetails, $order, $groupId, $type) = $cat;
            if ($categoryId == $this->selectedCategory) {
                return $hasDetails;
            }
        }
    }

    private function translate($key)
    {
        return $this->translator->trans($key, ['%client%' => $this->clientFirstName], 'report-money-transaction');
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

            $builder->add('amount', 'number', [
                'precision' => 2,
                'grouping' => true,
                'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                'invalid_message' => 'moneyTransaction.form.amount.type',
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
                /* @var $data \AppBundle\Entity\Report\MoneyTransaction */

                $validationGroups = [];

                if ($this->step === 1) {
                    $validationGroups[] = 'transaction-group';
                }
                if ($this->step === 2) {
                    $validationGroups[] = 'transaction-category';
                }
                if ($this->step === 3) {
                    $validationGroups[] = 'transaction-amount';
                    if ($this->isDescriptionMandatory()) {
                        $validationGroups[] = 'transaction-description';
                    }
                }

                return $validationGroups;
            },
        ]);
    }
}
