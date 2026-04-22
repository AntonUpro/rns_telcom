<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add operators table and operator column to calculation_equipment';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE operators (
                id        BIGSERIAL     NOT NULL,
                name      VARCHAR(255)  NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT NOW(),
                PRIMARY KEY (id)
            )
        SQL);

        $this->addSql('CREATE INDEX idx_operators_name ON operators (name)');

        $this->addSql('ALTER TABLE calculation_equipment ADD COLUMN operator VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calculation_equipment DROP COLUMN operator');
        $this->addSql('DROP TABLE operators');
    }
}
