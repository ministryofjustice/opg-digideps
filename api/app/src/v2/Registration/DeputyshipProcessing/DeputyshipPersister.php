<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use Doctrine\ORM\EntityManagerInterface;

class DeputyshipPersister
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return iterable<DeputyshipPersisterResult>
     */
    public function persist(DeputyshipBuilderResult $builderResult): iterable
    {
        // TOD actually persist entities in $builderResult
        foreach ($builderResult->getEntities() as $entity) {
            $this->em->persist($entity);
        }

        try {
            $this->em->flush();
            $this->em->clear();
            unset($builderResult);
        } catch (\Exception) {
            $this->em->rollback();
            $this->em->close();
        }

        yield new DeputyshipPersisterResult();
    }
}
