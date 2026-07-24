<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Traits\CreationAudit;
use OPG\Digideps\Backend\Entity\Traits\ModifyAudit;
use OPG\Digideps\Backend\Repository\NoteRepository;

#[ORM\Table(name: 'note')]
#[ORM\Index(columns: ['client_id'], name: 'ix_note_client_id')]
#[ORM\Index(columns: ['created_by'], name: 'ix_note_created_by')]
#[ORM\Index(columns: ['last_modified_by'], name: 'ix_note_last_modified_by')]
#[ORM\Entity(repositoryClass: NoteRepository::class)]
class Note
{
    use CreationAudit;
    use ModifyAudit;

    /**
     * Keep in sync with API.
     *
     * Possible refactor would be moving some entities data into a shared library
     *
     * @var array<string, string> $categories
     */
    #[JMS\Exclude]
    public static array $categories = [
        // categoryId | categoryTranslationKey
        'Todo' => 'todo',
        'DWP' => 'dwp',
        'OPG' => 'opg',
        'Welfare' => 'welfare',
        'Bank' => 'bank',
        'Report' => 'report',
        'Other' => 'other',
    ];

    #[JMS\Type('integer')]
    #[JMS\Groups(['notes'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'user_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['notes'])]
    #[ORM\Column(name: 'category', type: 'string', length: 100, nullable: true)]
    private ?string $category;

    #[JMS\Type('string')]
    #[JMS\Groups(['notes'])]
    #[ORM\Column(name: 'title', type: 'string', length: 150, nullable: false)]
    private string $title;

    #[JMS\Type('string')]
    #[JMS\Groups(['notes'])]
    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private ?string $content;

    #[JMS\Groups(['note-client'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Client')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'notes')]
    private Client $client;

    public function __construct(Client $client, string $category, string $title, string $content)
    {
        $this->setCategory($category);
        $this->setTitle($title);
        $this->setContent($content);
        $this->setClient($client);
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
