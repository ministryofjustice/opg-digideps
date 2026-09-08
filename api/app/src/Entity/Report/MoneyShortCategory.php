<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'money_short_category')]
#[ORM\Entity, ORM\HasLifecycleCallbacks]
class MoneyShortCategory
{
    /**
     * @var array<string, array<string, array<never>>> $categories
     */
    private static array $categories = [
        'in' => [
            // typeId => options
            'state_pension_and_benefit' => [],
            'bequests' => [],
            'income_from_invesments_dividends_rental' => [],
            'sale_of_investments_property_assets' => [],
            'salary_or_wages' => [],
            'compensations_and_damages_awards' => [],
            'personal_pension' => [],
        ], 'out' => [
            // typeId => options
            'accomodation_costs' => [],
            'care_fees' => [],
            'holidays' => [],
            'households_bills' => [],
            'personal_allowance' => [],
            'professional_fees' => [],
            'new_investments' => [],
            'travel_costs' => [],
        ]];

    /**
     * "in" or "out" or null for both
     *
     * @return array<string, array<never>> [typeId=>[]]
     */
    public static function getCategories(?string $type): array
    {
        return match ($type) {
            'in' => self::$categories['in'],
            'out' => self::$categories['out'],
            default => self::$categories['in'] + self::$categories['out'],
        };
    }

    #[JMS\Type('integer')]
    #[JMS\Groups(['moneyShortCategoriesIn', 'moneyShortCategoriesOut'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'money_short_category_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'moneyShortCategories')]
    private Report $report;

    #[JMS\Groups(['moneyShortCategoriesIn', 'moneyShortCategoriesOut'])]
    #[ORM\Column(name: 'type_id', type: 'string', nullable: false)]
    private string $typeId;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['moneyShortCategoriesIn', 'moneyShortCategoriesOut'])]
    #[ORM\Column(name: 'present', type: 'boolean', nullable: true)]
    private ?bool $present;

    public function __construct(Report $report, string $typeId, bool $present)
    {
        $this->report = $report;
        $this->typeId = $typeId;
        $this->present = $present;
    }

    /**
     * null|in|out
     */
    public function getType(): ?string
    {
        foreach (['in', 'out'] as $type) {
            foreach (self::getCategories($type) as $typeId => $options) {
                if ($typeId == $this->typeId) {
                    return $type;
                }
            }
        }

        return null;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): void
    {
        $this->report = $report;
    }

    public function getTypeId(): string
    {
        return $this->typeId;
    }

    public function setTypeId(string $typeId): static
    {
        $this->typeId = $typeId;

        return $this;
    }

    public function getPresent(): ?bool
    {
        return $this->present;
    }

    public function setPresent(?bool $present): static
    {
        $this->present = $present;

        return $this;
    }
}
