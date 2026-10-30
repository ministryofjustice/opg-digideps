<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\UserResearch;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Uid\Uuid;

class ElectronicDeviceType
{
    #[JMS\Type('OPG\Digideps\Frontend\Entity\UserResearch\UserResearchResponse')]
    private UserResearchResponse $userResearchResponse;

    #[JMS\Type('string')]
    private Uuid $id;

    #[JMS\Type('bool')]
    private ?bool $desktop = null;

    #[JMS\Type('bool')]
    private ?bool $laptop = null;

    #[JMS\Type('bool')]
    private ?bool $mobile = null;

    #[JMS\Type('bool')]
    private ?bool $tablet = null;

    public function getUserResearchResponse(): UserResearchResponse
    {
        return $this->userResearchResponse;
    }

    public function setUserResearchResponse(UserResearchResponse $userResearchResponse): ElectronicDeviceType
    {
        $this->userResearchResponse = $userResearchResponse;

        return $this;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDesktop(): ?bool
    {
        return $this->desktop;
    }

    public function setDesktop(?bool $desktop): static
    {
        $this->desktop = $desktop;

        return $this;
    }

    public function getLaptop(): ?bool
    {
        return $this->laptop;
    }

    public function setLaptop(?bool $laptop): static
    {
        $this->laptop = $laptop;

        return $this;
    }

    public function getMobile(): ?bool
    {
        return $this->mobile;
    }

    public function setMobile(?bool $mobile): static
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getTablet(): ?bool
    {
        return $this->tablet;
    }

    public function setTablet(?bool $tablet): static
    {
        $this->tablet = $tablet;

        return $this;
    }

    public function getCommaSeparatedTypesAgreed(): string
    {
        $props = get_object_vars($this);

        $types = [];

        foreach ($props as $propName => $value) {
            if ($value) {
                $types[] = $propName;
            }
        }

        return implode(',', $types);
    }
}
