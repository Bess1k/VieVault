<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Renommage de la table "vault_element" en "vault_elements" et ajout du préfixe elv_
 * sur toutes les colonnes, conformément au dictionnaire de données MERISE.
 */
final class Version20260419163149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise en cohérence des noms de colonnes de la table VaultElement avec le dictionnaire MERISE (préfixe elv_)';
    }

    public function up(Schema $schema): void
    {
        // Renommer la table "vault_element" en "vault_elements"
        $this->addSql('ALTER TABLE vault_element RENAME TO vault_elements');

        // Renommer toutes les colonnes avec le préfixe elv_
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN id TO elv_id');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN title TO elv_title');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN type TO elv_type');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN content TO elv_content');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN is_heritage TO elv_is_heritage');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN created_at TO elv_created_at');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN updated_at TO elv_updated_at');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN created_by_id TO elv_user_id');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN beneficiary_id TO elv_beneficiary_id');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN file_path TO elv_file_path');
    }

    public function down(Schema $schema): void
    {
        // Rollback : restaurer les noms originaux
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN elv_id TO id');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN elv_title TO title');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN elv_type TO type');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN elv_content TO content');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN elv_is_heritage TO is_heritage');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN elv_created_at TO created_at');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN elv_updated_at TO updated_at');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN elv_user_id TO created_by_id');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN elv_beneficiary_id TO beneficiary_id');
        $this->addSql('ALTER TABLE vault_elements RENAME COLUMN elv_file_path TO file_path');

        $this->addSql('ALTER TABLE vault_elements RENAME TO vault_element');
    }
}