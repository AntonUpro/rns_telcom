<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260517120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Таблица статических изображений для приложений отчёта ОТС (Сертификаты, СРО, НОПРИЗ)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE appendix_static_images (
                id            BIGSERIAL                   NOT NULL,
                appendix_type VARCHAR(50)                 NOT NULL,
                position      INTEGER                     NOT NULL DEFAULT 0,
                file_name     VARCHAR(255)                NOT NULL,
                created_at    TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT NOW(),
                updated_at    TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);

        $this->addSql('CREATE INDEX idx_appendix_static_images_type_pos ON appendix_static_images (appendix_type, position)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE appendix_static_images');
    }
}
