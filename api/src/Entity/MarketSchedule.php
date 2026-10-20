<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MarketSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Market::class, inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Market $market = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $dayOfWeek = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $seasonStart = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $seasonEnd = null;

    /** @var Collection<int, StallAttendance> */
    #[ORM\OneToMany(targetEntity: StallAttendance::class, mappedBy: 'marketSchedule', orphanRemoval: true)]
    private Collection $attendances;

    public function __construct()
    {
        $this->attendances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarket(): ?Market
    {
        return $this->market;
    }

    public function setMarket(?Market $market): static
    {
        $this->market = $market;

        return $this;
    }

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(int $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getSeasonStart(): ?\DateTimeInterface
    {
        return $this->seasonStart;
    }

    public function setSeasonStart(?\DateTimeInterface $seasonStart): static
    {
        $this->seasonStart = $seasonStart;

        return $this;
    }

    public function getSeasonEnd(): ?\DateTimeInterface
    {
        return $this->seasonEnd;
    }

    public function setSeasonEnd(?\DateTimeInterface $seasonEnd): static
    {
        $this->seasonEnd = $seasonEnd;

        return $this;
    }

    /** @return Collection<int, StallAttendance> */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function isActiveOn(\DateTimeInterface $date): bool
    {
        if ($this->seasonStart === null && $this->seasonEnd === null) {
            return true;
        }

        $monthDay = (int) $date->format('md');
        $start = $this->seasonStart ? (int) $this->seasonStart->format('md') : 101;
        $end = $this->seasonEnd ? (int) $this->seasonEnd->format('md') : 1231;

        if ($start <= $end) {
            return $monthDay >= $start && $monthDay <= $end;
        }

        return $monthDay >= $start || $monthDay <= $end;
    }
}
