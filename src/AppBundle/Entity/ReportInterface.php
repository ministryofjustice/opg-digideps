<?php

namespace AppBundle\Entity;

/**
 * Common functionalities among Report and NDR
 */
interface ReportInterface
{
    public function getId();

    public function getType();

    public function getClient();

    public function createAttachmentName($format);

    public function getZipName();
}
