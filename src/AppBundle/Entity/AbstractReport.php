<?php

namespace AppBundle\Entity;


abstract class AbstractReport
{
    /**
     * @return Client
     */
    abstract public function getClient();

}
