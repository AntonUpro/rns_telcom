<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260710131954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Правки полей';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calculations RENAME COLUMN name TO object_code;');
        $this->addSql('ALTER TABLE calculation_data DROP COLUMN object_code;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calculations RENAME COLUMN object_code TO name;');
        $this->addSql('ALTER TABLE calculation_data ADD COLUMN object_code varchar(255);');
    }
}
