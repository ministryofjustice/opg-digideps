<?php

namespace AppBundle\Service\File\Storage;

interface StorageInterface
{
    public function retrieve($key);

    public function delete($key);

    public function store($key, $body);
}