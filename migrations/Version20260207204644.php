<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207204644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create calculation_equipment table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE calculation_equipment (
            id SERIAL NOT NULL,
            calculation_id INT NOT NULL,
            equipment_group VARCHAR(20) NOT NULL,
            equipment_type VARCHAR(20) NOT NULL,
            mounting_height NUMERIC(8, 2) DEFAULT NULL,
            quantity INT NOT NULL,
            equipment_params JSON NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        
        $this->addSql('CREATE INDEX idx_calculation_equipment_calculation_id ON calculation_equipment (calculation_id)');
        
        $this->addSql('ALTER TABLE calculation_equipment ADD CONSTRAINT FK_CalculationEquipment_Calculation 
            FOREIGN KEY (calculation_id) REFERENCES calculations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calculation_equipment DROP CONSTRAINT FK_CalculationEquipment_Calculation');
        $this->addSql('DROP TABLE calculation_equipment');
    }
}