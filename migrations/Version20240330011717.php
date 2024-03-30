<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240330011717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sample Student entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE student_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE student (id INT NOT NULL, name VARCHAR(64) NOT NULL, code INT DEFAULT NULL, is_active BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE student_id_seq CASCADE');
        $this->addSql('DROP TABLE student');
    }
}
