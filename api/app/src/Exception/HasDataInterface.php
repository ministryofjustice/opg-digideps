<?php

namespace App\Exception;

interface HasDataInterface
{
    public function getData();

    public function setData($data);
}
