<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Исправление наименований и обозначений труб ГОСТ 10704-91 в gauge_profile.
 *
 * Баг миграции Version20260605140000: толщина стенки вычислялась как
 * `t10 / 10.0` (numeric-деление), из-за чего PostgreSQL расширял дробную
 * часть до 16 знаков после запятой при приведении к тексту. В результате
 * в name/designation попали значения вида «10×1.5000000000000000» вместо
 * «10×1.5».
 *
 * Толщина стенки исходно хранилась как t10 (толщина×10, целое), поэтому
 * значимая дробная часть всегда состоит ровно из одной цифры — обрезаем
 * все цифры после неё регулярным выражением.
 */
final class Version20260719120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Исправление раздутой дробной части в name/designation труб ГОСТ 10704-91 (gauge_profile)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE gauge_profile
            SET name = regexp_replace(name, '(\.\d)\d+', '\1', 'g'),
                designation = regexp_replace(designation, '(\.\d)\d+', '\1', 'g')
            WHERE name ~ '\.\d\d+' OR designation ~ '\.\d\d+'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Восстановление изначального (ошибочного) вида не имеет смысла.
    }
}
