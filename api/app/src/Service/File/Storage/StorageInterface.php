<?php

namespace App\Service\File\Storage;

interface StorageInterface
{
    public function retrieve(string $key);

    public function delete($key);

    public function store($key, $body);
}
