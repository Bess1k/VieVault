<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Renommage de la table "audit_log" en "audit_logs" et ajout du préfixe log_
 * sur toutes les colonnes, conformément au dictionnaire de données MERISE.
 */
final class Version20260419173235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise en cohérence des noms de colonnes de la table AuditLog avec le dictionnaire MERISE (préfixe log_)';
    }

    public function up(Schema $schema): void
    {
        // Renommer la table "audit_log" en "audit_logs"
        $this->addSql('ALTER TABLE audit_log RENAME TO audit_logs');

        // Renommer toutes les colonnes avec le préfixe log_
        $this->addSql('ALTER TABLE audit_logs RENAME COLUMN id TO log_id');
        $this->addSql('ALTER TABLE audit_logs RENAME COLUMN action TO log_action');
        $this->addSql('ALTER TABLE audit_logs RENAME COLUMN ip_address TO log_ip_address');
        $this->addSql('ALTER TABLE audit_logs RENAME COLUMN created_at TO log_created_at');
        $this->addSql('ALTER TABLE audit_logs RENAME COLUMN created_by_id TO log_user_id');
    }

    public function down(Schema $schema): void
    {
        // Rollback : restaurer les noms originaux
        $this->addSql('ALTER TABLE audit_logs RENAME COLUMN log_id TO id');
        $this->addSql('ALTER TABLE audit_logs RENAME COLUMN log_action TO action');
        $this->addSql('ALTER TABLE audit_logs RENAME COLUMN log_ip_address TO ip_address');
        $this->addSql('ALTER TABLE audit_logs RENAME COLUMN log_created_at TO created_at');
        $this->addSql('ALTER TABLE audit_logs RENAME COLUMN log_user_id TO created_by_id');

        $this->addSql('ALTER TABLE audit_logs RENAME TO audit_log');
    }
}