<?php

namespace App\Entity;

use App\Repository\BeneficiaryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

// Entité représentant un bénéficiaire désigné par un utilisateur
// pour recevoir l'héritage numérique après son décès
#[ORM\Entity(repositoryClass: BeneficiaryRepository::class)]
#[ORM\Table(name: 'beneficiaries')] 
class Beneficiary
{
    // Clé primaire 
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'bnf_id')]
    private ?int $id = null;

    // Nom de famille du bénéficiaire
    #[ORM\Column(name: 'bnf_lastname', length: 255)]
    private ?string $lastname = null;

    // Prénom du bénéficiaire
    #[ORM\Column(name: 'bnf_firstname', length: 255)]
    private ?string $firstname = null;

    // Adresse email utilisée pour notifier le bénéficiaire lors du protocole d'héritage
    #[ORM\Column(name: 'bnf_email', length: 180)]
    private ?string $email = null;

    // Date de naissance — utilisée pour la vérification d'identité par le notaire
    #[ORM\Column(name: 'bnf_birth_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $birthDate = null;

    // Lieu de naissance — utilisé pour la vérification d'identité
    #[ORM\Column(name: 'bnf_birth_place', length: 255, nullable: true)]
    private ?string $birthPlace = null;

    // Token unique généré après approbation du notaire pour accéder aux données léguées
    #[ORM\Column(name: 'bnf_access_token', length: 255, nullable: true)]
    private ?string $accessToken = null;

    // Date d'expiration du token d'accès (30 jours après génération)
    #[ORM\Column(name: 'bnf_token_expires_at', nullable: true)]
    private ?\DateTime $tokenExpiresAt = null;

    // Chemin du justificatif de décès téléversé par le bénéficiaire
    #[ORM\Column(name: 'bnf_submitted_doc_path', length: 500, nullable: true)]
    private ?string $submittedDocPath = null;

    // Statut de validation : EN_ATTENTE / APPROUVE / REFUSE
    #[ORM\Column(name: 'bnf_validation_status', length: 20)]
    private ?string $validationStatus = null;

    // Relation vers l'utilisateur qui a désigné ce bénéficiaire
    #[ORM\ManyToOne(inversedBy: 'beneficiaries')]
    #[ORM\JoinColumn(name: 'bnf_user_id', referencedColumnName: 'usr_id', nullable: false)]
    private ?User $createdBy = null;

    // Liste des éléments du coffre légués à ce bénéficiaire
    /**
     * @var Collection<int, VaultElement>
     */
    #[ORM\OneToMany(targetEntity: VaultElement::class, mappedBy: 'beneficiary')]
    private Collection $vaultElements;

    // Chemin de la pièce d'identité téléversée (complément au justificatif de décès)
    #[ORM\Column(name: 'bnf_id_doc_path', length: 500, nullable: true)]
    private ?string $idDocPath = null;

    public function __construct()
    {
        $this->vaultElements = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, VaultElement>
     */
    public function getVaultElements(): Collection
    {
        return $this->vaultElements;
    }

    public function addVaultElement(VaultElement $vaultElement): static
    {
        if (!$this->vaultElements->contains($vaultElement)) {
            $this->vaultElements->add($vaultElement);
            $vaultElement->setBeneficiary($this);
        }

        return $this;
    }

    public function removeVaultElement(VaultElement $vaultElement): static
    {
        if ($this->vaultElements->removeElement($vaultElement)) {
            // Mettre le côté propriétaire à null (si pas déjà fait)
            if ($vaultElement->getBeneficiary() === $this) {
                $vaultElement->setBeneficiary(null);
            }
        }

        return $this;
    }

    public function getIdDocPath(): ?string
    {
        return $this->idDocPath;
    }

    public function setIdDocPath(?string $idDocPath): static
    {
        $this->idDocPath = $idDocPath;

        return $this;
    }
}