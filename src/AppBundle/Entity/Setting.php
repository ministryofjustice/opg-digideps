<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="setting")
 */
class Setting
{
    /**
     * @var int
     * @JMS\Type("string")
     * @JMS\Groups({"setting"})
     *
     * @ORM\Column(name="id", type="string", length=64, nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"setting"})
     *
     * @ORM\Column(name="content", type="string", length=100, nullable=false)
     */
    private $content;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"setting"})
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * Setting constructor.
     * @param int $id
     * @param string $content
     * @param bool $enabled
     */
    public function __construct($id, $content, $enabled)
    {
        $this->id = $id;
        $this->content = $content;
        $this->enabled = $enabled;
    }

    /**
     * @return int
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
     * @param int $id
     * @return Setting
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $content
     * @return Setting
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param bool $enabled
     * @return Setting
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }




}
