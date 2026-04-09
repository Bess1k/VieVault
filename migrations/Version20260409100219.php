<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260409100219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vault_element ADD beneficiary_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vault_element ADD CONSTRAINT FK_1F592D07ECCAAFA0 FOREIGN KEY (beneficiary_id) REFERENCES beneficiary (id)');
        $this->addSql('CREATE INDEX IDX_1F592D07ECCAAFA0 ON vault_element (beneficiary_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vault_element DROP CONSTRAINT FK_1F592D07ECCAAFA0');
        $this->addSql('DROP INDEX IDX_1F592D07ECCAAFA0');
        $this->addSql('ALTER TABLE vault_element DROP beneficiary_id');
    }
}
