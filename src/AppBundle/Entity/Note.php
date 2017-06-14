<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Traits\CreationAudit;
use AppBundle\Entity\Traits\ModifyAudit;

/**
 * Note.
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
        'other' => 'other'
    ];

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"audit_log"})
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
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
     * @JMS\Type("AppBundle\Entity\Report")
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
     * @param Report $report
     *
     * @return $this
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
        return $this;
    }
}
