<?php

namespace App\Entity;

use App\Repository\StallRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: StallRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Stall
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $awningColor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $claimed = false;

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $labels = [];

    #[ORM\Column(type: 'json', options: ['default' => '{}'])]
    private array $socialLinks = [];

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, Family> */
    #[ORM\ManyToMany(targetEntity: Family::class, inversedBy: 'stalls')]
    #[ORM\JoinTable(name: 'stall_family')]
    private Collection $families;

    /** @var Collection<int, Specialty> */
    #[ORM\ManyToMany(targetEntity: Specialty::class, inversedBy: 'stalls')]
    #[ORM\JoinTable(name: 'stall_specialty')]
    private Collection $specialties;

    /** @var Collection<int, StallMember> */
    #[ORM\OneToMany(targetEntity: StallMember::class, mappedBy: 'stall', cascade: ['persist'], orphanRemoval: true)]
    private Collection $members;

    /** @var Collection<int, StallAttendance> */
    #[ORM\OneToMany(targetEntity: StallAttendance::class, mappedBy: 'stall', cascade: ['persist'], orphanRemoval: true)]
    private Collection $attendances;

    /** @var Collection<int, Absence> */
    #[ORM\OneToMany(targetEntity: Absence::class, mappedBy: 'stall', cascade: ['persist'], orphanRemoval: true)]
    private Collection $absences;

    /** @var Collection<int, Product> */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'stall', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $products;

    /** @var Collection<int, Announcement> */
    #[ORM\OneToMany(targetEntity: Announcement::class, mappedBy: 'stall', cascade: ['persist'], orphanRemoval: true)]
    private Collection $announcements;

    /** @var Collection<int, Follow> */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'stall')]
    private Collection $followers;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->families = new ArrayCollection();
        $this->specialties = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->attendances = new ArrayCollection();
        $this->absences = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->announcements = new ArrayCollection();
        $this->followers = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAwningColor(): ?string
    {
        return $this->awningColor;
    }

    public function setAwningColor(?string $awningColor): static
    {
        $this->awningColor = $awningColor;

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

    public function isClaimed(): bool
    {
        return $this->claimed;
    }

    public function setClaimed(bool $claimed): static
    {
        $this->claimed = $claimed;

        return $this;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function setLabels(array $labels): static
    {
        $this->labels = $labels;

        return $this;
    }

    public function getSocialLinks(): array
    {
        return $this->socialLinks;
    }

    public function setSocialLinks(array $socialLinks): static
    {
        $this->socialLinks = $socialLinks;

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

    /** @return Collection<int, Family> */
    public function getFamilies(): Collection
    {
        return $this->families;
    }

    public function addFamily(Family $family): static
    {
        if (!$this->families->contains($family)) {
            $this->families->add($family);
        }

        return $this;
    }

    public function removeFamily(Family $family): static
    {
        $this->families->removeElement($family);

        return $this;
    }

    /** @return Collection<int, Specialty> */
    public function getSpecialties(): Collection
    {
        return $this->specialties;
    }

    public function addSpecialty(Specialty $specialty): static
    {
        if (!$this->specialties->contains($specialty)) {
            $this->specialties->add($specialty);
        }

        return $this;
    }

    public function removeSpecialty(Specialty $specialty): static
    {
        $this->specialties->removeElement($specialty);

        return $this;
    }

    /** @return Collection<int, StallMember> */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    /** @return Collection<int, StallAttendance> */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    /** @return Collection<int, Absence> */
    public function getAbsences(): Collection
    {
        return $this->absences;
    }

    /** @return Collection<int, Product> */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /** @return Collection<int, Announcement> */
    public function getAnnouncements(): Collection
    {
        return $this->announcements;
    }

    /** @return Collection<int, Follow> */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
