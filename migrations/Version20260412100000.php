<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260412100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Таблица документов программного расчёта';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE calculation_documents (
                id                SERIAL          NOT NULL,
                calculation_id    INT             NOT NULL,
                name              VARCHAR(500)    NOT NULL,
                sort_order        SMALLINT        NOT NULL DEFAULT 0,
                created_at        TIMESTAMP(0)    WITHOUT TIME ZONE NOT NULL,
                updated_at        TIMESTAMP(0)    WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )'
        );

        $this->addSql(
            'ALTER TABLE calculation_documents
                ADD CONSTRAINT fk_calc_documents_calculation_id
                FOREIGN KEY (calculation_id)
                REFERENCES calculations(id)
                ON DELETE CASCADE
                NOT DEFERRABLE INITIALLY IMMEDIATE'
        );

        $this->addSql('CREATE INDEX idx_calc_documents_calc_id ON calculation_documents(calculation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_calc_documents_calc_id');
        $this->addSql('DROP TABLE calculation_documents');
    }
}
