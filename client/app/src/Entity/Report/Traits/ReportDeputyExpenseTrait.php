<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\Expense;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportDeputyExpenseTrait
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"expenses-paid-anything"})
     * @Assert\NotBlank(message="ndr.expenses.paidForAnything.notBlank", groups={"expenses-paid-anything"})
     */
    private ?string $paidForAnything;

    /**
     * @JMS\Type("array<App\Entity\Report\Expense>")
     * @JMS\Groups({"expenses"})
     *
     * @var Expense[]
     */
    private array $expenses = [];

    /**
     * @JMS\Type("double")
     * @JMS\Groups({"expenses-total"})
     */
    private $expensesTotal;

    public function getPaidForAnything(): string
    {
        return $this->paidForAnything;
    }

    public function setPaidForAnything(string $paidForAnything): static
    {
        $this->paidForAnything = $paidForAnything;

        return $this;
    }

    /**
     * @return Expense[]
     */
    public function getExpenses(): array
    {
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

    public function getExpensesTotal(): string
    {
        return $this->expensesTotal;
    }

    public function setExpensesTotal(string $expensesTotal): void
    {
        $this->expensesTotal = $expensesTotal;
    }
}
