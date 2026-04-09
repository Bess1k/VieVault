<?php

namespace App\Entity;

use App\Repository\BeneficiaryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BeneficiaryRepository::class)]
class Beneficiary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $birthDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $birthPlace = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accessToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $tokenExpiresAt = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $submittedDocPath = null;

    #[ORM\Column(length: 20)]
    private ?string $validationStatus = null;

    #[ORM\ManyToOne(inversedBy: 'beneficiaries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
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

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTime $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(?string $birthPlace): static
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getTokenExpiresAt(): ?\DateTime
    {
        return $this->tokenExpiresAt;
    }

    public function setTokenExpiresAt(?\DateTime $tokenExpiresAt): static
    {
        $this->tokenExpiresAt = $tokenExpiresAt;

        return $this;
    }

    public function getSubmittedDocPath(): ?string
    {
        return $this->submittedDocPath;
    }

    public function setSubmittedDocPath(?string $submittedDocPath): static
    {
        $this->submittedDocPath = $submittedDocPath;

        return $this;
    }

    public function getValidationStatus(): ?string
    {
        return $this->validationStatus;
    }

    public function setValidationStatus(string $validationStatus): static
    {
        $this->validationStatus = $validationStatus;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
