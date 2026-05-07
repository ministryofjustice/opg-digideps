<?php

namespace OPG\Digideps\Frontend\Form\Report;

use OPG\Digideps\Frontend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MoneyTransactionType extends AbstractType
{
    private AuthorizationCheckerInterface $authorizationChecker;

    private int $step;

    private string $type;

    private ?string $selectedCategory;

    /**
     * @return array where keys and values are the categoriesID. e.g. [broadband=>null, fees=>null]
     */
    private function getCategories(): array
    {
        $ret = [];

        /** @var array[] $categories */
        $categories = MoneyTransaction::$categories;
        foreach ($categories as $cat) {
            /** @var string $categoryId */
            $categoryId = $cat[0];
            $type = $cat[3];

            // filter by user roles (if specified)
            $allowedRole = $cat[4] ?? null;

            $isCategoryAllowedForThisRole = null === $allowedRole || $this->authorizationChecker->isGranted($allowedRole);
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
    private function isDescriptionMandatory(): bool
    {
        /** @var array[] $categories */
        $categories = MoneyTransaction::$categories;
        foreach ($categories as $row) {
            /** @var string $categoryId */
            $categoryId = $row[0];
            /** @var bool $hasDetails */
            $hasDetails = $row[1];
            if ($categoryId == $this->selectedCategory) {
                return $hasDetails;
            }
        }
        return false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var int $step */
        $step = $options['step'];
        /** @var AuthorizationCheckerInterface $authorizationChecker */
        $authorizationChecker = $options['authChecker'];
        /** @var string|null $selectedCategory */
        $selectedCategory = $options['selectedCategory'];
        /** @var string $type */
        $type = $options['type'];

        $this->authorizationChecker = $authorizationChecker;
        $this->step = $step;
        $this->type = $type;
        $this->selectedCategory = $selectedCategory;

        $builder->add('id', FormTypes\HiddenType::class);

        if (1 === $this->step) {
            $builder->add('category', FormTypes\ChoiceType::class, [
                'choices' => $this->getCategories(),
                'expanded' => true,
            ]);
        }

        if (2 === $this->step) {
            $builder->add('description', FormTypes\TextareaType::class, [
                'required' => $this->isDescriptionMandatory(),
            ]);

            $builder->add('amount', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                'invalid_message' => 'moneyTransaction.form.amount.type',
            ]);

            /** @var Report $report */
            $report = $options['report'];
            /** @var string $type */
            $type = $this->type;
            if (!empty($report->getBankAccountOptions()) && $report->canLinkToBankAccounts()) {
                $builder->add('bankAccountId', FormTypes\ChoiceType::class, [
                    'choices' => $report->getBankAccountOptions(),
                    'placeholder' => 'Please select',
                    'label' => 'form.bankAccount.money' . ucfirst($type) . '.label',
                    'required' => false,
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
            'selectedCategory' => null,
            'translation_domain' => 'report-money-transaction',
            'choice_translation_domain' => 'report-money-transaction',
            'validation_groups' => function () {
                $validationGroups = [];
                if (1 === $this->step) {
                    $validationGroups[] = 'transaction-category';
                }
                if (2 === $this->step) {
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
    }
}
