<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\MoneyTransaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class MoneyTransactionType extends AbstractType
{
    private $clientFirstName;
    private $step;
    private $type;
    private $selectedGroup;
    private $selectedCategory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private function getGroups()
    {
        $ret = [];

        foreach (MoneyTransaction::$categories as $cat) {
            list($categoryId, $hasDetails, $order, $groupId, $type) = $cat;
            if ($type == $this->type) {
                $ret[$groupId] = $this->translate('form.group.entries.' . $groupId);
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
                $ret[$categoryId] = $this->translate('form.category.entries.' . $categoryId . '.label');
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
        $this->step             = (int) $options['step'];
        $this->type             = $options['type'];
        $this->selectedGroup    = $options['selectedGroup'];
        $this->selectedCategory = $options['selectedCategory'];
        $this->translator       = $options['translator'];
        $this->clientFirstName  = $options['clientFirstName'];

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'selectedCategory' => null,
            'translation_domain' => 'report-money-transaction',
            'choice_translation_domain' => 'report-money-transaction',
            'validation_groups' => function (FormInterface $form) {

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
        ])
        ->setRequired(['step', 'type', 'translator', 'clientFirstName', 'selectedGroup'])
        ->setAllowedTypes('translator', TranslatorInterface::class);
    }
}
