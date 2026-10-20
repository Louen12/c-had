<?php

namespace App\Entity;

use App\Repository\AnnouncementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AnnouncementRepository::class)]
class Announcement
{
    public const TYPE_ABSENCE = 'absence';
    public const TYPE_PROMOTION = 'promotion';
    public const TYPE_NEW_PRODUCT = 'new_product';
    public const TYPE_INFO = 'info';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Stall::class, inversedBy: 'announcements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Stall $stall = null;

    #[ORM\Column(length: 30)]
    private string $type = self::TYPE_INFO;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getStall(): ?Stall
    {
        return $this->stall;
    }

    public function setStall(?Stall $stall): static
    {
        $this->stall = $stall;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isActive(): bool
    {
        return $this->endsAt === null || $this->endsAt > new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return mb_substr($this->content ?? '', 0, 50);
    }
}
