<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\MoneyTransaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MoneyTransactionType extends AbstractType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var int
     */
    private $step;

    /**
     * @var string in/out
     */
    private $type;

    /**
     * @var string
     */
    private $selectedCategory;

    /**
     * @return array where keys and values are the categoriesID. e.g. [broadband=>null, fees=>null]
     */
    private function getCategories()
    {
        $ret = [];

        foreach (MoneyTransaction::$categories as $cat) {
            $categoryId = $cat[0];
            $type = $cat[3];
            // filter by user roles (if specified)
            $allowedRoles = isset($cat[4]) ? $cat[4] : null;
            $isCategoryAllowedForThisRole = $allowedRoles === null || $this->authorizationChecker->isGranted($allowedRoles);
            // filter by
            if ($type === $this->type && $isCategoryAllowedForThisRole) {
                $ret[$categoryId] = $categoryId;
            }
        }

        return $ret;
    }

    /**
     * @return bool
     */
    private function isDescriptionMandatory()
    {
        foreach (MoneyTransaction::$categories as $row) {
            $categoryId = $row[0];
            $hasDetails = $row[1];
            if ($categoryId == $this->selectedCategory) {
                return $hasDetails;
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->authorizationChecker = $options['authChecker'];
        $this->step = (int) $options['step'];
        $this->type = $options['type'];
        $this->selectedCategory = $options['selectedCategory'];

        $builder->add('id', FormTypes\HiddenType::class);

        if ($this->step === 1) {
            $builder->add('category', FormTypes\ChoiceType::class, [
                'choices'  => $this->getCategories(),
                'expanded' => true,
            ]);
        }

        if ($this->step === 2) {
            $builder->add('description', FormTypes\TextareaType::class, [
                'required' => $this->isDescriptionMandatory(),
            ]);

            $builder->add('amount', FormTypes\NumberType::class, [
                'scale'       => 2,
                'grouping'        => true,
                'error_bubbling'  => false, // keep (and show) the error (Default behaviour). if true, error is lost
                'invalid_message' => 'moneyTransaction.form.amount.type',
            ]);

            $reportType = $options['report']->getType();

            if (!empty($options['report']->getBankAccountOptions()) && $options['report']->canLinkToBankAccounts()))
            {
                $builder->add('bankAccountId', FormTypes\ChoiceType::class, [
                    'choices' => $options['report']->getBankAccountOptions(),
                    'placeholder' => 'Please select',
                    'label' => 'form.bankAccount.money' . ucfirst($this->type) . '.label'
                ]);
            }
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function getBlockPrefix()
    {
        return 'account';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'selectedCategory'          => null,
            'translation_domain'        => 'report-money-transaction',
            'choice_translation_domain' => 'report-money-transaction',
            'validation_groups'         => function () {
                $validationGroups = [];
                if ($this->step === 1) {
                    $validationGroups[] = 'transaction-category';
                }
                if ($this->step === 2) {
                    $validationGroups[] = 'transaction-amount';
                    if ($this->isDescriptionMandatory()) {
                        $validationGroups[] = 'transaction-description';
                    }
                }

                return $validationGroups;
            },
        ])
        ->setRequired(['report', 'step', 'type', 'authChecker'])
        ->setAllowedTypes('authChecker', AuthorizationCheckerInterface::class);
        ;
    }
}
