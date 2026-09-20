<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Переименование pillar_platform/pillar_platform_sections в platform/platform_sections (общие для столба и башни)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pillar_platform RENAME TO platform');
        $this->addSql('ALTER TABLE pillar_platform_sections RENAME TO platform_sections');
        $this->addSql('ALTER TABLE platform_sections RENAME COLUMN pillar_platform_id TO platform_id');

        $this->addSql('ALTER TABLE platform RENAME CONSTRAINT fk_pillar_platform_calculation_id TO fk_platform_calculation_id');
        $this->addSql('ALTER TABLE platform_sections RENAME CONSTRAINT fk_sections_pillar_platform_id TO fk_sections_platform_id');

        $this->addSql('ALTER INDEX idx_pillar_platform_calculation_id RENAME TO idx_platform_calculation_id');
        $this->addSql('ALTER INDEX idx_sections_pillar_equipments_id RENAME TO idx_sections_platform_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER INDEX idx_platform_calculation_id RENAME TO idx_pillar_platform_calculation_id');
        $this->addSql('ALTER INDEX idx_sections_platform_id RENAME TO idx_sections_pillar_equipments_id');

        $this->addSql('ALTER TABLE platform RENAME CONSTRAINT fk_platform_calculation_id TO fk_pillar_platform_calculation_id');
        $this->addSql('ALTER TABLE platform_sections RENAME CONSTRAINT fk_sections_platform_id TO fk_sections_pillar_platform_id');

        $this->addSql('ALTER TABLE platform_sections RENAME COLUMN platform_id TO pillar_platform_id');
        $this->addSql('ALTER TABLE platform_sections RENAME TO pillar_platform_sections');
        $this->addSql('ALTER TABLE platform RENAME TO pillar_platform');
    }
}
