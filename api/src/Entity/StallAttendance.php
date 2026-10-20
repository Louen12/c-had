<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['stall_id', 'market_schedule_id'])]
class StallAttendance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Stall::class, inversedBy: 'attendances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Stall $stall = null;

    #[ORM\ManyToOne(targetEntity: MarketSchedule::class, inversedBy: 'attendances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketSchedule $marketSchedule = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
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

    public function getMarketSchedule(): ?MarketSchedule
    {
        return $this->marketSchedule;
    }

    public function setMarketSchedule(?MarketSchedule $marketSchedule): static
    {
        $this->marketSchedule = $marketSchedule;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
