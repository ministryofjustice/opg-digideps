<?php

namespace AppBundle\Service\Client\TokenStorage;

interface TokenStorageInterface
{
    public function get($id);

    public function set($id, $value);

    public function remove($id);
}
