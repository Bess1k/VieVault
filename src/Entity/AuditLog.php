<?php

namespace App\Entity;

use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;

// Entité pour tracer les actions sensibles effectuées dans l'application
// Chaque action est enregistrée avec l'utilisateur concerné, l'IP et l'horodatage
#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: 'audit_logs')] //< Nom de table conforme au dictionnaire MERISE
class AuditLog
{
    // Clé primaire avec préfixe log_ (dictionnaire MERISE)
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'log_id')]
    private ?int $id = null;

    // Type d'action : LOGIN, PANIC_LOGIN, CREATE, UPDATE, DELETE, HERITAGE_TRIGGER...
    #[ORM\Column(name: 'log_action', length: 255)]
    private ?string $action = null;

    // Adresse IP de l'utilisateur au moment de l'action (IPv4 ou IPv6)
    #[ORM\Column(name: 'log_ip_address', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    // Date et heure précises de l'action
    #[ORM\Column(name: 'log_created_at')]
    private ?\DateTime $createdAt = null;

    // Relation vers l'utilisateur concerné par l'action
    #[ORM\ManyToOne(inversedBy: 'auditLogs')]
    #[ORM\JoinColumn(name: 'log_user_id', referencedColumnName: 'usr_id', nullable: false)]
    private ?User $createdBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

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