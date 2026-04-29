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
            CREATE TABLE customers (
                id         BIGSERIAL                    NOT NULL,
                name       VARCHAR(255)                 NOT NULL,
                code       VARCHAR(100)                 DEFAULT NULL,
                is_active  BOOLEAN                      NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP(0) WITH TIME ZONE  NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMP(0) WITH TIME ZONE  DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);

        $this->addSql('CREATE INDEX idx_customers_name ON customers (name)');
        $this->addSql('CREATE INDEX idx_customers_is_active ON customers (is_active)');

        $this->addSql('ALTER TABLE calculation_data ADD COLUMN customer_id BIGINT DEFAULT NULL');
        $this->addSql(<<<'SQL'
            ALTER TABLE calculation_data
                ADD CONSTRAINT fk_calculation_data_customer_id
                FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL
        SQL);

        $this->addSql('ALTER TABLE calculation_data DROP COLUMN customer');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calculation_data DROP CONSTRAINT fk_calculation_data_customer_id');
        $this->addSql('ALTER TABLE calculation_data DROP COLUMN customer_id');
        $this->addSql('ALTER TABLE calculation_data ADD COLUMN customer VARCHAR(150) DEFAULT NULL');
        $this->addSql('DROP TABLE customers');
    }
}
