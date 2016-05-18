<?php

namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;

class EmailAttachment
{
    /**
     * @JMS\Type("string")
     */
    private $filename;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("contentType")
     */
    private $contentType;

    /**
     * @JMS\Type("string")
     */
    private $content;

    public function __construct($filename, $contentType, $content)
    {
        $this->filename = $filename;
        $this->contentType = $contentType;
        $this->content = $content;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getContent()
    {
        return $this->content;
    }
}
