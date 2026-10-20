<?php

namespace App\Entity;

use App\Repository\MunicipalityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MunicipalityRepository::class)]
class Municipality
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 5, unique: true)]
    private ?string $inseeCode = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 3)]
    private ?string $departmentCode = null;

    /** @var Collection<int, MunicipalityPostalCode> */
    #[ORM\OneToMany(targetEntity: MunicipalityPostalCode::class, mappedBy: 'municipality', cascade: ['persist'], orphanRemoval: true)]
    private Collection $postalCodes;

    /** @var Collection<int, Market> */
    #[ORM\OneToMany(targetEntity: Market::class, mappedBy: 'municipality')]
    private Collection $markets;

    public function __construct()
    {
        $this->postalCodes = new ArrayCollection();
        $this->markets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(string $inseeCode): static
    {
        $this->inseeCode = $inseeCode;

        return $this;
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

    public function getDepartmentCode(): ?string
    {
        return $this->departmentCode;
    }

    public function setDepartmentCode(string $departmentCode): static
    {
        $this->departmentCode = $departmentCode;

        return $this;
    }

    /** @return Collection<int, MunicipalityPostalCode> */
    public function getPostalCodes(): Collection
    {
        return $this->postalCodes;
    }

    public function addPostalCode(string $postalCode): static
    {
        foreach ($this->postalCodes as $existing) {
            if ($existing->getPostalCode() === $postalCode) {
                return $this;
            }
        }

        $pc = new MunicipalityPostalCode();
        $pc->setPostalCode($postalCode);
        $pc->setMunicipality($this);
        $this->postalCodes->add($pc);

        return $this;
    }

    /** @return Collection<int, Market> */
    public function getMarkets(): Collection
    {
        return $this->markets;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
