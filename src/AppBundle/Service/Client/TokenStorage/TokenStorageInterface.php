<?php
namespace AppBundle\Service\Client\TokenStorage;

interface TokenStorageInterface
{
    public function get();
    
    public function set($value);
}