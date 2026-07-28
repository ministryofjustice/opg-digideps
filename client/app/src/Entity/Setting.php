<?php

namespace OPG\Digideps\Frontend\Entity;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Setting
{
    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $id;

    /**
     * @var string
     */
    #[JMS\Groups(['setting'])]
    #[Assert\NotBlank(message: 'adminSetting.content.notBlank', groups: ['setting'])]
    #[JMS\Type('string')]
    private $content;


    /**
     * @var bool
     */
    #[JMS\Groups(['setting'])]
    #[Assert\NotNull(message: 'adminSetting.enabled.notBlank', groups: ['setting'])]
    #[JMS\Type('boolean')]
    private $enabled;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id): static
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
     */
    public function setContent($content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
