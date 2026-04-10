<?php

namespace OPG\Digideps\Backend\Exception;

interface HasDataInterface
{
    public function getData();

    public function setData($data);
}
