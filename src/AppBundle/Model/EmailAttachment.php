<?php
namespace AppBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;
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

}