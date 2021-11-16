<?php

namespace App\Model;

class EmailAttachment
{
    public function __construct(private $filename, private $contentType, private $content)
    {
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
