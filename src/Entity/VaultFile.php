<?php

namespace App\Entity;

use App\Repository\VaultFileRepository;
use Doctrine\ORM\Mapping as ORM;

// Entité représentant un fichier (PDF, image, document) associé à un élément du coffre
// Chaque élément du coffre peut contenir plusieurs fichiers (relation OneToMany)
#[ORM\Entity(repositoryClass: VaultFileRepository::class)]
#[ORM\Table(name: 'vault_files')] //< Nom de table au pluriel pour cohérence avec les autres tables
class VaultFile
{
    // Clé primaire avec préfixe vfl_ (cohérence avec le dictionnaire MERISE)
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'vfl_id')]
    private ?int $id = null;

    // Nom unique du fichier sur le disque (généré avec uniqid pour éviter les collisions)
    #[ORM\Column(name: 'vfl_filename', length: 255)]
    private ?string $filename = null;

    // Nom original du fichier tel qu'envoyé par l'utilisateur
    #[ORM\Column(name: 'vfl_original_name', length: 255)]
    private ?string $originalName = null;

    // Type MIME du fichier (ex : application/pdf, image/jpeg)
    #[ORM\Column(name: 'vfl_mime_type', length: 100)]
    private ?string $mimeType = null;

    // Date et heure du téléversement (DateTimeImmutable pour garantir l'intégrité)
    #[ORM\Column(name: 'vfl_uploaded_at')]
    private ?\DateTimeImmutable $uploadedAt = null;

    // Relation vers l'élément du coffre auquel ce fichier appartient
    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(name: 'vfl_vault_element_id', referencedColumnName: 'elv_id', nullable: false)]
    private ?VaultElement $vaultElement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): static
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getVaultElement(): ?VaultElement
    {
        return $this->vaultElement;
    }

    public function setVaultElement(?VaultElement $vaultElement): static
    {
        $this->vaultElement = $vaultElement;

        return $this;
    }
}