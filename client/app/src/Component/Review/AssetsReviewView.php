<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\Review;

use OPG\Digideps\Frontend\Component\GovUk\List\ListBuilder;
use OPG\Digideps\Frontend\Component\GovUk\List\ListEntries;
use OPG\Digideps\Frontend\Component\GovUk\Table\Cell;
use OPG\Digideps\Frontend\Component\GovUk\Table\Table;
use OPG\Digideps\Frontend\Component\GovUk\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\Asset;
use OPG\Digideps\Frontend\Entity\Report\AssetOther;
use OPG\Digideps\Frontend\Entity\Report\AssetProperty;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AssetsReviewView
{
    private const string NUMERIC_FORMAT = ''; //Should be 'numeric' but that would be inconsistent with other tables currently

    public ?ListEntries $list = null;
    /**
     * @var array<Table>
     */
    public array $tables = [];
    /**
     * @var array<string, string> $text
     */
    public array $text = [];

    private array $parameters = [];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function mount(Report $report): void
    {
        $this->parameters = ['%client%' => $report->getClient()->getFirstname()];
        $this->text = $this->makeText();

        $this->list = $this->makeList($report);
        if ($report->getNoAssetToAdd() === false) {
            foreach ($this->getAssetsGroupedByTitle($report) as $title => $assets) {
                if ($title === $this->text['property']) {
                    /**
                     * @var array<AssetProperty> $assets
                     */
                    $this->tables = [...$this->tables, ...$this->makePropertyTables(...$assets)];
                } else {
                    /**
                     * @var array<AssetOther> $assets
                     */
                    $this->tables[] = $this->makeTableOther($title, ...$assets);
                }
            }
            $this->tables[] = $this->makeTotalTable($report);
        }
    }

    private function makeList(Report $report): ListEntries
    {
        $builder = new ListBuilder();
        $builder->addEntry($this->text['hasAssets'], $this->text[match ($report->getNoAssetToAdd()) {
            true => 'no',
            false => 'yes',
            default => 'notEntered'
        }]);
        return $builder->makeList();
    }

    private function makeTableOther(string $title, AssetOther ...$assets): Table
    {
        $builder = new TableBuilder()->addHeader($title, $this->text['valuationDate'], $this->text['value']);
        $total = 0.0;
        foreach ($assets as $asset) {
            $builder->addRow(
                $asset->getDescription() ?? $this->text['notEntered'],
                $asset->getValuationDate() === null ? '' : $asset->getValuationDate()->format('d F Y'),
                $asset->getValue() === null ? '' : $this->formatMoney((float)$asset->getValue()),
            );
            $total += (float)$asset->getValue();
        }
        $builder->addRow(new Cell($this->text['totalAmount'], isHeader: true), '', new Cell($this->formatMoney($total), self::NUMERIC_FORMAT, true));
        return $builder->makeTable();
    }

    /**
     * @return array<Table>
     */
    private function makePropertyTables(AssetProperty ...$assets): array
    {
        $tables = [];
        foreach ($assets as $key => $asset) {
            $index = (int)$key + 1;
            $builder = new TableBuilder()->addHeader(new Cell("{$this->text['property']} {$index}", size: 2), '');

            $builder->addRow($this->text['address1'], $asset->getAddress() ?? 'notEntered');
            if (!empty($asset->getAddress2())) {
                $builder->addRow($this->text['address2'], $asset->getAddress2());
            }
            if (!empty($asset->getCounty())) {
                $builder->addRow($this->text['county'], $asset->getCounty());
            }
            $builder->addRow($this->text['postcode'], $asset->getPostcode() ?? 'notEntered');
            $builder->addRow($this->text['occupants'], $asset->getOccupants() ?? 'notEntered');
            $builder->addRow($this->text['owned'], $this->text[$asset->getOwned() ?? 'fully']);
            if ($asset->getOwned() === 'partly') {
                $builder->addRow($this->text['ownedPercentage'], $this->formatPercent((float)$asset->getOwnedPercentage()));
            }
            $builder->addRow($this->text['hasMortgage'], $this->text[$asset->getHasMortgage() ?? 'notEntered']);
            if ($asset->getHasMortgage() === 'yes') {
                $builder->addRow($this->text['mortgageOutstandingAmount'], $this->formatMoney((float)$asset->getMortgageOutstandingAmount()));
            }
            $builder->addRow($this->text['propertyValue'], $this->formatMoney((float)$asset->getValue()));
            $builder->addRow($this->text['isSubjectToEquityRelease'], $this->text[$asset->getIsSubjectToEquityRelease() ?? 'notEntered']);
            $builder->addRow($this->text['hasCharges'], $this->text[$asset->getHasCharges() ?? 'notEntered']);
            $builder->addRow($this->text['isRentedOut'], $this->text[$asset->getIsRentedOut() ?? 'notEntered']);
            if ($asset->getIsRentedOut() === 'yes') {
                $builder->addRow($this->text['rentAgreementEndDate'], $asset->getRentAgreementEndDate() === null ? $this->text['notEntered'] : $asset->getRentAgreementEndDate()->format('F Y'));
                $builder->addRow($this->text['rentIncomeMonth'], $this->formatMoney((float)$asset->getRentIncomeMonth()));
            }
            $tables[] = $builder->makeTable();
        }

        return $tables;
    }

    private function makeTotalTable(Report $report): Table
    {
        return new TableBuilder(true, true)
            ->addRow(new Cell($this->text['totalValue'], size: 2), new Cell($this->formatMoney((float)$report->getAssetsTotalValue()), self::NUMERIC_FORMAT, true))
            ->makeTable();
    }

    /**
     * @return array<string, array<Asset>>
     */
    private function getAssetsGroupedByTitle(Report $report): array
    {
        $groups = [];
        foreach ($report->getAssets() as $asset) {
            if ($asset instanceof AssetOther) {
                $title = $asset->getTitle() ?? '';
                if (in_array($title, ['Artwork', 'Antiques', 'Jewellery'])) {
                    $title = $this->text['artworkAntiquesJewellery'];
                }
                $groups[$title] ??= [];
                $groups[$title][] = $asset;
            } else {
                $groups[$this->text['property']][] = $asset;
            }
        }

        ksort($groups);
        foreach ($groups as $group) {
            usort($group, fn (Asset $left, Asset $right): int => $left->getId() <=> $right->getId());
        }

        return $groups;
    }

    private function formatMoney(float $value): string
    {
        return '£ ' . number_format($value, 2);
    }

    private function formatPercent(float $value): string
    {
        return number_format($value, 2) . ' %';
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('startPage.pageTitle'),
            'hasAssets' => $this->translate('existPage.form.noAssetToAdd.label'),
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'notEntered' => $this->translate('review.notEntered'),
            'tableHeader' => $this->translate('summaryPage.listOfAssets'),
            'artworkAntiquesJewellery' => $this->translate('review.artworkAntiquesJewellery'),
            'property' => $this->translate('form.title.choices.property'),
            'description' => $this->translate('review.description'),
            'value' => $this->translate('form.value.label'),
            'valuationDate' => $this->translate('form.valuationDate.legend'),
            'totalAmount' => $this->translate('review.totalAmount'),
            'totalValue' => $this->translate('summaryPage.totalValueOfAssets'),
            'address1' => $this->translate('form.property.address.label'),
            'address2' => $this->translate('form.property.address2.label'),
            'county' => $this->translate('form.property.county.label'),
            'postcode' => $this->translate('form.property.postcode.label'),
            'occupants' => $this->translate('form.property.occupants.label'),
            'owned' => $this->translate('form.property.owned.label'),
            'ownedPercentage' => $this->translate('form.property.ownedPercentage.label'),
            'isSubjectToEquityRelease' => $this->translate('form.property.isSubjectToEquityRelease.label'),
            'propertyValue' => $this->translate('form.property.value.label'),
            'hasMortgage' => $this->translate('form.property.hasMortgage.label'),
            'mortgageOutstandingAmount' => $this->translate('form.property.mortgageOutstandingAmount.label'),
            'hasCharges' => $this->translate('form.property.hasCharges.label'),
            'isRentedOut' => $this->translate('form.property.isRentedOut.label'),
            'rentAgreementEndDate' => $this->translate('form.property.rentAgreementEndDate.label'),
            'rentIncomeMonth' => $this->translate('form.property.rentIncomeMonth.label'),
            'fully' => $this->translate('review.fully'),
            'partly' => $this->translate('review.partly'),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-assets');
        } catch (\Throwable $t) {
            return "$t";
        }
    }
}
