<?php

namespace App\Entity;

use App\Repository\MarketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MarketRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Market
{
    public const SOURCE_OSM = 'osm';
    public const SOURCE_DATATOURISME = 'datatourisme';
    public const SOURCE_DATAGOUV = 'datagouv';
    public const SOURCE_MANUAL = 'manual';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(length: 220, unique: true)]
    private ?string $slug = null;

    #[ORM\ManyToOne(targetEntity: Municipality::class, inversedBy: 'markets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Municipality $municipality = null;

    #[ORM\Column(type: 'float')]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float')]
    private ?float $longitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isCovered = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $parkingInfo = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sourceId = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $verified = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, MarketSchedule> */
    #[ORM\OneToMany(targetEntity: MarketSchedule::class, mappedBy: 'market', cascade: ['persist'], orphanRemoval: true)]
    private Collection $schedules;

    /** @var Collection<int, Follow> */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'market')]
    private Collection $followers;

    /** @var Collection<int, MarketReport> */
    #[ORM\OneToMany(targetEntity: MarketReport::class, mappedBy: 'market')]
    private Collection $reports;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->schedules = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getMunicipality(): ?Municipality
    {
        return $this->municipality;
    }

    public function setMunicipality(?Municipality $municipality): static
    {
        $this->municipality = $municipality;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function isCovered(): bool
    {
        return $this->isCovered;
    }

    public function setIsCovered(bool $isCovered): static
    {
        $this->isCovered = $isCovered;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getParkingInfo(): ?string
    {
        return $this->parkingInfo;
    }

    public function setParkingInfo(?string $parkingInfo): static
    {
        $this->parkingInfo = $parkingInfo;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getSourceId(): ?string
    {
        return $this->sourceId;
    }

    public function setSourceId(?string $sourceId): static
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /** @return Collection<int, MarketSchedule> */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(MarketSchedule $schedule): static
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->setMarket($this);
        }

        return $this;
    }

    /** @return Collection<int, Follow> */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    /** @return Collection<int, MarketReport> */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
