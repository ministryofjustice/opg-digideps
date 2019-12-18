<?php

namespace AppBundle\Entity;

interface DeputyInterface
{
    public function getFullName();
    public function getAddressNotEmptyParts();
    public function getPhoneMain();
    public function getPhoneAlternative();
    public function getEmail();
}
