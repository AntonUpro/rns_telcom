<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260418100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Переименование table_type в calculation_result_table: числовые ключи → семантические';
    }

    public function up(Schema $schema): void
    {
        // Расширяем колонку: 'superstructure_stress' = 21 символ
        $this->addSql("ALTER TABLE calculation_result_table ALTER COLUMN table_type TYPE VARCHAR(32)");

        $this->addSql("UPDATE calculation_result_table SET table_type = 'pillar_forces'         WHERE table_type = 'table1'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'crack_opening'         WHERE table_type = 'table2'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'brace_stress'          WHERE table_type = 'table3'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'superstructure_stress' WHERE table_type = 'table4'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'platform_forces'       WHERE table_type = 'table5'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'base_forces'           WHERE table_type = 'table6'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'deformation'           WHERE table_type = 'table7'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'foundation'            WHERE table_type = 'table8'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE calculation_result_table SET table_type = 'table1' WHERE table_type = 'pillar_forces'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'table2' WHERE table_type = 'crack_opening'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'table3' WHERE table_type = 'brace_stress'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'table4' WHERE table_type = 'superstructure_stress'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'table5' WHERE table_type = 'platform_forces'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'table6' WHERE table_type = 'base_forces'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'table7' WHERE table_type = 'deformation'");
        $this->addSql("UPDATE calculation_result_table SET table_type = 'table8' WHERE table_type = 'foundation'");

        $this->addSql("ALTER TABLE calculation_result_table ALTER COLUMN table_type TYPE VARCHAR(16)");
    }
}
