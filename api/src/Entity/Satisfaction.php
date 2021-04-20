<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\UserResearch\ResearchType;
use App\Entity\UserResearch\UserResearchResponse;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use DateTime;

/**
 * User satisfaction scores
 *
 * @ORM\Table(name="satisfaction")
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="App\Repository\SatisfactionRepository")
 */
class Satisfaction
{
    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="satisfaction_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(type="string", name="comments", length=1200, nullable=true)
     */
    private $comments;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(type="string", name="deputy_role", length=50, nullable=true)
     */
    private $deputyrole;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(type="string", name="report_type", length=9, nullable=true)
     */
    private $reporttype;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private DateTime $created;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserResearch\UserResearchResponse", inversedBy="userResearchResponse", cascade={"persist", "remove"})
     *
     * @JMS\Type("App\Entity\UserResearchResponse")
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private UserResearchResponse $userResearchResponse;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Satisfaction
     */
    public function setId(int $id): Satisfaction
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @param int $score
     * @return Satisfaction
     */
    public function setScore(int $score): Satisfaction
    {
        $this->score = $score;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param string|null $comments
     * @return Satisfaction
     */
    public function setComments(?string $comments): Satisfaction
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyrole(): ?string
    {
        return $this->deputyrole;
    }

    /**
     * @param string $deputyrole
     * @return Satisfaction
     */
    public function setDeputyrole(string $deputyrole): Satisfaction
    {
        $this->deputyrole = $deputyrole;
        return $this;
    }

    /**
     * @return string
     */
    public function getReporttype(): ?string
    {
        return $this->reporttype;
    }

    /**
     * @param string $reporttype
     * @return Satisfaction
     */
    public function setReporttype(string $reporttype): Satisfaction
    {
        $this->reporttype = $reporttype;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     * @return Satisfaction
     */
    public function setCreated(DateTime $created): Satisfaction
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return UserResearchResponse
     */
    public function getUserResearchResponse(): UserResearchResponse
    {
        return $this->userResearchResponse;
    }

    /**
     * @param UserResearchResponse $userResearchResponse
     * @return Satisfaction
     */
    public function setUserResearchResponse(UserResearchResponse $userResearchResponse): Satisfaction
    {
        $this->userResearchResponse = $userResearchResponse;
        return $this;
    }
}
