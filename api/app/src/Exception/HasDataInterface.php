<?php

namespace OPG\Digideps\Backend\Exception;

interface HasDataInterface
{
    public function getData(): mixed;

    public function setData(mixed $data): void;
}
