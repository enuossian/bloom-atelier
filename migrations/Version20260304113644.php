<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304113644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking_session DROP FOREIGN KEY `FK_8AFA90F43301C60`');
        $this->addSql('ALTER TABLE booking_session DROP FOREIGN KEY `FK_8AFA90F4613FECDF`');
        $this->addSql('DROP TABLE booking_session');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking_session (booking_id INT NOT NULL, session_id INT NOT NULL, INDEX IDX_8AFA90F43301C60 (booking_id), INDEX IDX_8AFA90F4613FECDF (session_id), PRIMARY KEY (booking_id, session_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE booking_session ADD CONSTRAINT `FK_8AFA90F43301C60` FOREIGN KEY (booking_id) REFERENCES booking (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_session ADD CONSTRAINT `FK_8AFA90F4613FECDF` FOREIGN KEY (session_id) REFERENCES session (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
