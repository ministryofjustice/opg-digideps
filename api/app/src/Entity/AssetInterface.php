<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

interface AssetInterface
{
    public function getType();

    public function getValue();

    /**
     * @return bool
     */
    public function isEqual(AssetInterface $asset);
}
