<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260311080856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Таблицы с площадками и секциями для площадок';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE pillar_platform (
                id SERIAL NOT NULL,
                calculation_id INT NOT NULL,
                mounting_height NUMERIC(8, 2) DEFAULT NULL,
                mounting_height_strut NUMERIC(8, 2) DEFAULT NULL,
                facets_count INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )'
        );

        $this->addSql(
            'CREATE TABLE pillar_platform_sections (
                id SERIAL NOT NULL,
                pillar_platform_id INT NOT NULL,
                type_section VARCHAR(20) NOT NULL,
                number_section integer NOT NULL,
                height NUMERIC(8, 3) DEFAULT NULL,
                width_bottom NUMERIC(8, 3) DEFAULT NULL,
                width_top NUMERIC(8, 3) DEFAULT NULL,
                mount_height_bottom integer NOT NULL,
                mount_height_top integer NOT NULL,
                elements json DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )'
        );

        $this->addSql('ALTER TABLE pillar_platform ADD CONSTRAINT FK_PILLAR_PLATFORM_CALCULATION_ID FOREIGN KEY (calculation_id) REFERENCES calculations(id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pillar_platform_sections ADD CONSTRAINT FK_SECTIONS_PILLAR_PLATFORM_ID FOREIGN KEY (pillar_platform_id) REFERENCES pillar_platform(id) NOT DEFERRABLE INITIALLY IMMEDIATE');


        $this->addSql('CREATE INDEX IDX_PILLAR_PLATFORM_CALCULATION_ID ON pillar_platform(calculation_id)');
        $this->addSql('CREATE INDEX IDX_SECTIONS_PILLAR_EQUIPMENTS_ID ON pillar_platform_sections(pillar_platform_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_SECTIONS_PILLAR_EQUIPMENTS_ID');
        $this->addSql('DROP INDEX IDX_PILLAR_PLATFORM_CALCULATION_ID');
        $this->addSql('DROP TABLE pillar_platform_sections');
        $this->addSql('DROP TABLE pillar_platform');
    }
}
