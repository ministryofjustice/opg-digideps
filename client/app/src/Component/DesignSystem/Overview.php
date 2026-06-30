<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\DesignSystem;

use OPG\Digideps\Frontend\Component\GovUk\List\DefinitionList;
use OPG\Digideps\Frontend\Component\GovUk\List\ListBuilder;
use OPG\Digideps\Frontend\Component\GovUk\Table\Cell;
use OPG\Digideps\Frontend\Component\GovUk\Table\Table;
use OPG\Digideps\Frontend\Component\GovUk\Table\TableBuilder;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Overview
{
    public readonly string $hello;

    public ?string $fox = null;
    public ?string $over = null;
    public ?DefinitionList $list = null;
    public ?Table $table1 = null;
    public ?Table $table2 = null;
    public ?Table $table3 = null;
    public ?Table $tableWithCaption;

    public string $lore {get => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";}

    public function __construct()
    {
        $this->hello = 'Hello World!';
    }

    public function mount(): void
    {
        $this->list = new ListBuilder()
            ->addItem("Alpha", $this->hello)
            ->addItem("Beta", "Gamma")
            ->addItem("Delta", $this->hello)
            ->makeList();
        $this->table1 = new TableBuilder()->addColumns(1, 1, 1)
            ->addHeader('AxA', 'BxB', 'CxC')
            ->addRow('A1', 'B1', 'C1')
            ->addRow('A2', 'B2', 'C1')
            ->addRow('A3', 'B3', 'C3')
            ->makeTable();
        $this->table2 = new TableBuilder(true)->addColumns(1, 1, 1)
            ->addRow('A1', 'B1', 'C1')
            ->addRow('A2', 'B2', 'C1')
            ->addRow('A3', 'B3', 'C3')
            ->makeTable();
        $this->table3 = new TableBuilder()->addColumns(1, 1, 1)
            ->addHeader('AxA', 'BxB', 'CxC')
            ->addRow(new Cell('A1', isBold: true), 'B1', new Cell('C1', isHeader: true))
            ->addRow('A2', new Cell($this->lore, colspan: 2, rowspan: 2))
            ->addRow('A3')
            ->makeTable();
        $this->tableWithCaption = new TableBuilder(caption: "Table caption")
            ->addHeader('AxA', 'BxB', 'CxC')
            ->addRow('A1', 'B1', 'C1')
            ->addRow('A2', 'B2', 'C1')
            ->addRow('A3', 'B3', 'C3')
            ->makeTable();
    }

    public string $foxOver {get => "{$this->fox} {$this->over}";}
}
