<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Expense;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportDeputyExpenseTrait
{
    #[JMS\Type('string')]
    #[JMS\Groups(['expenses-paid-anything'])]
    #[Assert\NotBlank(message: 'expenses.paidForAnything.notBlank', groups: ['expenses-paid-anything'])]
    private ?string $paidForAnything = null;

    /**
     * @var Expense[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Expense>')]
    #[JMS\Groups(['expenses'])]
    private array $expenses = [];

    #[JMS\Type('double')]
    #[JMS\Groups(['expenses-total'])]
    private float $expensesTotal;

    public function getPaidForAnything(): ?string
    {
        return $this->paidForAnything;
    }

    public function setPaidForAnything(string $paidForAnything): static
    {
        $this->paidForAnything = $paidForAnything;

        return $this;
    }

    /**
     * Returns expenses in ascending createdAt order. Does not change the order of the underlying $this->expenses.
     *
     * @return Expense[]
     */
    public function getExpenses(): array
    {
        $expenses = [...$this->expenses];
        uasort($expenses, fn ($exp1, $exp2) => $exp1 <=> $exp2);
        return $this->expenses;
    }

    public function setExpenses(mixed $expenses): static
    {
        $this->expenses = $expenses;

        return $this;
    }

    public function addExpense(Expense $expense): static
    {
        $this->expenses[] = $expense;

        return $this;
    }

    public function getExpensesTotal(): float
    {
        return $this->expensesTotal;
    }
}
