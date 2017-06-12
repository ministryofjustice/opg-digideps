<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * Notes.
 *
 * @ORM\Table(name="note",
 *    indexes={
 *        @ORM\Index(name="ix_note_report_id", columns={"report_id"}),
 *        @ORM\Index(name="ix_note_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_note_last_modified_by", columns={"last_modified_by"})
 *    }
 * ))
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\NoteRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 */
class Note
{
    /**
     * Keep in sync with API
     *
     * Possible refactor would be moving some entities data into a shared library
     *
     * @JMS\Exclude
     */
    public static $categories = [
        // categoryId | categoryTranslationKey
        'Misc' => 'misc',
        'To Do' => 'todo',
        'OPG' => 'opg'
    ];

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"audit_log","add_note"})
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"add_note"})
     *
     * @Assert\NotBlank( message="note.category.notBlank",
     *     groups={"add_note", "edit_note"}
     * )
     */
    private $category;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"add_note"})
     *
     * @Assert\NotBlank( message="note.category.notBlank",
     *     groups={"add_note", "edit_note"}
     * )
     */
    private $title;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"add_note"})
     *
     */
    private $content;

    /**
     * @var int
     *
     * @JMS\Groups({"note"})
     * @JMS\Type("AppBundle\Entity\Report")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="notes")
     */
    private $report;

    /**
     * Constructor.
     */
    public function __construct(Report $report, $category = '', $title = '', $content = '')
    {
        $this->setCategory($category);
        $this->setTitle($title);
        $this->setContent($content);
        $this->setReport($report);
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
     * @return int
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param ReportInterface $report
     *
     * @return $this
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
        return $this;
    }
}
