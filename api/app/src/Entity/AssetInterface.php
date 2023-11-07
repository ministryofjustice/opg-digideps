<?php

namespace App\Entity;

interface AssetInterface
{
    public function getType();

    public function getValue();

    /**
     * @return bool
     */
    public function isEqual(AssetInterface $asset);
}
