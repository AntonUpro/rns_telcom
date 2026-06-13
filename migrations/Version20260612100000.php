<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление отчества и файла подписи к пользователям';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD COLUMN patronymic VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN signature_file_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN patronymic');
        $this->addSql('ALTER TABLE users DROP COLUMN signature_file_name');
    }
}
