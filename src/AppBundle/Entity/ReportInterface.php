<?php

namespace AppBundle\Entity;

/**
 * Common functionalities among Report and NDR
 */
interface ReportInterface
{
    function getId();

    function getType();

    function getClient();

    function createAttachmentName($format);

    function getZipName();
}
