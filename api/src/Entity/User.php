<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true, unique: true)]
    private ?string $phone = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $phoneVerified = false;

    #[ORM\Column(type: 'smallint', options: ['default' => 100])]
    private int $reliabilityScore = 100;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $suspendedUntil = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, StallMember> */
    #[ORM\OneToMany(targetEntity: StallMember::class, mappedBy: 'user')]
    private Collection $stallMemberships;

    /** @var Collection<int, Follow> */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $follows;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->stallMemberships = new ArrayCollection();
        $this->follows = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function isPhoneVerified(): bool
    {
        return $this->phoneVerified;
    }

    public function setPhoneVerified(bool $phoneVerified): static
    {
        $this->phoneVerified = $phoneVerified;

        return $this;
    }

    public function getReliabilityScore(): int
    {
        return $this->reliabilityScore;
    }

    public function setReliabilityScore(int $reliabilityScore): static
    {
        $this->reliabilityScore = $reliabilityScore;

        return $this;
    }

    public function getSuspendedUntil(): ?\DateTimeImmutable
    {
        return $this->suspendedUntil;
    }

    public function setSuspendedUntil(?\DateTimeImmutable $suspendedUntil): static
    {
        $this->suspendedUntil = $suspendedUntil;

        return $this;
    }

    public function isSuspended(): bool
    {
        return $this->suspendedUntil !== null && $this->suspendedUntil > new \DateTimeImmutable();
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

    /** @return Collection<int, StallMember> */
    public function getStallMemberships(): Collection
    {
        return $this->stallMemberships;
    }

    /** @return Collection<int, Follow> */
    public function getFollows(): Collection
    {
        return $this->follows;
    }

    public function __toString(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}
