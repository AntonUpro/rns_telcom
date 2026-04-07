<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403125406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Таблица файлов отчетов';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE calculation_report_file (
                id                 SERIAL          NOT NULL,
                calculation_id     INT             NOT NULL,
                type               VARCHAR(50)     NOT NULL,
                file_name          VARCHAR(255)    NOT NULL,
                file_path          VARCHAR(500)    NOT NULL,
                mime_type          VARCHAR(100)    NOT NULL,
                file_size          INTEGER         NOT NULL,
                version            INTEGER         NOT NULL DEFAULT 1,
                created_at         TIMESTAMP(0)    WITHOUT TIME ZONE NOT NULL,
                updated_at         TIMESTAMP(0)    WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )'
        );

        $this->addSql(
            'ALTER TABLE calculation_report_file
                ADD CONSTRAINT fk_calc_report_file_calculation_id
                FOREIGN KEY (calculation_id)
                REFERENCES calculations(id)
                ON DELETE CASCADE
                NOT DEFERRABLE INITIALLY IMMEDIATE'
        );

        $this->addSql('CREATE INDEX idx_calculation_report_file_calc_id   ON calculation_report_file(calculation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_calculation_report_file_calc_id');
        $this->addSql('DROP TABLE calculation_report_file');
    }
}
