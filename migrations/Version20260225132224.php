<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225132224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(50) NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, location VARCHAR(255) DEFAULT NULL, max_participants INT NOT NULL, status VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, service_id INT NOT NULL, INDEX IDX_D044D5D4ED5CA9E6 (service_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE service CHANGE is_active is_active TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4ED5CA9E6');
        $this->addSql('DROP TABLE session');
        $this->addSql('ALTER TABLE service CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL');
    }
}
