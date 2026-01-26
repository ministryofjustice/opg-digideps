<?php

namespace App\Factory;

/**
 * Interface for components which perform ad hoc data manipulation tasks, typically during ingests.
 *
 * This is to cope with deficiencies in the data model and in ingests (e.g. figuring out report types),
 * and to deal with work-arounds applied by caseworkers which break data integrity (e.g. deleting and recreating clients).
 */
interface DataFactoryInterface
{
    // identifier for the data factory
    public function getName(): string;

    // run some form of data addition/fix/deletion etc. against the database
    public function run(): DataFactoryResult;
}
