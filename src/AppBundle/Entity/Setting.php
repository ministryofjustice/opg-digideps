<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Report\Report;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Setting
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Groups({"admin_setting"})
     * @Assert\NotBlank( message="adminSetting.content.notBlank", groups={"admin_setting"} )
     *
     * @JMS\Type("string")
     */
    private $content;


    /**
     * @var bool
     *
     * @JMS\Groups({"admin_setting"})
     * @Assert\NotBlank( message="adminSetting.active.notBlank", groups={"admin_setting"} )
     *
     *
     * @JMS\Type("boolean")
     */
    private $active;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Setting
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
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
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return Setting
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }



}
