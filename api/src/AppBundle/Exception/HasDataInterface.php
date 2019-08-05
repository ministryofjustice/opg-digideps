<?php

namespace AppBundle\Exception;

interface HasDataInterface
{
    public function getData();

    public function setData($data);
}
