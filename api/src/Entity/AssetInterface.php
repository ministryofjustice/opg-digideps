<?php

namespace AppBundle\Entity;

interface AssetInterface
{
    public function getType();

    public function getValue();

    /**
     * @param AssetInterface $asset
     * @return bool
     */
    public function isEqual(AssetInterface $asset);
}
