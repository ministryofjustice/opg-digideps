<?php

namespace AppBundle\Model;

class EmailAttachment
{
    private $filename;

    private $contentType;

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
