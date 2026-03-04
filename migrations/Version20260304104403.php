<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304104403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(50) NOT NULL, total_amount NUMERIC(6, 2) NOT NULL, status VARCHAR(255) NOT NULL, confirmation_sent TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_E00CEDDEAEA34913 (reference), INDEX IDX_E00CEDDEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE booking_session (booking_id INT NOT NULL, session_id INT NOT NULL, INDEX IDX_8AFA90F43301C60 (booking_id), INDEX IDX_8AFA90F4613FECDF (session_id), PRIMARY KEY (booking_id, session_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE booking_session ADD CONSTRAINT FK_8AFA90F43301C60 FOREIGN KEY (booking_id) REFERENCES booking (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_session ADD CONSTRAINT FK_8AFA90F4613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA76ED395');
        $this->addSql('ALTER TABLE booking_session DROP FOREIGN KEY FK_8AFA90F43301C60');
        $this->addSql('ALTER TABLE booking_session DROP FOREIGN KEY FK_8AFA90F4613FECDF');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE booking_session');
    }
}
