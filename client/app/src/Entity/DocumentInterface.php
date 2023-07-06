<?php

namespace App\Entity;

/**
 * Common functionalities among Documents
 */
interface DocumentInterface
{
    public function getStorageReference();

    public function getId();
}
