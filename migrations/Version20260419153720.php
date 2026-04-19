<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Renommage de la table "user" en "users" et ajout du préfixe usr_
 * sur toutes les colonnes, conformément au dictionnaire de données MERISE.
 */
final class Version20260419153720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise en cohérence des noms de colonnes de la table User avec le dictionnaire MERISE (préfixe usr_)';
    }

    public function up(Schema $schema): void
    {
        // Renommer la table "user" en "users"
        $this->addSql('ALTER TABLE "user" RENAME TO users');

        // Renommer toutes les colonnes avec le préfixe usr_
        $this->addSql('ALTER TABLE users RENAME COLUMN id TO usr_id');
        $this->addSql('ALTER TABLE users RENAME COLUMN email TO usr_email');
        $this->addSql('ALTER TABLE users RENAME COLUMN roles TO usr_roles');
        $this->addSql('ALTER TABLE users RENAME COLUMN password TO usr_password_hash');
        $this->addSql('ALTER TABLE users RENAME COLUMN lastname TO usr_lastname');
        $this->addSql('ALTER TABLE users RENAME COLUMN firstname TO usr_firstname');
        $this->addSql('ALTER TABLE users RENAME COLUMN birth_date TO usr_birth_date');
        $this->addSql('ALTER TABLE users RENAME COLUMN birth_place TO usr_birth_place');
        $this->addSql('ALTER TABLE users RENAME COLUMN panic_password_hash TO usr_panic_password_hash');
        $this->addSql('ALTER TABLE users RENAME COLUMN last_login_at TO usr_last_login_at');
        $this->addSql('ALTER TABLE users RENAME COLUMN is_paused TO usr_is_paused');
        $this->addSql('ALTER TABLE users RENAME COLUMN pause_until TO usr_pause_until');
        $this->addSql('ALTER TABLE users RENAME COLUMN status TO usr_status');
        $this->addSql('ALTER TABLE users RENAME COLUMN emergency_email TO usr_emergency_email');
        $this->addSql('ALTER TABLE users RENAME COLUMN is_verified TO usr_is_verified');
    }

    public function down(Schema $schema): void
    {
        // Rollback : restaurer les noms originaux des colonnes et de la table
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_id TO id');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_email TO email');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_roles TO roles');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_password_hash TO password');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_lastname TO lastname');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_firstname TO firstname');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_birth_date TO birth_date');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_birth_place TO birth_place');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_panic_password_hash TO panic_password_hash');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_last_login_at TO last_login_at');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_is_paused TO is_paused');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_pause_until TO pause_until');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_status TO status');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_emergency_email TO emergency_email');
        $this->addSql('ALTER TABLE users RENAME COLUMN usr_is_verified TO is_verified');

        $this->addSql('ALTER TABLE users RENAME TO "user"');
    }
}