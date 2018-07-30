<?php

namespace AppBundle\Entity;

/**
 * Common functionalities among Report and NDR
 */
interface ReportInterface
{
    public function getId();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return Client
     */
    public function getClient();

    public function createAttachmentName($format);
}
