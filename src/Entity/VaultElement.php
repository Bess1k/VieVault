<?php

namespace App\Entity;

use App\Repository\VaultElementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

// Élément stocké dans le coffre-fort (mot de passe, document, code, etc.)
#[ORM\Entity(repositoryClass: VaultElementRepository::class)]
#[ORM\Table(name: 'vault_elements')] //< nom de table au pluriel selon le dictionnaire MERISE
class VaultElement
{
    // Identifiant unique de l'élément
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'elv_id')]
    private ?int $id = null;

    // Titre donné par l'utilisateur (ex : "Mot de passe Gmail")
    #[ORM\Column(name: 'elv_title', length: 255)]
    private ?string $title = null;

    // Type d'élément : DOCUMENT, MOT_DE_PASSE, CODE, CRYPTO...
    #[ORM\Column(name: 'elv_type', length: 20)]
    private ?string $type = null;

    // Contenu de l'élément (texte, mot de passe, note, etc.)
    #[ORM\Column(name: 'elv_content', type: Types::TEXT)]
    private ?string $content = null;

    // Vrai si l'élément doit être transmis aux bénéficiaires en cas de décès
    #[ORM\Column(name: 'elv_is_heritage')]
    private ?bool $isHeritage = null;

    // Date de création de l'élément
    #[ORM\Column(name: 'elv_created_at')]
    private ?\DateTime $createdAt = null;

    // Date de la dernière modification (null si jamais modifié)
    #[ORM\Column(name: 'elv_updated_at', nullable: true)]
    private ?\DateTime $updatedAt = null;

    // Utilisateur propriétaire de l'élément
    #[ORM\ManyToOne(inversedBy: 'vaultElements')]
    #[ORM\JoinColumn(name: 'elv_user_id', referencedColumnName: 'usr_id', nullable: false)]
    private ?User $createdBy = null;

    // Bénéficiaire qui recevra l'élément en cas d'héritage (optionnel)
    #[ORM\ManyToOne(inversedBy: 'vaultElements')]
    #[ORM\JoinColumn(name: 'elv_beneficiary_id', referencedColumnName: 'bnf_id', nullable: true)]
    private ?Beneficiary $beneficiary = null;

    // Ancien champ pour un seul fichier (remplacé par la relation VaultFile)
    #[ORM\Column(name: 'elv_file_path', length: 500, nullable: true)]
    private ?string $filePath = null;

    /**
     * @var Collection<int, VaultFile>
     */
    // Liste des fichiers joints à cet élément (plusieurs possibles)
    #[ORM\OneToMany(targetEntity: VaultFile::class, mappedBy: 'vaultElement', orphanRemoval: true)]
    private Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?string
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

    public function isHeritage(): ?bool
    {
        return $this->isHeritage;
    }

    public function setIsHeritage(bool $isHeritage): static
    {
        $this->isHeritage = $isHeritage;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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

    public function getBeneficiary(): ?Beneficiary
    {
        return $this->beneficiary;
    }

    public function setBeneficiary(?Beneficiary $beneficiary): static
    {
        $this->beneficiary = $beneficiary;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return Collection<int, VaultFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(VaultFile $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setVaultElement($this);
        }

        return $this;
    }

    public function removeFile(VaultFile $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getVaultElement() === $this) {
                $file->setVaultElement(null);
            }
        }

        return $this;
    }
}