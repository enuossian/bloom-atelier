<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304114940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book_item (id INT AUTO_INCREMENT NOT NULL, price NUMERIC(6, 2) NOT NULL, created_at DATETIME NOT NULL, booking_id INT NOT NULL, session_id INT NOT NULL, INDEX IDX_6866E163301C60 (booking_id), INDEX IDX_6866E16613FECDF (session_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE book_item ADD CONSTRAINT FK_6866E163301C60 FOREIGN KEY (booking_id) REFERENCES booking (id)');
        $this->addSql('ALTER TABLE book_item ADD CONSTRAINT FK_6866E16613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book_item DROP FOREIGN KEY FK_6866E163301C60');
        $this->addSql('ALTER TABLE book_item DROP FOREIGN KEY FK_6866E16613FECDF');
        $this->addSql('DROP TABLE book_item');
    }
}
