<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="setting")
 */
class Setting
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=64, nullable=false)
     *
     * @ORM\Id
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['setting'])]
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['setting'])]
    private $content;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['setting'])]
    private $enabled;

    /**
     * Setting constructor.
     *
     * @param string $id
     * @param string $content
     * @param bool   $enabled
     */
    public function __construct($id, $content, $enabled)
    {
        $this->id = $id;
        $this->content = $content;
        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param string $id
     *
     * @return Setting
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return Setting
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param bool $enabled
     *
     * @return Setting
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }
}
