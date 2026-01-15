<?php

declare(strict_types=1);

namespace App\v2\Service;

/**
 * Service for applying data fixes after all the ingests are complete.
 * This is to cope with deficiencies in the data model and in other ingests (e.g. figuring out report types),
 * and to deal with work-arounds applied by caseworkers which break data integrity (e.g. deleting and recreating clients).
 */
class DataFixer
{
    public function fix(): DataFixerResult
    {
        return new DataFixerResult();
    }
}
