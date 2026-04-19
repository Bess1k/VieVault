<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

// Utilisateur de l'application VieVault
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')] 
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cet email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Identifiant unique de l'utilisateur
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'usr_id')]
    private ?int $id = null;

    // Adresse email, sert aussi d'identifiant pour se connecter
    #[ORM\Column(name: 'usr_email', length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    // Rôles : ROLE_USER par défaut, ou ROLE_ADMIN / ROLE_NOTAIRE
    #[ORM\Column(name: 'usr_roles')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    // Mot de passe hashé 
    #[ORM\Column(name: 'usr_password_hash')]
    private ?string $password = null;

    // Nom de famille
    #[ORM\Column(name: 'usr_lastname', length: 100)]
    private ?string $lastname = null;

    // Prénom
    #[ORM\Column(name: 'usr_firstname', length: 100)]
    private ?string $firstname = null;

    // Date de naissance (utilisée pour la vérification chez le notaire)
    #[ORM\Column(name: 'usr_birth_date', type: Types::DATE_MUTABLE)]
    private ?\DateTime $birthDate = null;

    // Lieu de naissance
    #[ORM\Column(name: 'usr_birth_place', length: 255)]
    private ?string $birthPlace = null;

    // Mot de passe panique hashé, déclenche le mode leurre s'il est utilisé
    #[ORM\Column(name: 'usr_panic_password_hash', length: 255, nullable: true)]
    private ?string $panicPasswordHash = null;

    // Date de la dernière connexion (utilisé pour le Dead Man's Switch)
    #[ORM\Column(name: 'usr_last_login_at', nullable: true)]
    private ?\DateTime $lastLoginAt = null;

    // Vrai si l'utilisateur a activé le mode vacances
    #[ORM\Column(name: 'usr_is_paused')]
    private ?bool $isPaused = null;

    // Date jusqu'à laquelle le mode vacances est actif
    #[ORM\Column(name: 'usr_pause_until', nullable: true)]
    private ?\DateTime $pauseUntil = null;

    // Statut du compte : ACTIVE, INACTIVE, DECEASED
    #[ORM\Column(name: 'usr_status', length: 20)]
    private ?string $status = null;

    /**
     * @var Collection<int, VaultElement>
     */
    // Liste des éléments dans le coffre de l'utilisateur
    #[ORM\OneToMany(targetEntity: VaultElement::class, mappedBy: 'createdBy', orphanRemoval: true)]
    private Collection $vaultElements;

    /**
     * @var Collection<int, Beneficiary>
     */
    // Liste des bénéficiaires désignés par l'utilisateur
    #[ORM\OneToMany(targetEntity: Beneficiary::class, mappedBy: 'createdBy', orphanRemoval: true)]
    private Collection $beneficiaries;

    /**
     * @var Collection<int, AuditLog>
     */
    // Historique des actions effectuées par l'utilisateur
    #[ORM\OneToMany(targetEntity: AuditLog::class, mappedBy: 'createdBy')]
    private Collection $auditLogs;

    // Email de contact d'urgence (reçoit une alerte en cas de mode panique)
    #[ORM\Column(name: 'usr_emergency_email', length: 180, nullable: true)]
    private ?string $emergencyEmail = null;

    // Vrai si l'email de l'utilisateur a été vérifié après inscription
    #[ORM\Column(name: 'usr_is_verified')]
    private ?bool $isVerified = null;

    public function __construct()
    {
        $this->vaultElements = new ArrayCollection();
        $this->beneficiaries = new ArrayCollection();
        $this->auditLogs = new ArrayCollection();
    }

    public function getId(): ?int
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
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

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTime $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(string $birthPlace): static
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    public function getPanicPasswordHash(): ?string
    {
        return $this->panicPasswordHash;
    }

    public function setPanicPasswordHash(?string $panicPasswordHash): static
    {
        $this->panicPasswordHash = $panicPasswordHash;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTime
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTime $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function isPaused(): ?bool
    {
        return $this->isPaused;
    }

    public function setIsPaused(bool $isPaused): static
    {
        $this->isPaused = $isPaused;

        return $this;
    }

    public function getPauseUntil(): ?\DateTime
    {
        return $this->pauseUntil;
    }

    public function setPauseUntil(?\DateTime $pauseUntil): static
    {
        $this->pauseUntil = $pauseUntil;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
            $vaultElement->setCreatedBy($this);
        }

        return $this;
    }

    public function removeVaultElement(VaultElement $vaultElement): static
    {
        if ($this->vaultElements->removeElement($vaultElement)) {
            if ($vaultElement->getCreatedBy() === $this) {
                $vaultElement->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Beneficiary>
     */
    public function getBeneficiaries(): Collection
    {
        return $this->beneficiaries;
    }

    public function addBeneficiary(Beneficiary $beneficiary): static
    {
        if (!$this->beneficiaries->contains($beneficiary)) {
            $this->beneficiaries->add($beneficiary);
            $beneficiary->setCreatedBy($this);
        }

        return $this;
    }

    public function removeBeneficiary(Beneficiary $beneficiary): static
    {
        if ($this->beneficiaries->removeElement($beneficiary)) {
            // set the owning side to null (unless already changed)
            if ($beneficiary->getCreatedBy() === $this) {
                $beneficiary->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AuditLog>
     */
    public function getAuditLogs(): Collection
    {
        return $this->auditLogs;
    }

    public function addAuditLog(AuditLog $auditLog): static
    {
        if (!$this->auditLogs->contains($auditLog)) {
            $this->auditLogs->add($auditLog);
            $auditLog->setCreatedBy($this);
        }

        return $this;
    }

    public function removeAuditLog(AuditLog $auditLog): static
    {
        if ($this->auditLogs->removeElement($auditLog)) {
            // set the owning side to null (unless already changed)
            if ($auditLog->getCreatedBy() === $this) {
                $auditLog->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function getEmergencyEmail(): ?string
    {
        return $this->emergencyEmail;
    }

    public function setEmergencyEmail(?string $emergencyEmail): static
    {
        $this->emergencyEmail = $emergencyEmail;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}