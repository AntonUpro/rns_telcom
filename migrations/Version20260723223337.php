<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260723223337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add survey_performed column to calculation_data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calculation_data ADD COLUMN IF NOT EXISTS survey_performed BOOLEAN NOT NULL DEFAULT FALSE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calculation_data DROP COLUMN IF EXISTS survey_performed');
    }
}
