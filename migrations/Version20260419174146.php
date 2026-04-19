<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Renommage de la table "vault_file" en "vault_files" et ajout du préfixe vfl_
 * sur toutes les colonnes, pour cohérence avec les autres tables MERISE.
 */
final class Version20260419174146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise en cohérence des noms de colonnes de la table VaultFile avec la convention MERISE (préfixe vfl_)';
    }

    public function up(Schema $schema): void
    {
        // Renommer la table "vault_file" en "vault_files"
        $this->addSql('ALTER TABLE vault_file RENAME TO vault_files');

        // Renommer toutes les colonnes avec le préfixe vfl_
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN id TO vfl_id');
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN filename TO vfl_filename');
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN original_name TO vfl_original_name');
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN mime_type TO vfl_mime_type');
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN uploaded_at TO vfl_uploaded_at');
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN vault_element_id TO vfl_vault_element_id');
    }

    public function down(Schema $schema): void
    {
        // Rollback : restaurer les noms originaux
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN vfl_id TO id');
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN vfl_filename TO filename');
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN vfl_original_name TO original_name');
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN vfl_mime_type TO mime_type');
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN vfl_uploaded_at TO uploaded_at');
        $this->addSql('ALTER TABLE vault_files RENAME COLUMN vfl_vault_element_id TO vault_element_id');

        $this->addSql('ALTER TABLE vault_files RENAME TO vault_file');
    }
}