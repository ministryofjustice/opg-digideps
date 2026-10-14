<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\UserResearch;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Uid\Uuid;

#[ORM\Table(name: 'research_type')]
#[ORM\Entity]
class ResearchType
{
    public function __construct(array $formResponses, ?Uuid $id = null)
    {
        if (empty($formResponses)) {
            throw new \RuntimeException('Must select at least one research type', 403);
        }

        $this->id = $id ?? Uuid::v4();

        $setters = array_map(function ($response): string {
            return sprintf('set%s', ucfirst($response));
        }, $formResponses);

        foreach ($setters as $setter) {
            $this->$setter(true);
        }
    }

    #[JMS\Type('OPG\Digideps\Backend\Entity\UserResearchResponse')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\OneToOne(targetEntity: UserResearchResponse::class, mappedBy: 'researchType', cascade: ['persist'])]
    private UserResearchResponse $userResearchResponse;

    #[JMS\Type('string')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\CustomIdGenerator(class: Uuid::class)]
    private Uuid $id;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\Column(name: 'surveys', type: 'boolean', nullable: true)]
    private ?bool $surveys = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\Column(name: 'video_call', type: 'boolean', nullable: true)]
    private ?bool $videoCall = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\Column(name: 'phone', type: 'boolean', nullable: true)]
    private ?bool $phone = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\Column(name: 'in_person', type: 'boolean', nullable: true)]
    private ?bool $inPerson = null;

    public function getUserResearchResponse(): UserResearchResponse
    {
        return $this->userResearchResponse;
    }

    public function setUserResearchResponse(UserResearchResponse $userResearchResponse): ResearchType
    {
        $this->userResearchResponse = $userResearchResponse;

        return $this;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getSurveys(): ?bool
    {
        return $this->surveys;
    }

    public function setSurveys(?bool $surveys): ResearchType
    {
        $this->surveys = $surveys;

        return $this;
    }

    public function getVideoCall(): ?bool
    {
        return $this->videoCall;
    }

    public function setVideoCall(?bool $videoCall): ResearchType
    {
        $this->videoCall = $videoCall;

        return $this;
    }

    public function getPhone(): ?bool
    {
        return $this->phone;
    }

    public function setPhone(?bool $phone): ResearchType
    {
        $this->phone = $phone;

        return $this;
    }

    public function getInPerson(): ?bool
    {
        return $this->inPerson;
    }

    public function setInPerson(?bool $inPerson): ResearchType
    {
        $this->inPerson = $inPerson;

        return $this;
    }
}
