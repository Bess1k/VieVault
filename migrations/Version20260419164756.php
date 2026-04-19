<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Renommage de la table "beneficiary" en "beneficiaries" et ajout du préfixe bnf_
 * sur toutes les colonnes, conformément au dictionnaire de données MERISE.
 */
final class Version20260419164756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise en cohérence des noms de colonnes de la table Beneficiary avec le dictionnaire MERISE (préfixe bnf_)';
    }

    public function up(Schema $schema): void
    {
        // Renommer la table "beneficiary" en "beneficiaries"
        $this->addSql('ALTER TABLE beneficiary RENAME TO beneficiaries');

        // Renommer toutes les colonnes avec le préfixe bnf_
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN id TO bnf_id');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN lastname TO bnf_lastname');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN firstname TO bnf_firstname');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN email TO bnf_email');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN birth_date TO bnf_birth_date');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN birth_place TO bnf_birth_place');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN access_token TO bnf_access_token');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN token_expires_at TO bnf_token_expires_at');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN submitted_doc_path TO bnf_submitted_doc_path');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN validation_status TO bnf_validation_status');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN created_by_id TO bnf_user_id');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN id_doc_path TO bnf_id_doc_path');
    }

    public function down(Schema $schema): void
    {
        // Rollback : restaurer les noms originaux
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_id TO id');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_lastname TO lastname');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_firstname TO firstname');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_email TO email');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_birth_date TO birth_date');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_birth_place TO birth_place');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_access_token TO access_token');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_token_expires_at TO token_expires_at');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_submitted_doc_path TO submitted_doc_path');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_validation_status TO validation_status');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_user_id TO created_by_id');
        $this->addSql('ALTER TABLE beneficiaries RENAME COLUMN bnf_id_doc_path TO id_doc_path');

        $this->addSql('ALTER TABLE beneficiaries RENAME TO beneficiary');
    }
}