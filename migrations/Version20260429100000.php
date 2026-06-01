<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260429100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add customers table and customer_id FK to calculation_data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS customers (
                id         BIGSERIAL                    NOT NULL,
                legal_name VARCHAR(255)                 NOT NULL,
                ots_name   VARCHAR(255)                 NOT NULL,
                code       VARCHAR(100)                 DEFAULT NULL,
                is_active  BOOLEAN                      NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP(0) WITH TIME ZONE  NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMP(0) WITH TIME ZONE  DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);

        $this->addSql('CREATE INDEX idx_customers_legal_name ON customers (legal_name)');
        $this->addSql('CREATE INDEX idx_customers_is_active ON customers (is_active)');

        $this->addSql(<<<'SQL'
            INSERT INTO customers (legal_name, ots_name, code, is_active) VALUES
                (E'АО "Национальная Башенная Компания"',     E'ОА ГК "Сервис-Телеком"',                    'NBK',   TRUE),
                (E'ООО "ПЕРВАЯ БАШЕННАЯ КОМПАНИЯ"',          E'АО "ПБК"',                                   'PBK',   TRUE),
                (E'ООО "Пилар"',                             E'ООО "Пилар"',                                'PILAR', TRUE),
                (E'ООО "Т2 Мобайл"',                        E'ООО "Т2 Мобайл"',                            'T2M',   TRUE),
                (E'ООО "Антарес"',                           E'ООО "Антарес"',                              'ANT',   TRUE),
                (E'ООО "Асва"',                              E'ООО "Асва"',                                 'ASVA',  TRUE),
                (E'ООО "Аструм"',                            E'ООО "Аструм"',                               'ASTR',  TRUE),
                (E'ООО "Б2Б Дальний Восток"',               E'ООО "Б2Б Дальний Восток"',                   'B2BDV', TRUE),
                (E'ООО "Бюро 1440"',                         E'ООО "Бюро 1440"',                            'B1440', TRUE),
                (E'ООО "ВЕСТ КОЛЛ ЛТД"',                    E'ООО "ВЕСТ КОЛЛ ЛТД"',                        'VKL',   TRUE),
                (E'ООО "Газпром трансгаз Сургут"',          E'ООО "Газпром трансгаз Сургут"',              'GTS',   TRUE),
                (E'ООО "Динат"',                             E'ООО "Динат"',                                'DINAT', TRUE),
                (E'ООО "Мегаполис"',                         E'ООО "Мегаполис"',                            'MEG',   TRUE),
                (E'ИП Меркушева',                            E'ИП Меркушева',                               'MERK',  TRUE),
                (E'ООО "МСК Групп"',                        E'ООО "МСК Групп"',                            'MSKG',  TRUE),
                (E'ООО "Новые Строительные Технологии"',    E'ООО "Новые Строительные Технологии"',        'NST',   TRUE),
                (E'ООО "ПоморКом"',                          E'ООО "ПоморКом"',                             'POMK',  TRUE),
                (E'АО "Русские Башни"',                     E'АО "Русские Башни"',                         'RB',    TRUE),
                (E'ООО "Русские Башни Транспорт"',          E'ООО "Русские Башни Транспорт"',              'RBT',   TRUE),
                (E'АО "СИГНАЛТЕК"',                         E'АО "СИГНАЛТЕК"',                             'SIGT',  TRUE),
                (E'АО "СИНЕРДЖИ ТЕЛЕКОМ"',                  E'АО "СИНЕРДЖИ ТЕЛЕКОМ"',                      'SINT',  TRUE),
                (E'ООО "СМТ"',                               E'ООО "СМТ"',                                  'SMT',   TRUE),
                (E'АО "СОЦИНТЕХ-ИНСТАЛ"',                   E'АО "СОЦИНТЕХ-ИНСТАЛ"',                       'SOCI',  TRUE),
                (E'ООО "Связь Проектирование Строительство"', E'ООО "Связь Проектирование Строительство"', 'SPS',   TRUE),
                (E'ООО "СТРОЙМИГ КОННЕКТ"',                 E'ООО "СТРОЙМИГ КОННЕКТ"',                     'SMK',   TRUE),
                (E'ООО "СТРОЙСТАНДАРТ"',                    E'ООО "СТРОЙСТАНДАРТ"',                        'SST',   TRUE),
                (E'ООО "СФЕРА ТЕЛЕКОМ СЕРВИС"',             E'ООО "СФЕРА ТЕЛЕКОМ СЕРВИС"',                 'STS',   TRUE),
                (E'ООО "ТАУЭР"',                             E'ООО "ТАУЭР"',                                'TAUR',  TRUE),
                (E'ООО "ТЕЛЕКОМ-52"',                        E'ООО "ТЕЛЕКОМ-52"',                           'TEL52', TRUE),
                (E'ООО "ТелекомСтройСервис"',               E'ООО "ТелекомСтройСервис"',                   'TSS',   TRUE),
                (E'ООО "Теле-Нова"',                         E'ООО "Теле-Нова"',                            'TN',    TRUE),
                (E'ООО "Телетауэр"',                         E'ООО "Телетауэр"',                            'TT',    TRUE),
                (E'ООО "Техмонтаж"',                         E'ООО "Техмонтаж"',                            'TM',    TRUE)
        SQL);

        $this->addSql('ALTER TABLE calculation_data ADD COLUMN IF NOT EXISTS customer_id BIGINT DEFAULT NULL');
        $this->addSql(<<<'SQL'
            ALTER TABLE calculation_data
                ADD CONSTRAINT fk_calculation_data_customer_id
                FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL
        SQL);

        $this->addSql('ALTER TABLE calculation_data DROP COLUMN IF EXISTS customer');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calculation_data DROP CONSTRAINT IF EXISTS fk_calculation_data_customer_id');
        $this->addSql('ALTER TABLE calculation_data DROP COLUMN IF EXISTS customer_id');
        $this->addSql('ALTER TABLE calculation_data ADD COLUMN IF NOT EXISTS customer VARCHAR(150) DEFAULT NULL');
        $this->addSql('DROP TABLE customers');
    }
}
