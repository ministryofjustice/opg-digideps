<?php

namespace OPG\Digideps\Frontend\Entity\Report;

class UnsubmittedSection
{
    public function __construct(
        public string $id,
        public string $title,
        public bool $present
    ) {
    }
}
