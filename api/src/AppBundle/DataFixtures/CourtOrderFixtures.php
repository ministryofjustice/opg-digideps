<?php

namespace AppBundle\DataFixtures;

use AppBundle\Builder\CourtOrderBuilder;
use AppBundle\v2\Assembler\DoctrineFixtureCourtOrderAssembler;
use Doctrine\Common\Persistence\ObjectManager;

class CourtOrderFixtures extends AbstractDataFixture
{
    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function doLoad(ObjectManager $manager): void
    {
        /** @var CourtOrderBuilder $builder */
        $builder = $this->container->get('AppBundle\Builder\CourtOrderBuilder');

        /** @var DoctrineFixtureCourtOrderAssembler $courtOrderAssembler */
        $courtOrderAssembler = $this->container->get('AppBundle\v2\Assembler\DoctrineFixtureCourtOrderAssembler');

        $rows = array_map('str_getcsv', file(__DIR__ . '/court_orders.csv'));
        $header = array_shift($rows);

        foreach($rows as $row) {
            $data = array_combine($header, $row);
            $dto = $courtOrderAssembler->assembleFromArray($data);

            $builder
                ->createItem($dto)
                ->addCourtOrderDeputies()
                ->addClient()
                ->addReport()
                ->persistItem()
                ->getItem();
        }
    }

    /**
     * @return array
     */
    protected function getEnvironments(): array
    {
        return ['dev'];
    }
}
