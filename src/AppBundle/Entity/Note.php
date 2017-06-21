<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Client;
use AppBundle\Entity\Traits\CreationAudit;
use AppBundle\Entity\Traits\IsSoftDeleteableEntity;
use AppBundle\Entity\Traits\ModifyAudit;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * Notes.
 *
 * @ORM\Table(name="note",
 *     indexes={
 *     @ORM\Index(name="ix_note_client_id", columns={"client_id"}),
 *     @ORM\Index(name="ix_note_created_by", columns={"created_by"}),
 *     @ORM\Index(name="ix_note_last_modified_by", columns={"last_modified_by"})
 *     })
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\NoteRepository")
 *
 */
class Note
{
    use CreationAudit;
    use ModifyAudit;

    /**
     * Keep in sync with API
     *
     * Possible refactor would be moving some entities data into a shared library
     *
     * @JMS\Exclude
     */
    public static $categories = [
        // categoryId | categoryTranslationKey
        'Todo' => 'todo',
        'DWP' => 'dwp',
        'OPG' => 'opg',
        'Welfare' => 'welfare',
        'Bank' => 'bank',
        'Report' => 'report',
        'Other' => 'other'
    ];


    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"audit_log","notes"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="user_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"notes"})
     *
     * @ORM\Column(name="category", type="string", length=100, nullable=false)
     */
    private $category;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"notes"})
     *
     * @ORM\Column(name="title", type="string", length=150, nullable=false)
     */
    private $title;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"notes"})
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    private $content;

    /**
     * @var Client
     *
     * @JMS\Groups({"report-client"})
     *
     * @JMS\Type("AppBundle\Entity\Client")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client", inversedBy="notes")
     */
    private $client;

    /**
     * Constructor.
     */
    public function __construct(Client $client, $category, $title, $content)
    {
        $this->setCategory($category);
        $this->setTitle($title);
        $this->setContent($content);
        $this->setClient($client);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }
}
