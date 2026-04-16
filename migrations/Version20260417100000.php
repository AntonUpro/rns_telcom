<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260417100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Таблица результатов расчёта (table1..table8) с хранением строк в JSONB';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE calculation_result_table (
                id               BIGSERIAL    PRIMARY KEY,
                calculation_id   INT          NOT NULL REFERENCES calculations(id) ON DELETE CASCADE,
                table_type       VARCHAR(16)  NOT NULL,
                enabled          BOOLEAN      NOT NULL DEFAULT TRUE,
                rows             JSONB        NOT NULL DEFAULT '[]',
                updated_at       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
                CONSTRAINT uq_calc_result_table UNIQUE (calculation_id, table_type)
            )
        ");

        $this->addSql("COMMENT ON TABLE  calculation_result_table                IS 'Результаты расчётных таблиц (table1..table8) по расчёту'");
        $this->addSql("COMMENT ON COLUMN calculation_result_table.table_type     IS 'Идентификатор таблицы: table1..table8'");
        $this->addSql("COMMENT ON COLUMN calculation_result_table.enabled        IS 'Для опциональных таблиц (3-8): включена ли таблица'");
        $this->addSql("COMMENT ON COLUMN calculation_result_table.rows           IS 'Массив строк таблицы (входные + вычисленные поля) в формате JSON'");

        $this->addSql("CREATE INDEX idx_calc_result_table_calc_id ON calculation_result_table(calculation_id)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS calculation_result_table');
    }
}
